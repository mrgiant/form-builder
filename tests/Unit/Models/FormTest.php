<?php

namespace Mrgiant\FormBuilder\Tests\Unit\Models;

use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mrgiant\FormBuilder\Tests\TestCase;

class FormTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_forms_table(): void
    {
        $this->assertEquals('forms', (new Form())->getTable());
    }

    public function test_fillable_contains_expected_fields(): void
    {
        $fillable = (new Form())->getFillable();

        foreach (['name', 'description', 'thank_you_message', 'begin_at', 'end_at'] as $field) {
            $this->assertContains($field, $fillable, "Missing '$field' in fillable");
        }
    }

    public function test_emails_is_cast_to_array(): void
    {
        $this->assertEquals('array', (new Form())->getCasts()['emails']);
    }

    public function test_has_many_questions(): void
    {
        $relation = (new Form())->questions();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $relation
        );
    }

    public function test_has_many_responses(): void
    {
        $relation = (new Form())->responses();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $relation
        );
    }

    public function test_active_attribute_always_returns_true(): void
    {
        $form       = new Form();
        $form->name = 'Test';

        $this->assertTrue($form->active);
    }

    public function test_named_static_method_returns_form_by_slug(): void
    {
        $form       = new Form(['name' => 'Intake']);
        $form->slug = 'intake-form';
        $form->save();

        $this->assertEquals($form->id, Form::named('intake-form')->id);
    }

    public function test_named_returns_null_for_missing_slug(): void
    {
        $this->assertNull(Form::named('does-not-exist'));
    }

    public function test_form_can_be_created(): void
    {
        $form       = new Form(['name' => 'Intake']);
        $form->slug = 'intake-' . uniqid();
        $form->save();

        $this->assertDatabaseHas('forms', [
            'id'   => $form->id,
            'name' => 'Intake',
        ]);
    }

    public function test_questions_relationship_excludes_group_and_label_types(): void
    {
        $form       = new Form(['name' => 'Test']);
        $form->slug = 'test-' . uniqid();
        $form->save();

        $q1            = new Question();
        $q1->label     = 'Q1';
        $q1->question_type = 'text';
        $q1->form_id   = $form->id;
        $q1->order     = 1;
        $q1->save();

        $q2            = new Question();
        $q2->label     = 'Group';
        $q2->question_type = 'Group';
        $q2->form_id   = $form->id;
        $q2->order     = 2;
        $q2->save();

        $q3            = new Question();
        $q3->label     = 'Label';
        $q3->question_type = 'Label';
        $q3->form_id   = $form->id;
        $q3->order     = 3;
        $q3->save();

        $this->assertEquals(1, $form->questions()->count());
        $this->assertEquals('text', $form->questions()->first()->question_type);
    }

    public function test_responses_relationship_returns_related_responses(): void
    {
        $form       = new Form(['name' => 'Test']);
        $form->slug = 'resp-' . uniqid();
        $form->save();

        $response           = new FormResponse();
        $response->form_id  = $form->id;
        $response->save();

        $this->assertEquals(1, $form->responses()->count());
    }
}
