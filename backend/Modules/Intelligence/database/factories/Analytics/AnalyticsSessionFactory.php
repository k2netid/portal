<?php

declare(strict_types=1);

namespace Modules\Intelligence\Analytics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Intelligence\Analytics\Models\AnalyticsSession;

/**
 * @extends Factory<AnalyticsSession>
 */
class AnalyticsSessionFactory extends Factory
{
    protected $model = AnalyticsSession::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid,
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'started_at' => now(),
            'last_activity_at' => now(),
        ];
    }
}
