<?php

declare(strict_types=1);

namespace Modules\Intelligence\Analytics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Intelligence\Analytics\Models\AnalyticsEvent;

/**
 * @extends Factory<AnalyticsEvent>
 */
class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid,
            'event_type' => 'click',
            'event_name' => 'Button Click',
            'occurred_at' => now(),
        ];
    }
}
