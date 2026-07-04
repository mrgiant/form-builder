<?php

namespace Mrgiant\FormBuilder\Tests\Unit\Models;

use Mrgiant\FormBuilder\Models\Answer;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mrgiant\FormBuilder\Tests\TestCase;

class AnswerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    public function test_uses_answers_table(): void
    {
        $this->assertEquals('answers', (new Answer())->getTable());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $fillable = (new Answer())->getFillable();

        $this->assertEquals(['form_response_id', 'question_id', 'value'], $fillable);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_belongs_to_form_response(): void
    {
        $relation = (new Answer())->FormResponse();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $relation
        );
    }

    public function test_belongs_to_question(): void
    {
        $relation = (new Answer())->question();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $relation
        );
    }

    // -------------------------------------------------------------------------
    // Database persistence
    // -------------------------------------------------------------------------

    public function test_answer_can_be_created(): void
    {
        [$response, $question] = $this->makeDependencies();

        $answer = Answer::create([
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'Yes',
        ]);

        $this->assertDatabaseHas('answers', [
            'id'               => $answer->id,
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'Yes',
        ]);
    }

    public function test_question_relationship_resolves(): void
    {
        [$response, $question] = $this->makeDependencies();

        $answer = Answer::create([
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'A',
        ]);

        $this->assertEquals($question->id, $answer->question->id);
    }

    public function test_form_response_relationship_resolves(): void
    {
        [$response, $question] = $this->makeDependencies();

        $answer = Answer::create([
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'A',
        ]);

        $this->assertEquals($response->id, $answer->FormResponse->id);
    }

    public function test_answer_cascades_on_question_delete(): void
    {
        [$response, $question] = $this->makeDependencies();

        $answer = Answer::create([
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'A',
        ]);

        $question->delete();

        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    public function test_answer_cascades_on_form_response_delete(): void
    {
        [$response, $question] = $this->makeDependencies();

        $answer = Answer::create([
            'form_response_id' => $response->id,
            'question_id'      => $question->id,
            'value'            => 'A',
        ]);

        $response->delete();

        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeDependencies(): array
    {
        $form = new Form(['name' => 'Intake Form']);
        $form->slug = 'intake-' . uniqid();
        $form->save();

        $response           = new FormResponse();
        $response->form_id  = $form->id;
        $response->save();

        $question          = new Question();
        $question->label   = 'Are you allergic to penicillin?';
        $question->form_id = $form->id;
        $question->save();

        return [$response, $question];
    }
}
