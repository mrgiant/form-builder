<?php

namespace Mrgiant\FormBuilder\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mrgiant\FormBuilder\Models\Form;
use Mrgiant\FormBuilder\Models\FormResponse;
use Mrgiant\FormBuilder\Notifications\FormWorkflowNotification;
use Mrgiant\FormBuilder\Tests\TestCase;

class FormWorkflowNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_html_renders_markdown(): void
    {
        $form = new Form(['name' => 'Intake']);
        $form->slug = 'intake-'.uniqid();
        $form->save();

        $response = new FormResponse();
        $response->form_id = $form->id;
        $response->save();

        $note = new FormWorkflowNotification($response, 'custom', 'Title', "**bold** and a [link](https://example.com)");
        $data = $note->toArray(null);

        // Raw markdown preserved, HTML rendered alongside it.
        $this->assertSame('**bold** and a [link](https://example.com)', $data['message']);
        $this->assertStringContainsString('<strong>bold</strong>', $data['message_html']);
        $this->assertStringContainsString('<a href="https://example.com"', $data['message_html']);
    }

    public function test_message_html_renders_gfm_table_and_escapes_raw_html(): void
    {
        $form = new Form(['name' => 'Intake']);
        $form->slug = 'intake-'.uniqid();
        $form->save();

        $response = new FormResponse();
        $response->form_id = $form->id;
        $response->save();

        $md = "| Field | Text |\n| --- | --- |\n| First Name | Odit |\n\n<script>alert(1)</script>";
        $data = (new FormWorkflowNotification($response, 'custom', 'T', $md))->toArray(null);

        $this->assertStringContainsString('<table>', $data['message_html']);
        $this->assertStringContainsString('<td>First Name</td>', $data['message_html']);
        // Raw HTML from (potentially attacker-controlled) values is escaped, not executed.
        $this->assertStringNotContainsString('<script>', $data['message_html']);
    }
}
