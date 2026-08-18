<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Services\CaptchaService;

class CaptchaController extends BaseApiController
{
    /**
     * Generate a new captcha challenge.
     */
    public function generate(): JsonResponse
    {
        $service = new CaptchaService;
        $captcha = $service->generate();

        return $this->success($captcha, 'Captcha generated successfully');
    }

    /**
     * Verify the captcha token and answer.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'answer' => 'required|string',
            'fingerprint' => 'nullable|string',
            'behavior' => 'nullable|array',
            'timing' => 'nullable|numeric',
        ]);

        $token = $request->string('token')->toString();
        $answer = $request->string('answer')->toString();
        $metadata = [
            'fingerprint' => $request->string('fingerprint')->toString(),
            'movements' => $request->input('behavior.movements'),
            'timing' => $request->float('timing'),
        ];

        $service = new CaptchaService;
        $valid = $service->verify($token, $answer, false, $metadata); // Don't consume on dry-run verify

        if (! $valid) {
            return $this->error('Invalid captcha or bot detected', 422);
        }

        return $this->success(null, 'Captcha verified');
    }

    /**
     * Get captcha settings for frontend.
     */
    public function settings(): JsonResponse
    {
        return $this->success([
            'enabled' => CaptchaService::isEnabled('login'),
            'enabled_login' => CaptchaService::isEnabled('login'),
            'enabled_register' => CaptchaService::isEnabled('register'),
            'enabled_forgot_password' => CaptchaService::isEnabled('forgot-password'),
            'enabled_comment' => CaptchaService::isEnabled('comment'),
            'enabled_contact' => CaptchaService::isEnabled('contact'),
            'method' => CaptchaService::getMethod(),
        ], 'Captcha settings retrieved');
    }
}
