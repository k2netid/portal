<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Core\System\Models\Setting;

class CaptchaService
{
    protected string $method;

    protected int $ttl = 300; // 5 minutes

    public function __construct()
    {
        $methodRaw = Setting::get('captcha_method', 'slider');
        $this->method = is_string($methodRaw) ? $methodRaw : 'slider';
    }

    /**
     * Generate a captcha challenge based on the configured method.
     *
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        return match ($this->method) {
            'math' => $this->generateMathChallenge(),
            'image' => $this->generateImageChallenge(),
            default => $this->generateSliderChallenge(),
        };
    }

    /**
     * Verify the captcha answer.
     *
     * @param  array<string, mixed>  $metadata  Additional data for validation (behavior, timing, fingerprint)
     */
    public function verify(string $token, string $answer, bool $consume = true, array $metadata = []): bool
    {
        $cacheKey = "captcha:{$token}";
        /** @var array{method: string, answer?: int, code?: string, target?: int, created_at?: float}|null $stored */
        $stored = Cache::get($cacheKey);

        if (! is_array($stored)) {
            return false;
        }

        // Timing validation: Must take at least 800ms to solve (too fast = bot)
        if (isset($stored['created_at'])) {
            $elapsed = microtime(true) - $stored['created_at'];
            if ($elapsed < 0.8) {
                return false;
            }
        }

        // Remove from cache after verification attempt if consume is true
        if ($consume) {
            Cache::forget($cacheKey);
        }

        return match ($stored['method']) {
            'math' => $this->verifyMath($stored, $answer),
            'image' => $this->verifyImage($stored, $answer),
            default => $this->verifySlider($stored, $answer, $metadata),
        };
    }

    /**
     * Playwright sends X-E2E-Captcha-Bypass; never honoured in production.
     */
    public static function isE2eBypassed(): bool
    {
        if (! app()->environment(['local', 'testing'])) {
            return false;
        }

        $token = (string) env('E2E_CAPTCHA_BYPASS_TOKEN', 'local-e2e');
        if ($token === '') {
            return false;
        }

        $header = (string) request()->header('X-E2E-Captcha-Bypass', '');

        return $header !== '' && hash_equals($token, $header);
    }

    /**
     * Check if captcha is enabled for the given action.
     */
    public static function isEnabled(string $action = 'login'): bool
    {
        if (self::isE2eBypassed()) {
            return false;
        }

        if (! Setting::get('enable_captcha', false)) {
            return false;
        }

        return match ($action) {
            'login' => (bool) Setting::get('captcha_on_login', true),
            'register' => (bool) Setting::get('captcha_on_register', true),
            'comment' => (bool) Setting::get('comments.security.guest_captcha', true),
            'contact', 'message' => (bool) Setting::get('captcha_on_contact', true),
            'forgot-password' => (bool) Setting::get('captcha_on_forgot_password', true),
            default => true,
        };
    }

    /**
     * Get the current captcha method.
     */
    public static function getMethod(): string
    {
        $methodRaw = Setting::get('captcha_method', 'slider');

        return is_string($methodRaw) ? $methodRaw : 'slider';
    }

    // ========================================
    // Slider Captcha
    // ========================================

    /**
     * @return array<string, mixed>
     */
    protected function generateSliderChallenge(): array
    {
        $token = Str::random(32);
        $targetPosition = random_int(15, 75); // Target position 15-75% to fit within bounds on all screens

        Cache::put("captcha:{$token}", [
            'method' => 'slider',
            'target' => $targetPosition,
            'created_at' => microtime(true),
        ], $this->ttl);

        return [
            'method' => 'slider',
            'token' => $token,
            'target' => $targetPosition,
        ];
    }

    /**
     * @param  array{method: string, target?: int}  $stored
     * @param  array<string, mixed>  $metadata
     */
    protected function verifySlider(array $stored, string $answer, array $metadata = []): bool
    {
        $userPosition = (int) $answer;
        $target = (int) ($stored['target'] ?? 0);
        $tolerance = 1; // ±1% tolerance (strict)

        // Basic check
        if (abs($userPosition - $target) > $tolerance) {
            return false;
        }

        // Behavior validation: verify trajectory if provided
        if (isset($metadata['movements']) && is_array($metadata['movements'])) {
            $movements = $metadata['movements'];

            // Bot check 1: Too few points (human drag usually generates many events)
            if (count($movements) < 5) {
                return false;
            }

            // Bot check 2: Perfectly linear/instant movement (simplified check)
            // If the time between first and last point is near zero, it's a bot
            /** @var array{t?: mixed} $first */
            $first = $movements[0];
            /** @var array{t?: mixed} $last */
            $last = end($movements);

            $firstT = isset($first['t']) && is_numeric($first['t']) ? (float) $first['t'] : 0.0;
            $lastT = isset($last['t']) && is_numeric($last['t']) ? (float) $last['t'] : 0.0;

            if (($lastT - $firstT) < 200) {
                return false;
            }
        }

        return true;
    }

    // ========================================
    // Math Captcha
    // ========================================

    /**
     * @return array<string, mixed>
     */
    protected function generateMathChallenge(): array
    {
        $token = Str::random(32);

        // Simplify: only addition, numbers 1-9
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $answer = $a + $b;

        Cache::put("captcha:{$token}", [
            'method' => 'math',
            'answer' => $answer,
            'created_at' => microtime(true),
        ], $this->ttl);

        return [
            'method' => 'math',
            'token' => $token,
            'question' => "{$a} + {$b} = ?",
        ];
    }

    /**
     * @param  array{method: string, answer?: int}  $stored
     */
    protected function verifyMath(array $stored, string $answer): bool
    {
        return (int) $answer === ($stored['answer'] ?? 0);
    }

    // ========================================
    // Image Captcha
    // ========================================

    /**
     * @return array<string, mixed>
     */
    protected function generateImageChallenge(): array
    {
        $token = Str::random(32);
        $code = strtoupper(Str::random(6)); // 6 uppercase characters

        Cache::put("captcha:{$token}", [
            'method' => 'image',
            'code' => $code,
            'created_at' => microtime(true),
        ], $this->ttl);

        $image = $this->createCaptchaImage($code);

        return [
            'method' => 'image',
            'token' => $token,
            'image' => 'data:image/png;base64,'.base64_encode($image),
        ];
    }

    protected function createCaptchaImage(string $code): string
    {
        $width = 140; // Reduced from 180
        $height = 50; // Reduced from 60

        // Create image
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $bgColor = imagecolorallocate($image, 245, 245, 245) ?: 0;
        $textColors = [
            imagecolorallocate($image, 50, 50, 150) ?: 0,
            imagecolorallocate($image, 150, 50, 50) ?: 0,
            imagecolorallocate($image, 50, 150, 50) ?: 0,
            imagecolorallocate($image, 100, 100, 100) ?: 0,
        ];
        $lineColor = imagecolorallocate($image, 200, 200, 200) ?: 0;
        $noiseColor = imagecolorallocate($image, 180, 180, 180) ?: 0;

        // Fill background
        imagefill($image, 0, 0, $bgColor);

        // Add noise dots
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noiseColor);
        }

        // Add lines
        for ($i = 0; $i < 5; $i++) {
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $lineColor
            );
        }

        // Use TrueType font for larger text, allow config override for testing
        /** @var string $fontPath */
        $fontPath = config('app.captcha_font_path', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf');

        // Fallback if system font not found - normally we'd check file_exists
        $useTtf = file_exists($fontPath);

        $fontSize = 24; // Much larger font size

        // Improve spacing logic for TTF
        // Calculate approximation of text width
        $boxWidth = $width * 0.8;
        $charPadding = $boxWidth / strlen($code);
        $startX = ($width - $boxWidth) / 2;

        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];

            $angle = random_int(-15, 15);
            $color = $textColors[array_rand($textColors)];

            $x = $startX + ($i * $charPadding) + random_int(-2, 2);
            $y = ($height / 2) + ($fontSize / 2) + random_int(-2, 2); // Baseline position

            if ($useTtf) {
                imagettftext($image, $fontSize, $angle, (int) $x, $y, $color, $fontPath, $char);
            } else {
                // Legacy fallback (should ideally not happen given our search)
                // Use built-in font scaled up? No, just center it
                $baseFont = 5;
                $fw = imagefontwidth($baseFont);
                $fh = imagefontheight($baseFont);
                // Center roughly
                $lx = $x + ($charPadding / 2) - ($fw / 2);
                $ly = ($height - $fh) / 2;
                imagestring($image, $baseFont, (int) $lx, (int) $ly, $char, $color);
            }
        }

        // Output to string
        ob_start();
        imagepng($image);
        $imageData = (string) ob_get_clean();
        imagedestroy($image);

        return $imageData;
    }

    /**
     * @param  array{method: string, code?: string}  $stored
     */
    protected function verifyImage(array $stored, string $answer): bool
    {
        return strtoupper(trim($answer)) === ($stored['code'] ?? '');
    }
}
