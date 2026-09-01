<?php

declare(strict_types=1);

namespace Modules\Member\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Services\CaptchaService;

final class MemberCaptchaGuard
{
    /**
     * @throws ValidationException
     */
    public static function assert(Request $request, string $action): void
    {
        if (! CaptchaService::isEnabled($action)) {
            return;
        }

        $captcha = new CaptchaService;
        $token = is_string($request->input('captcha_token')) ? $request->input('captcha_token') : '';
        $answer = is_string($request->input('captcha_answer')) ? $request->input('captcha_answer') : '';

        if ($token === '' || $answer === '') {
            throw ValidationException::withMessages([
                'captcha' => ['Captcha verification is required.'],
            ]);
        }

        if (! $captcha->verify($token, $answer)) {
            throw ValidationException::withMessages([
                'captcha' => ['Invalid captcha verification. Please try again.'],
            ]);
        }
    }
}
