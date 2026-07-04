<?php

namespace Mrgiant\FormBuilder\Tests\Unit\Models;

use Mrgiant\FormBuilder\Models\Answer;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mrgiant\FormBuilder\Tests\TestCase;

class FormResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_form_responses_table(): void
    {
        $this->assertEquals('form_responses', (new FormResponse())->getTable());
    }

    public function test_fillable_contains_form_id_and_ip(): void
    {
        $this->assertEquals(['form_id', 'ip', 'approval_status'], (new FormResponse())->getFillable());
    }

    public function test_processed_is_cast_to_boolean(): void
    {
        $this->assertEquals('boolean', (new FormResponse())->getCasts()['processed']);
    }

    public function test_belongs_to_form(): void
    {
        $relation = (new FormResponse())->form();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $relation
        );
    }

    public function test_has_many_answers(): void
    {
        $relation = (new FormResponse())->answers();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $relation
        );
    }

    public function test_form_response_can_be_created(): void
    {
        $form = $this->makeForm();

        $response = FormResponse::create([
            'form_id' => $form->id,
            'ip'      => '192.168.1.1',
        ]);

        $this->assertDatabaseHas('form_responses', [
            'id'      => $response->id,
            'form_id' => $form->id,
            'ip'      => '192.168.1.1',
        ]);
    }

    public function test_mark_as_processed_throws_when_processed_column_is_missing(): void
    {
        // Note: the `processed` column is referenced by the model but missing
        // from the form_responses migration, so markAsProcessed() will throw
        // a QueryException on a fresh schema. This test documents that known bug.
        $form     = $this->makeForm();
        $response = FormResponse::create(['form_id' => $form->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $response->markAsProcessed();
    }

    public function test_answers_relationship_returns_related_answers(): void
    {
        $form     = $this->makeForm();
        $response = FormResponse::create(['form_id' => $form->id]);

        $question          = new Question();
        $question->label   = 'Q1';
        $question->form_id = $form->id;
        $question->save();

        Answer::create([
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'Yes',
        ]);

        $this->assertEquals(1, $response->answers()->count());
    }

    public function test_form_response_cascades_on_form_delete(): void
    {
        $form     = $this->makeForm();
        $response = FormResponse::create(['form_id' => $form->id]);

        $form->delete();

        $this->assertDatabaseMissing('form_responses', ['id' => $response->id]);
    }

    protected function makeForm(): Form
    {
        $form       = new Form(['name' => 'Test']);
        $form->slug = 'test-' . uniqid();
        $form->save();

        return $form;
    }
}
