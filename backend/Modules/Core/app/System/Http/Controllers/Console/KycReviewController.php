<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\KycDocument;
use Modules\Core\System\Models\KycSubmission;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\KycService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KycReviewController extends BaseApiController
{
    public function __construct(private readonly KycService $kyc) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', KycSubmission::STATUS_PENDING);
        $limit = min(max((int) $request->query('limit', 50), 1), 100);

        $query = KycSubmission::query()
            ->with(['user:id,name,email,kyc_level', 'documents'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $submissions = $query->limit($limit)->get()->map(fn (KycSubmission $s) => [
            'id' => $s->id,
            'status' => $s->status,
            'submitted_at' => $s->submitted_at?->toIso8601String(),
            'reviewed_at' => $s->reviewed_at?->toIso8601String(),
            'rejection_reason' => $s->rejection_reason,
            'user' => $s->user ? [
                'id' => $s->user->id,
                'name' => $s->user->name,
                'email' => $s->user->email,
                'kyc_level' => $s->user->kyc_level,
            ] : null,
            'documents' => $s->documents->map(fn (KycDocument $d) => [
                'id' => $d->id,
                'type' => $d->type,
                'original_name' => $d->original_name,
            ]),
        ]);

        return $this->success($submissions);
    }

    public function show(KycSubmission $submission): JsonResponse
    {
        $submission->load(['user', 'documents', 'reviewer:id,name,email']);

        return $this->success([
            'submission' => $this->kyc->serializeSubmission($submission),
            'user' => $submission->user,
            'reviewer' => $submission->reviewer,
        ]);
    }

    public function approve(Request $request, KycSubmission $submission): JsonResponse
    {
        $reviewer = $request->user();
        if (! $reviewer instanceof User) {
            return $this->unauthorized();
        }

        if ($submission->status !== KycSubmission::STATUS_PENDING) {
            return $this->error('Only pending submissions can be approved', 422);
        }

        $validated = $request->validate(['note' => 'nullable|string|max:500']);
        $submission = $this->kyc->approve($reviewer, $submission, $validated['note'] ?? null);

        return $this->success($this->kyc->serializeSubmission($submission), 'KYC approved — user is now Level 3');
    }

    public function reject(Request $request, KycSubmission $submission): JsonResponse
    {
        $reviewer = $request->user();
        if (! $reviewer instanceof User) {
            return $this->unauthorized();
        }

        if ($submission->status !== KycSubmission::STATUS_PENDING) {
            return $this->error('Only pending submissions can be rejected', 422);
        }

        $validated = $request->validate(['reason' => 'required|string|min:5|max:1000']);
        $submission = $this->kyc->reject($reviewer, $submission, $validated['reason']);

        return $this->success($this->kyc->serializeSubmission($submission), 'KYC rejected');
    }

    public function downloadDocument(KycSubmission $submission, KycDocument $document): StreamedResponse|JsonResponse
    {
        if ($document->submission_id !== $submission->id) {
            return $this->notFound('Document');
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            return $this->notFound('Document file');
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }
}
