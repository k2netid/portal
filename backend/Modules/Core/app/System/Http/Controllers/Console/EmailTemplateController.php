<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\EmailTemplate;

class EmailTemplateController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = EmailTemplate::query();

        if ($request->has('category')) {
            $categoryRaw = $request->input('category');
            $category = is_string($categoryRaw) ? $categoryRaw : '';
            $query->where('category', $category);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->latest()->get();

        return $this->success($templates, 'Email templates retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:sys_email_templates,slug',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'text_body' => 'nullable|string',
            'variables' => 'nullable|array',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $template = EmailTemplate::create($validated);

        return $this->success($template, 'Email template created successfully', 201);
    }

    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        return $this->success($emailTemplate, 'Email template retrieved successfully');
    }

    public function update(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:sys_email_templates,slug,'.$emailTemplate->id,
            'subject' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'text_body' => 'nullable|string',
            'variables' => 'nullable|array',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $emailTemplate->update($validated);

        return $this->success($emailTemplate, 'Email template updated successfully');
    }

    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->delete();

        return $this->success(null, 'Email template deleted successfully');
    }

    public function previewUnsaved(Request $request): JsonResponse
    {
        $templateData = $request->only(['subject', 'body', 'text_body', 'variables']);
        $emailTemplate = new EmailTemplate($templateData);

        $dataRaw = $request->input('data', []);
        $data = is_array($dataRaw) ? $dataRaw : [];
        $rendered = $emailTemplate->render($data);

        return $this->success($rendered, 'Email template preview generated successfully');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $dataRaw = $request->input('data', []);
        $data = is_array($dataRaw) ? $dataRaw : [];
        $rendered = $emailTemplate->render($data);

        return $this->success($rendered, 'Email template preview generated successfully');
    }

    public function sendTest(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'data' => 'nullable|array',
        ]);

        $dataRaw = $request->input('data', []);
        $data = is_array($dataRaw) ? $dataRaw : [];
        $rendered = $emailTemplate->render($data);

        $toRaw = $request->input('email');
        $to = is_string($toRaw) ? $toRaw : '';

        try {
            \Mail::raw($rendered['text_body'] ?? strip_tags($rendered['body']), function ($message) use ($to, $rendered): void {
                $message->to($to)
                    ->subject($rendered['subject']);
            });

            return $this->success(null, 'Test email sent successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to send test email: '.$e->getMessage(), 500, [], 'EMAIL_SEND_ERROR');
        }
    }
}
