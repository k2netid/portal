<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Member\Models\Member;
use Modules\Newsletter\Models\NewsletterSubscriber;

class ReaderNewsletterController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        $subscriber = NewsletterSubscriber::query()
            ->withTrashed()
            ->where('email', $member->email)
            ->first();

        if ($subscriber === null) {
            return $this->success([
                'subscribed' => false,
                'status' => null,
                'subscribed_at' => null,
            ], 'Newsletter status');
        }

        return $this->success([
            'subscribed' => $subscriber->status === 'subscribed' && $subscriber->deleted_at === null,
            'status' => $subscriber->status,
            'subscribed_at' => $subscriber->subscribed_at,
            'unsubscribed_at' => $subscriber->unsubscribed_at,
        ], 'Newsletter status');
    }

    public function update(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if ($member === null) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'subscribe' => 'required|boolean',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $wantsSubscribe = (bool) $validated['subscribe'];

        if ($wantsSubscribe) {
            $subscriber = NewsletterSubscriber::query()
                ->withTrashed()
                ->where('email', $member->email)
                ->first();

            if ($subscriber !== null) {
                if ($subscriber->trashed()) {
                    $subscriber->restore();
                }
                $subscriber->update([
                    'status' => 'subscribed',
                    'name' => $member->name,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'source' => 'member_portal',
                    'ip_address' => IpHelper::getClientIp($request),
                    'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : null,
                ]);
            } else {
                $subscriber = NewsletterSubscriber::query()->create([
                    'email' => $member->email,
                    'name' => $member->name,
                    'status' => 'subscribed',
                    'subscribed_at' => now(),
                    'source' => 'member_portal',
                    'ip_address' => IpHelper::getClientIp($request),
                    'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : null,
                ]);
            }

            return $this->success([
                'subscribed' => true,
                'status' => 'subscribed',
                'subscribed_at' => $subscriber->subscribed_at,
            ], 'Subscribed to newsletter');
        }

        $subscriber = NewsletterSubscriber::query()->where('email', $member->email)->first();
        if ($subscriber !== null) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return $this->success([
            'subscribed' => false,
            'status' => 'unsubscribed',
        ], 'Unsubscribed from newsletter');
    }

    private function member(Request $request): ?Member
    {
        $user = $request->user('member');

        return $user instanceof Member ? $user : null;
    }
}
