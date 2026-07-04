<?php

namespace Mrgiant\FormBuilder\Tests\Unit\Models;

use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mrgiant\FormBuilder\Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_questions_table(): void
    {
        $this->assertEquals('gl_questions', (new Question())->getTable());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $fillable = (new Question())->getFillable();

        foreach (['form_id', 'question_type', 'label', 'description', 'options',
                 'required', 'parent_id'] as $field) {
            $this->assertContains($field, $fillable, "Missing '$field' in fillable");
        }
    }

    public function test_options_is_cast_to_array(): void
    {
        $this->assertEquals('array', (new Question())->getCasts()['options']);
    }

    public function test_belongs_to_form(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            (new Question())->form()
        );
    }

    public function test_has_many_children_via_parent_id(): void
    {
        $relation = (new Question())->children();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $relation
        );
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
    }

    public function test_boot_auto_increments_order_for_top_level_questions(): void
    {
        $form = $this->makeForm();

        $q1 = Question::create(['form_id' => $form->id, 'label' => 'Q1']);
        $q2 = Question::create(['form_id' => $form->id, 'label' => 'Q2']);
        $q3 = Question::create(['form_id' => $form->id, 'label' => 'Q3']);

        $this->assertEquals(1, (int) $q1->fresh()->order);
        $this->assertEquals(2, (int) $q2->fresh()->order);
        $this->assertEquals(3, (int) $q3->fresh()->order);
    }

    public function test_boot_scopes_order_to_parent(): void
    {
        $form   = $this->makeForm();
        $parent = Question::create(['form_id' => $form->id, 'label' => 'Parent']);

        $c1 = Question::create(['form_id' => $form->id, 'label' => 'C1', 'parent_id' => $parent->id]);
        $c2 = Question::create(['form_id' => $form->id, 'label' => 'C2', 'parent_id' => $parent->id]);

        $this->assertEquals(1, (int) $c1->fresh()->order);
        $this->assertEquals(2, (int) $c2->fresh()->order);
    }

    public function test_question_cascades_on_form_delete(): void
    {
        $form     = $this->makeForm();
        $question = Question::create(['form_id' => $form->id, 'label' => 'Q']);

        $form->delete();

        $this->assertDatabaseMissing('gl_questions', ['id' => $question->id]);
    }

    public function test_options_is_persisted_as_array(): void
    {
        $form     = $this->makeForm();
        $question = Question::create([
            'form_id' => $form->id,
            'label'   => 'Choose one',
            'options' => ['A', 'B', 'C'],
        ]);

        $this->assertEquals(['A', 'B', 'C'], $question->fresh()->options);
    }

    protected function makeForm(): Form
    {
        $form       = new Form(['name' => 'Test']);
        $form->slug = 'q-' . uniqid();
        $form->save();

        return $form;
    }
}
