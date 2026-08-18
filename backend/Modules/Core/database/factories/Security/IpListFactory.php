<?php

declare(strict_types=1);

namespace Modules\Core\Security\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Security\Models\IpList;

/**
 * @extends Factory<IpList>
 */
class IpListFactory extends Factory
{
    protected $model = IpList::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4(),
            'type' => 'blocklist',
            'reason' => null,
            'created_by' => null,
        ];
    }
}
