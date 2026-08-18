<?php

declare(strict_types=1);

namespace Modules\Intelligence\Analytics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Intelligence\Analytics\Models\AnalyticsVisit;

/**
 * @extends Factory<AnalyticsVisit>
 */
class AnalyticsVisitFactory extends Factory
{
    protected $model = AnalyticsVisit::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid,
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'url' => $this->faker->url,
            'method' => 'GET',
            'status_code' => 200,
            'visited_at' => now(),
        ];
    }
}
