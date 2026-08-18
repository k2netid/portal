<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\KycDocument;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\KycService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileKycController extends BaseApiController
{
    public function __construct(private readonly KycService $kyc) {}

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        return $this->success($this->kyc->statusFor($user));
    }

    public function completeBasic(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'location' => 'nullable|string|max:255',
        ]);

        $user = $this->kyc->completeBasic($user, $validated);

        return $this->success($this->kyc->statusFor($user), 'Basic profile verified (Level 1)');
    }

    public function completeContact(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        try {
            $user = $this->kyc->completeContact($user);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($this->kyc->statusFor($user), 'Contact verification complete (Level 2)');
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'type' => 'required|string|in:id_card,passport,selfie',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('document');
        if (! ($file instanceof UploadedFile)) {
            return $this->error('Invalid file upload', 422);
        }

        try {
            $doc = $this->kyc->uploadDocument($user, $file, $validated['type']);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 500);
        }

        return $this->success([
            'document' => [
                'id' => $doc->id,
                'type' => $doc->type,
                'original_name' => $doc->original_name,
            ],
            'status' => $this->kyc->statusFor($user->fresh() ?? $user),
        ], 'Document uploaded');
    }

    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        try {
            $submission = $this->kyc->submitForReview($user);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'submission' => $this->kyc->serializeSubmission($submission),
            'status' => $this->kyc->statusFor($user->fresh() ?? $user),
        ], 'Submitted for review');
    }

    public function downloadOwnDocument(Request $request, KycDocument $document): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized();
        }

        if ($document->user_id !== $user->id) {
            return $this->forbidden('You cannot access this document');
        }

        return $this->streamDocument($document);
    }

    private function streamDocument(KycDocument $document): StreamedResponse|JsonResponse
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            return $this->notFound('Document file');
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }
}
