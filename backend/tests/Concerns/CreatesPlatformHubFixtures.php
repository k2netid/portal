<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Str;
use Modules\Operational\Platform\Models\PlatformCustomer;
use Modules\Operational\Platform\Models\PlatformPackage;
use Modules\Operational\Platform\Models\PlatformProduct;
use Modules\Operational\Platform\Models\PlatformSubscription;

trait CreatesPlatformHubFixtures
{
    protected function ensurePlatformProduct(string $id = 'platform', string $name = 'Jejakawan Platform'): PlatformProduct
    {
        return PlatformProduct::query()->firstOrCreate(
            ['id' => $id],
            ['name' => $name, 'is_active' => true]
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createPlatformPackage(array $attributes = []): PlatformPackage
    {
        $productId = (string) ($attributes['product_id'] ?? 'platform');
        $this->ensurePlatformProduct($productId);

        return PlatformPackage::create(array_merge([
            'id' => Str::uuid()->toString(),
            'product_id' => $productId,
            'name' => 'Test Package',
            'price_monthly' => 10.00,
            'price_yearly' => 100.00,
            'user_limit' => 10,
            'storage_limit_mb' => 1024,
            'ai_monthly_token_limit' => 0,
            'features' => ['member_portal' => true, 'cms_publishing' => true],
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createPlatformCustomer(array $attributes = []): PlatformCustomer
    {
        return PlatformCustomer::create(array_merge([
            'name' => 'Test Customer',
            'email' => Str::uuid().'@example.com',
            'status' => 'active',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createPlatformSubscription(array $attributes = []): PlatformSubscription
    {
        $package = $attributes['package'] ?? null;
        unset($attributes['package']);

        if (! $package instanceof PlatformPackage) {
            $packageId = $attributes['package_id'] ?? null;
            if ($packageId !== null) {
                $package = PlatformPackage::query()->findOrFail($packageId);
            } else {
                $package = $this->createPlatformPackage();
            }
        }

        $customerId = $attributes['customer_id'] ?? $this->createPlatformCustomer()->id;
        $productId = $attributes['product_id'] ?? $package->product_id;

        return PlatformSubscription::create(array_merge([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'package_id' => $package->id,
            'name' => 'Test Subscription',
            'domain' => Str::uuid().'.test',
            'license_key' => 'LIC-'.strtoupper(Str::random(12)),
            'status' => 'active',
            'user_limit' => $package->user_limit,
            'storage_limit_mb' => $package->storage_limit_mb,
        ], $attributes));
    }
}
