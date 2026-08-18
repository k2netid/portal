<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\KycDocument;
use Modules\Core\System\Models\KycSubmission;
use Modules\Core\System\Models\User;

class KycService
{
    /** @var list<string> */
    public const IDENTITY_TYPES = [KycDocument::TYPE_ID_CARD, KycDocument::TYPE_PASSPORT];

    /**
     * @return array{kyc_level: string, onboarding_step: int, email_verified: bool, has_phone: bool, submission: array<string, mixed>|null}
     */
    /**
     * @return array<string, mixed>
     */
    public function statusFor(User $user): array
    {
        $submission = $this->activeSubmission($user);

        return [
            'kyc_level' => $user->kyc_level ?? 'level_0',
            'onboarding_step' => (int) ($user->onboarding_step ?? 0),
            'email_verified' => $user->hasVerifiedEmail(),
            'has_phone' => filled($user->phone),
            'submission' => $submission ? $this->serializeSubmission($submission) : null,
        ];
    }

    /**
     * @param  array{name: string, phone: string, location?: string|null}  $data
     */
    /**
     * @param  array<string, mixed>  $data
     */
    public function completeBasic(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'location' => $data['location'] ?? $user->location,
        ]);
        $user->kyc_level = 'level_1';
        $user->onboarding_step = max((int) ($user->onboarding_step ?? 0), 1);
        $user->save();

        return $user->fresh() ?? $user;
    }

    public function completeContact(User $user): User
    {
        if (! $user->hasVerifiedEmail()) {
            throw new \InvalidArgumentException('Email must be verified before level 2.');
        }
        if (! filled($user->phone)) {
            throw new \InvalidArgumentException('Phone number is required before level 2.');
        }

        $user->kyc_level = $this->maxLevel($user->kyc_level ?? 'level_0', 'level_2');
        $user->onboarding_step = max((int) ($user->onboarding_step ?? 0), 2);
        $user->save();

        return $user->fresh() ?? $user;
    }

    public function getOrCreateDraft(User $user): KycSubmission
    {
        if (KycSubmission::query()->where('user_id', $user->id)->where('status', KycSubmission::STATUS_PENDING)->exists()) {
            throw new \InvalidArgumentException('A submission is already pending review.');
        }

        $draft = KycSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', KycSubmission::STATUS_DRAFT)
            ->latest()
            ->first();

        if ($draft) {
            return $draft->load('documents');
        }

        return KycSubmission::create([
            'user_id' => $user->id,
            'status' => KycSubmission::STATUS_DRAFT,
        ])->load('documents');
    }

    public function uploadDocument(User $user, UploadedFile $file, string $type): KycDocument
    {
        if (! in_array($type, [KycDocument::TYPE_ID_CARD, KycDocument::TYPE_PASSPORT, KycDocument::TYPE_SELFIE], true)) {
            throw new \InvalidArgumentException('Invalid document type.');
        }

        if ($this->levelRank($user->kyc_level ?? 'level_0') < 2) {
            throw new \InvalidArgumentException('Complete contact verification (level 2) before uploading documents.');
        }

        $submission = $this->getOrCreateDraft($user);

        $existing = KycDocument::query()
            ->where('submission_id', $submission->id)
            ->where('type', $type)
            ->first();

        $path = $file->store('kyc/'.$user->id, 'local');
        if ($path === false) {
            throw new \RuntimeException('Failed to store document.');
        }

        if ($existing && Storage::disk('local')->exists($existing->file_path)) {
            Storage::disk('local')->delete($existing->file_path);
        }

        if ($existing) {
            $existing->update([
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => (int) $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            return $existing->fresh() ?? $existing;
        }

        return KycDocument::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'type' => $type,
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => (int) $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    public function submitForReview(User $user): KycSubmission
    {
        $submission = $this->getOrCreateDraft($user);
        $submission->load('documents');

        $types = array_values(array_filter(
            $submission->documents->pluck('type')->all(),
            static fn (mixed $t): bool => is_string($t),
        ));
        if (count(array_intersect($types, self::IDENTITY_TYPES)) === 0) {
            throw new \InvalidArgumentException('Upload a national ID or passport before submitting.');
        }

        $submission->update([
            'status' => KycSubmission::STATUS_PENDING,
            'submitted_at' => now(),
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $user->onboarding_step = max((int) ($user->onboarding_step ?? 0), 2);
        $user->save();

        return $submission->fresh(['documents', 'user']) ?? $submission;
    }

    public function approve(User $reviewer, KycSubmission $submission, ?string $note = null): KycSubmission
    {
        return DB::transaction(function () use ($reviewer, $submission, $note): KycSubmission {
            $user = $submission->user;
            if (! $user instanceof User) {
                throw new \RuntimeException('KYC submission has no associated user.');
            }
            $submission->update([
                'status' => KycSubmission::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $user->kyc_level = 'level_3';
            $user->onboarding_step = 3;
            $user->save();

            SecurityLog::log('kyc_approved', $user, null, $note ?? 'KYC submission approved', [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewer->id,
            ]);

            return $submission->fresh(['documents', 'user', 'reviewer']) ?? $submission;
        });
    }

    public function reject(User $reviewer, KycSubmission $submission, string $reason): KycSubmission
    {
        return DB::transaction(function () use ($reviewer, $submission, $reason): KycSubmission {
            $user = $submission->user;
            if (! $user instanceof User) {
                throw new \RuntimeException('KYC submission has no associated user.');
            }
            $submission->update([
                'status' => KycSubmission::STATUS_REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            if ($this->levelRank($user->kyc_level ?? 'level_0') >= 3) {
                $user->kyc_level = 'level_2';
            }
            $user->save();

            SecurityLog::log('kyc_rejected', $user, null, $reason, [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewer->id,
            ]);

            return $submission->fresh(['documents', 'user', 'reviewer']) ?? $submission;
        });
    }

    public function activeSubmission(User $user): ?KycSubmission
    {
        return KycSubmission::query()
            ->where('user_id', $user->id)
            ->latest()
            ->with('documents')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function serializeSubmission(KycSubmission $submission): array
    {
        return [
            'id' => $submission->id,
            'status' => $submission->status,
            'rejection_reason' => $submission->rejection_reason,
            'submitted_at' => $submission->submitted_at?->toIso8601String(),
            'reviewed_at' => $submission->reviewed_at?->toIso8601String(),
            'documents' => $submission->documents->map(fn (KycDocument $d) => [
                'id' => $d->id,
                'type' => $d->type,
                'original_name' => $d->original_name,
                'mime_type' => $d->mime_type,
                'size_bytes' => $d->size_bytes,
                'uploaded_at' => $d->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    private function levelRank(string $level): int
    {
        return match ($level) {
            'level_1' => 1,
            'level_2' => 2,
            'level_3' => 3,
            default => 0,
        };
    }

    private function maxLevel(string $current, string $target): string
    {
        return $this->levelRank($current) >= $this->levelRank($target) ? $current : $target;
    }
}
