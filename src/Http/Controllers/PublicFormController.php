<?php

namespace Mrgiant\FormBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Mrgiant\FormBuilder\Models\Form;

class PublicFormController extends Controller
{
    /**
     * Public form page — look up by slug and render the right state.
     */
    public function view_form(string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        $beginAt = $form->begin_at ? Carbon::parse($form->begin_at) : null;
        $endAt = $form->end_at ? Carbon::parse($form->end_at) : null;

        if ($beginAt && $beginAt->isFuture()) {
            return view('forms.public', ['form' => $form, 'state' => 'not_started']);
        }

        if ($form->status == 0 || ($endAt && $endAt->isPast())) {
            return view('forms.public', ['form' => $form, 'state' => 'closed']);
        }

        // Custom-HTML forms render their own fully standalone page.
        if (! empty($form->custom_html)) {
            return view('forms.custom_render', compact('form'));
        }

        return view('forms.public', ['form' => $form, 'state' => 'active']);
    }

    public function closedform(string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        return view('forms.public', ['form' => $form, 'state' => 'closed']);
    }

    public function NotStartForm(string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        return view('forms.public', ['form' => $form, 'state' => 'not_started']);
    }
}
