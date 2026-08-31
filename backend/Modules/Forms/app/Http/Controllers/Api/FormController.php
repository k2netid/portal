<?php

namespace Modules\Forms\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Security\Rules\SafeUrl;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Core\System\Models\Webhook;
use Modules\Core\System\Services\CaptchaService;
use Modules\Forms\Events\FormSubmitted;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormField;
use Modules\Forms\Models\FormSubmission;
use Modules\Forms\Rules\FormRedirectUrl;
use Modules\Publishing\Contracts\MemberIdentityPort;

class FormController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['publicShow', 'submit', 'track']);
        $this->middleware('permission:view forms')->only(['index', 'show', 'stats']);
        $this->middleware('permission:manage forms')->except(['publicShow', 'submit', 'track', 'index', 'show', 'stats']);
    }

    /**
     * Public embed: active form definition without sensitive builder metadata.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $form->load(['fields' => static function ($query): void {
            $query->orderBy('sort_order');
        }]);

        $fields = $form->fields->map(static fn (FormField $field): array => [
            'name' => $field->name,
            'label' => $field->label,
            'type' => $field->type,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'options' => is_array($field->options) ? $field->options : [],
            'is_required' => (bool) $field->is_required,
        ])->values()->all();

        // Top-level JSON (legacy public clients expect unwrapped body, not BaseApiController envelope)
        return response()->json([
            'id' => $form->id,
            'slug' => $form->slug,
            'name' => $form->name,
            'description' => $form->description,
            'success_message' => $form->success_message,
            'redirect_url' => $this->safeRedirectForResponse($form->redirect_url),
            'settings' => $this->publicFormSettings($form),
            'fields' => $fields,
        ]);
    }

    /**
     * List forms.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Form::query();

        // Multi-tenancy scoping
        if (! $user->can('manage forms')) {
            $userId = (string) $user->id;
            $query->where('author_id', $userId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashed = $request->input('trashed');
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        $forms = $query->withCount(['submissions as submission_count', 'fields as fields_count'])->latest()->get();

        return $this->success($forms, 'Forms retrieved successfully');
    }

    /**
     * Create new form.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:frm_forms,slug',
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'redirect_url' => ['nullable', 'string', 'max:2048', new FormRedirectUrl],
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['author_id'] = (string) $user->id;

        /** @var Form $form */
        $form = Form::create($validated);

        return $this->success($form, 'Form created successfully', 201);
    }

    /**
     * Display the specified form.
     */
    public function show(Request $request, Form $form): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to view this form');
        }

        $form->load(['fields' => static function ($query): void {
            $query->orderBy('sort_order');
        }]);

        return $this->success($form, 'Form retrieved successfully');
    }

    /**
     * Append a new field to the form (admin builder).
     */
    public function addField(Request $request, Form $form): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,url,number,textarea,date,datetime,boolean,select,radio,multiselect,checkbox,file,image',
            'placeholder' => 'nullable|string|max:2048',
            'help_text' => 'nullable|string|max:2048',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'validation_rules.*' => 'string',
            'is_required' => 'sometimes|boolean',
        ]);

        $baseName = isset($validated['name']) && is_string($validated['name']) && trim($validated['name']) !== ''
            ? Str::slug($validated['name'], '_')
            : Str::slug($validated['label'], '_');
        if ($baseName === '') {
            $baseName = 'field';
        }

        $name = $baseName;
        $suffix = 2;
        while ($form->fields()->where('name', $name)->exists()) {
            $name = $baseName.'_'.$suffix;
            $suffix++;
        }

        $rulesIn = $validated['validation_rules'] ?? [];
        /** @var array<int, string> $validationRules */
        $validationRules = [];
        if (is_array($rulesIn)) {
            foreach ($rulesIn as $r) {
                if (is_string($r) && $r !== '') {
                    $validationRules[] = $r;
                }
            }
        }

        $type = (string) $validated['type'];
        $options = $this->normalizeFieldOptions($type, $validated['options'] ?? null);

        $maxOrderRaw = $form->fields()->max('sort_order');
        $maxOrder = is_numeric($maxOrderRaw) ? (int) $maxOrderRaw : 0;

        /** @var FormField $field */
        $field = $form->fields()->create([
            'name' => $name,
            'label' => $validated['label'],
            'type' => $type,
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'options' => $options,
            'validation_rules' => $validationRules,
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'sort_order' => $maxOrder + 1,
        ]);

        return $this->success($field, 'Field created successfully', 201);
    }

    /**
     * Update a single form field.
     */
    public function updateField(Request $request, Form $form, FormField $formField): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        if ((string) $formField->form_id !== (string) $form->id) {
            return $this->error('Field does not belong to this form', 404);
        }

        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:text,email,url,number,textarea,date,datetime,boolean,select,radio,multiselect,checkbox,file,image',
            'placeholder' => 'nullable|string|max:2048',
            'help_text' => 'nullable|string|max:2048',
            'options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'validation_rules.*' => 'string',
            'is_required' => 'sometimes|boolean',
        ]);

        if (array_key_exists('validation_rules', $validated)) {
            $rulesIn = $validated['validation_rules'];
            /** @var array<int, string> $validationRules */
            $validationRules = [];
            if (is_array($rulesIn)) {
                foreach ($rulesIn as $r) {
                    if (is_string($r) && $r !== '') {
                        $validationRules[] = $r;
                    }
                }
            }
            $validated['validation_rules'] = $validationRules;
        }

        if (array_key_exists('options', $validated)) {
            $type = isset($validated['type']) ? (string) $validated['type'] : (string) $formField->type;
            $validated['options'] = $this->normalizeFieldOptions($type, $validated['options']);
        } elseif (isset($validated['type'])) {
            $validated['options'] = $this->normalizeFieldOptions((string) $validated['type'], $formField->options);
        }

        $formField->fill($validated);
        $formField->save();

        return $this->success($formField->fresh(), 'Field updated successfully');
    }

    /**
     * Delete a form field.
     */
    public function deleteField(Request $request, Form $form, FormField $formField): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        if ((string) $formField->form_id !== (string) $form->id) {
            return $this->error('Field does not belong to this form', 404);
        }

        $formField->delete();

        return $this->success(null, 'Field deleted successfully');
    }

    /**
     * Persist field order (drag-and-drop).
     */
    public function reorderFields(Request $request, Form $form): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'string|exists:frm_form_fields,id',
        ]);

        /** @var array<int, string> $order */
        $order = array_values(array_map(
            static fn ($id): string => is_scalar($id) ? (string) $id : '',
            $validated['order']
        ));
        $order = array_values(array_filter($order, static fn ($id): bool => $id !== ''));

        if ($order === []) {
            return $this->success($form->fields()->orderBy('sort_order')->get(), 'Field order unchanged');
        }

        $ids = $form->fields()
            ->whereIn('id', $order)
            ->pluck('id')
            ->map(static fn ($id): string => is_scalar($id) ? (string) $id : '')
            ->filter(static fn (string $id): bool => $id !== '')
            ->values()
            ->all();

        if (count($ids) !== count($order)) {
            return $this->validationError(['order' => ['All IDs must belong to this form']], 'Invalid field order');
        }

        foreach ($order as $index => $fieldId) {
            FormField::where('id', $fieldId)->where('form_id', $form->id)->update(['sort_order' => $index + 1]);
        }

        $form->load(['fields' => static function ($query): void {
            $query->orderBy('sort_order');
        }]);

        return $this->success($form->fields, 'Field order updated');
    }

    /**
     * Update the specified form.
     */
    public function update(Request $request, Form $form): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to update this form');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:frm_forms,slug,'.$form->id,
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'redirect_url' => ['nullable', 'string', 'max:2048', new FormRedirectUrl],
            'settings' => 'nullable|array',
            'blocks' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $form->update($validated);

        return $this->success($form, 'Form updated successfully');
    }

    /**
     * Remove the specified form.
     */
    public function destroy(Request $request, Form $form): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        $form->delete();

        return $this->success(null, 'Form deleted successfully');
    }

    /**
     * Restore trashed form.
     *
     * @param  int|string  $id
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $form = Form::withTrashed()->findOrFail($id);
        /** @var Form $form */
        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to restore this form');
        }

        if (! $form->trashed()) {
            return $this->error('Form is not deleted', 400);
        }

        $form->restore();

        return $this->success(null, 'Form restored successfully');
    }

    /**
     * Permanently delete form.
     *
     * @param  int|string  $id
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $form = Form::withTrashed()->findOrFail($id);
        /** @var Form $form */
        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to delete this form');
        }

        // Delete fields
        $form->fields()->delete();
        // Delete submissions
        $form->submissions()->delete();

        $form->forceDelete();

        return $this->success(null, 'Form permanently deleted');
    }

    /**
     * Track form event.
     */
    public function track(Request $request, Form $form): JsonResponse
    {
        if (! $form->is_active) {
            return $this->validationError(['form' => ['Form is not active']], 'Form is not active');
        }

        $request->validate([
            'event' => 'required|in:view,start',
        ]);

        $eventRaw = $request->input('event');
        $event = is_string($eventRaw) ? $eventRaw : '';

        if ($event === 'view') {
            $form->incrementViewCount();
        } elseif ($event === 'start') {
            $form->incrementStartCount();
        }

        return $this->success(null, 'Event tracked successfully');
    }

    /**
     * Handle form submission.
     */
    public function submit(Request $request, Form $form): JsonResponse
    {
        if (! $form->is_active) {
            return $this->validationError(['form' => ['Form is not active']], 'Form is not active');
        }

        // Build validation rules from form fields
        $rules = [];
        $fields = $form->fields; // relationship
        if ($fields->isNotEmpty()) {
            $rules = $this->extractRulesFromFields($fields);
        }

        if ($rules === []) {
            return $this->validationError(['form' => ['Form has no valid fields configured']], 'Form has no valid fields configured');
        }

        // Check for captcha if enabled for contact forms
        if (CaptchaService::isEnabled('contact')) {
            $request->validate([
                'captcha_token' => 'required|string',
                'captcha_answer' => 'required|string',
            ]);

            $captchaService = new CaptchaService;
            $captchaTokenRaw = $request->input('captcha_token');
            $captchaAnswerRaw = $request->input('captcha_answer');
            $captchaToken = is_string($captchaTokenRaw) ? $captchaTokenRaw : '';
            $captchaAnswer = is_string($captchaAnswerRaw) ? $captchaAnswerRaw : '';

            if (! $captchaService->verify($captchaToken, $captchaAnswer)) {
                return $this->error('Invalid captcha', 422);
            }
        }

        $validated = $request->validate($rules);

        /** @var array<string, mixed> $submissionPayload */
        $submissionPayload = $validated;
        foreach ($fields as $field) {
            if (! in_array($field->type, ['file', 'image'], true)) {
                continue;
            }
            $key = (string) $field->name;
            if ($request->hasFile($key)) {
                $uploaded = $request->file($key);
                if ($uploaded !== null && $uploaded->isValid()) {
                    $path = $uploaded->store('form-uploads/'.$form->id, 'public');
                    $uploadedUrl = is_string($path) ? Storage::disk('public')->url($path) : '';
                    $submissionPayload[$key] = [
                        'type' => 'upload',
                        'path' => $path,
                        'url' => $uploadedUrl,
                        'original_name' => $uploaded->getClientOriginalName(),
                        'size' => $uploaded->getSize(),
                        'mime_type' => $uploaded->getMimeType(),
                    ];
                }
            } elseif (! $field->is_required) {
                $submissionPayload[$key] = null;
            }
        }

        $user = $request->user();
        /** @var User|null $user */
        $member = app(MemberIdentityPort::class)->current($request);

        // Create submission
        $submissionData = [
            'user_id' => $user?->id,
            'member_id' => $member?->id,
            'data' => $submissionPayload,
            'ip_address' => IpHelper::getClientIp($request),
            'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : '',
        ];

        /** @var FormSubmission $submission */
        $submission = $form->submissions()->create($submissionData);

        // Increment submission count
        $form->incrementSubmissionCount();

        // Send email notification if configured
        $settings = is_array($form->settings) ? $form->settings : [];
        if (isset($settings['email_notifications']) && $settings['email_notifications']) {
            $this->sendFormNotification($form, $submission);
        }

        FormSubmitted::dispatch($form, $submission, $submissionPayload);

        // Trigger webhook
        Webhook::triggerForEvent('form.submitted', [
            'form_id' => $form->id,
            'form_name' => (string) $form->name,
            'submission_id' => $submission->id,
            'data' => $submissionPayload,
        ]);

        return $this->success([
            'submission_id' => $submission->id,
            'redirect_url' => $this->safeRedirectForResponse($form->redirect_url),
        ], is_string($form->success_message) ? $form->success_message : 'Form submitted successfully', 201);
    }

    /**
     * Send form submission notification.
     */
    protected function sendFormNotification(Form $form, FormSubmission $submission): void
    {
        // Email notification logic
        // This can be enhanced with actual email sending
        Log::info('Form submission notification', [
            'form' => (string) $form->name,
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * Extract validation rules from form fields.
     *
     * @param  Collection<int, FormField>  $fields
     * @return array<string, array<int, string>>
     */
    private function extractRulesFromFields(Collection $fields): array
    {
        /** @var array<string, array<int, string>> $rules */
        $rules = [];

        foreach ($fields as $field) {
            $fieldId = (string) $field->name;
            $fieldRules = $this->sanitizePublicFieldRules($field);

            if ($fieldRules === []) {
                continue;
            }

            $rules[$fieldId] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @return array<int, string>
     */
    private function sanitizePublicFieldRules(FormField $field): array
    {
        $candidates = $field->getValidationRules();
        /** @var array<int, string> $out */
        $out = [];

        foreach ($candidates as $rule) {
            if ($rule === SafeUrl::class) {
                $out[] = SafeUrl::class;

                continue;
            }
            foreach (explode('|', $rule) as $part) {
                $part = trim($part);
                if ($part === '' || $part === 'url') {
                    continue;
                }
                if ($this->isAllowedPublicSubmissionRule($part)) {
                    $out[] = $part;
                }
            }
        }

        if ($field->type === 'url' && ! in_array(SafeUrl::class, $out, true)) {
            $out[] = SafeUrl::class;
        }

        if (! $field->is_required && ! in_array('nullable', $out, true) && ! in_array('required', $out, true)) {
            array_unshift($out, 'nullable');
        }

        return array_values(array_unique($out, SORT_REGULAR));
    }

    private function isAllowedPublicSubmissionRule(string $rule): bool
    {
        if (in_array($rule, ['required', 'nullable', 'sometimes', 'string', 'email', 'numeric', 'integer', 'boolean', 'date', 'array', 'file', 'image'], true)) {
            return true;
        }

        if (preg_match('/^(max|min):\d+$/', $rule) === 1) {
            return true;
        }

        if (preg_match('/^mimes:[a-zA-Z0-9,]+$/', $rule) === 1) {
            return true;
        }

        return preg_match('/^between:\d+,\d+$/', $rule) === 1;
    }

    /**
     * @param  array<string, mixed>|null  $raw
     * @return array<int, array{label: string, value: string}>|null
     */
    private function normalizeFieldOptions(string $type, mixed $raw): ?array
    {
        $needsOptions = in_array($type, ['select', 'radio', 'multiselect', 'checkbox'], true);
        if (! $needsOptions) {
            return null;
        }

        if (! is_array($raw) || $raw === []) {
            return [
                ['label' => 'Option 1', 'value' => 'option_1'],
                ['label' => 'Option 2', 'value' => 'option_2'],
            ];
        }

        /** @var array<int, array{label: string, value: string}> $out */
        $out = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $parts = array_map(trim(...), explode('|', $item, 2));
                $label = $parts[0];
                $value = $parts[1] ?? $parts[0];
                if ($label !== '' || $value !== '') {
                    $out[] = ['label' => $label !== '' ? $label : $value, 'value' => $value !== '' ? $value : Str::slug($label, '_')];
                }

                continue;
            }
            if (is_array($item)) {
                $label = isset($item['label']) && is_scalar($item['label']) ? (string) $item['label'] : '';
                $value = isset($item['value']) && is_scalar($item['value']) ? (string) $item['value'] : $label;
                if ($label !== '' || $value !== '') {
                    $out[] = ['label' => $label !== '' ? $label : $value, 'value' => $value !== '' ? $value : Str::slug($label, '_')];
                }
            }
        }

        return $out !== [] ? $out : [
            ['label' => 'Option 1', 'value' => 'option_1'],
            ['label' => 'Option 2', 'value' => 'option_2'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function publicFormSettings(Form $form): array
    {
        $settings = is_array($form->settings) ? $form->settings : [];
        $emailNotifications = $settings['email_notifications'] ?? false;

        return [
            'captcha_required' => CaptchaService::isEnabled('contact'),
            'email_notifications' => is_bool($emailNotifications) ? $emailNotifications : (bool) $emailNotifications,
        ];
    }

    private function safeRedirectForResponse(mixed $url): string
    {
        if (! is_string($url) || trim($url) === '') {
            return '';
        }

        $trimmed = trim($url);
        if (preg_match('#^\s*(javascript|data|vbscript)\s*:#i', $trimmed) === 1) {
            return '';
        }
        if (str_starts_with($trimmed, '/')) {
            if (str_starts_with($trimmed, '//')) {
                return '';
            }

            return $trimmed;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $scheme = parse_url($trimmed, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true) ? $trimmed : '';
    }

    /**
     * Duplicate the specified form.
     */
    public function duplicate(Request $request, Form $form): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage forms') && $form->author_id !== $user->id) {
            return $this->forbidden('You do not have permission to duplicate this form');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:frm_forms,slug',
            'copy_submissions' => 'boolean',
        ]);

        $titleRaw = $request->input('title');
        $title = is_string($titleRaw) ? $titleRaw : $form->name.' (Copy)';
        $slugRaw = $request->input('slug');
        $slug = is_string($slugRaw) ? $slugRaw : $form->slug.'-copy';
        $copySubmissions = $request->boolean('copy_submissions');

        $except = ['slug', 'name', 'submission_count', 'view_count', 'start_count'];
        /** @var Form $replicated */
        $replicated = $form->replicate($except);
        $replicated->name = $title;
        $replicated->slug = $slug;
        $replicated->is_active = false;
        $replicated->author_id = (string) $user->id;
        $replicated->submission_count = 0;
        $replicated->view_count = 0;
        $replicated->start_count = 0;
        $replicated->save();

        foreach ($form->fields()->orderBy('sort_order')->get() as $oldField) {
            $replicated->fields()->create([
                'name' => $oldField->name,
                'label' => $oldField->label,
                'type' => $oldField->type,
                'placeholder' => $oldField->placeholder,
                'help_text' => $oldField->help_text,
                'options' => is_array($oldField->options) ? $oldField->options : null,
                'validation_rules' => is_array($oldField->validation_rules) ? $oldField->validation_rules : [],
                'is_required' => (bool) $oldField->is_required,
                'sort_order' => (int) $oldField->sort_order,
            ]);
        }

        if ($copySubmissions) {
            // Bulk insert for performance
            $submissionsData = $form->submissions()->get()->map(fn (FormSubmission $submission) => [
                'form_id' => $replicated->id,
                'user_id' => $submission->user_id,
                'data' => json_encode($submission->data),
                'ip_address' => $submission->ip_address,
                'user_agent' => $submission->user_agent,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ])->toArray();

            if (! empty($submissionsData)) {
                FormSubmission::insert($submissionsData);
                $replicated->submission_count = count($submissionsData);
                $replicated->save();
            }
        }

        return $this->success($replicated, 'Form duplicated successfully', 201);
    }

    /**
     * Bulk actions for forms.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:cms_forms,id',
            'action' => 'required|in:delete,restore,force_delete',
        ]);

        $idsRaw = $request->input('ids');
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $actionRaw = $request->input('action');
        $action = is_string($actionRaw) ? $actionRaw : '';

        try {
            if ($action === 'delete') {
                $query = Form::whereIn('id', $ids);

                if (! $user->can('manage forms')) {
                    $query->where('author_id', $user->id);
                }

                $query->delete();

                return $this->success(null, 'Selected forms deleted successfully');
            } elseif ($action === 'restore') {
                $query = Form::withTrashed()->whereIn('id', $ids);
                if (! $user->can('manage forms')) {
                    $query->where('author_id', $user->id);
                }
                $query->restore();

                return $this->success(null, 'Selected forms restored successfully');
            } elseif ($action === 'force_delete') {
                $query = Form::withTrashed()->whereIn('id', $ids);
                if (! $user->can('manage forms')) {
                    $query->where('author_id', $user->id);
                }

                $forms = $query->get();
                foreach ($forms as $form) {
                    $form->submissions()->delete();
                    $form->forceDelete();
                }

                return $this->success(null, 'Selected forms permanently deleted');
            }
        } catch (\Exception $e) {
            return $this->error('Bulk action failed: '.$e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }
}
