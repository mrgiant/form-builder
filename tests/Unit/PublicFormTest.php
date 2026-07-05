<?php

namespace Mrgiant\FormBuilder\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\Question;
use Mrgiant\FormBuilder\Tests\TestCase;

class PublicFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_route_registered_at_root_without_admin_prefix(): void
    {
        $this->assertTrue(config('form-builder.register_public_routes'));
        $this->assertTrue(Route::has('frontend.form.view_form'));
        $this->assertSame('forms/{slug}', Route::getRoutes()->getByName('frontend.form.view_form')->uri());
    }

    public function test_public_template_resolves(): void
    {
        $this->assertTrue(View::exists('forms.public'));
    }

    public function test_active_form_page_renders_by_slug(): void
    {
        config()->set('form-builder.public_assets', []); // avoid @vite manifest in tests

        $form = new Form(['name' => 'Public Intake']);
        $form->slug = 'intake-'.uniqid();
        $form->save();

        $this->get('/forms/'.$form->slug)
            ->assertOk()
            ->assertSee('Public Intake')
            ->assertSee('forms-questions-answers', false);
    }

    public function test_missing_slug_returns_404(): void
    {
        $this->get('/forms/does-not-exist')->assertNotFound();
    }

    public function test_public_data_routes_registered(): void
    {
        $this->assertTrue(Route::has('frontend.form.info'));
        $this->assertTrue(Route::has('frontend.form.questions'));
        $this->assertTrue(Route::has('frontend.form.submit'));
    }

    public function test_public_submission_creates_response_and_answer(): void
    {
        $form = new Form(['name' => 'Contact']);
        $form->slug = 'contact-'.uniqid();
        $form->save();

        $q = new Question();
        $q->label = 'Your name';
        $q->question_type = 'Text';
        $q->form_id = $form->id;
        $q->save();

        $this->post('/forms/'.$form->id.'/d', ['q-'.$q->id => 'Alice'])
            ->assertOk();

        $this->assertDatabaseHas('gl_form_responses', ['form_id' => $form->id]);
        $this->assertDatabaseHas('gl_answers', ['question_id' => $q->id, 'value' => 'Alice']);
    }
}
