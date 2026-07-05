<?php

namespace Mrgiant\FormBuilder\Notifications;

use Mrgiant\FormBuilder\Models\FormResponse;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class FormWorkflowNotification extends Notification
{
    /**
     * @param  string  $kind  'pending' (approver's turn) | 'approved' | 'rejected' (submitter) | 'custom' (notification node)
     */
    public function __construct(
        public FormResponse $response,
        public string $kind,
        public ?string $customTitle = null,
        public ?string $customMessage = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'form_workflow',
            'kind' => $this->kind,
            'form_id' => $this->response->form_id,
            'form_name' => optional($this->response->form)->name,
            'response_id' => $this->response->id,
            'title' => $this->title(),
            'message' => $this->messageText(),
            'message_html' => $this->messageHtml(),
            'url' => $this->url(),
        ];
    }

    /**
     * The message rendered from GitHub-Flavored Markdown (tables, strikethrough,
     * task lists, autolinks) to HTML. Raw HTML is escaped so submitter answer
     * values interpolated via placeholders can't inject markup.
     */
    private function messageHtml(): string
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        return $this->inlineTableStyles((string) $converter->convert($this->messageText()));
    }

    /**
     * Apply inline table styles. Email clients strip <style>/external CSS, so
     * borders and padding must live on the elements themselves.
     */
    private function inlineTableStyles(string $html): string
    {
        if (! str_contains($html, '<table>')) {
            return $html;
        }

        return strtr($html, [
            '<table>' => '<table style="border-collapse:collapse;width:100%;margin:12px 0;font-size:14px;">',
            '<th>' => '<th style="border:1px solid #d1d5db;padding:6px 10px;text-align:left;background:#f3f4f6;font-weight:600;">',
            '<td>' => '<td style="border:1px solid #d1d5db;padding:6px 10px;">',
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->line(new HtmlString($this->messageHtml()))
            ->action(__('Open'), url($this->url()));
    }

    private function formName(): string
    {
        return (string) (optional($this->response->form)->name ?? __('Form'));
    }

    private function title(): string
    {
        return match ($this->kind) {
            'custom' => $this->customTitle ?: __('Workflow notification'),
            'approved' => __('Your submission was approved'),
            'rejected' => __('Your submission was rejected'),
            'terminated' => __('Your submission was closed'),
            default => $this->customTitle ?: __('A form response needs your approval'),
        };
    }

    private function messageText(): string
    {
        return match ($this->kind) {
            'custom' => $this->customMessage ?: __('A response to :form reached this step.', ['form' => $this->formName()]),
            'approved' => __('Your response to :form has been approved.', ['form' => $this->formName()]),
            'rejected' => __('Your response to :form has been rejected.', ['form' => $this->formName()]),
            'terminated' => __('Your response to :form has been closed.', ['form' => $this->formName()]),
            default => $this->customMessage ?: __('A response to :form is waiting for your approval.', ['form' => $this->formName()]),
        };
    }

    private function url(): string
    {
        return $this->kind === 'pending'
            ? '/admin/approvals/inbox'
            : '/admin/forms/'.$this->response->form_id.'/responses';
    }
}
