<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Unit;

use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Registries\HookRegistry;
use Tests\TestCase;

class HookRegistryTest extends TestCase
{
    /**
     * Test registering and executing custom actions.
     */
    public function test_can_register_and_trigger_actions(): void
    {
        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        $executionCount = 0;
        $registry->listen('on_test_action', function () use (&$executionCount): void {
            $executionCount++;
        });

        $registry->action('on_test_action');

        $this->assertEquals(1, $executionCount);
    }

    /**
     * Test registering and running custom filters sequentially.
     */
    public function test_can_register_and_run_filters(): void
    {
        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        // Filter that appends text
        $registry->listen('format_name', function (string $name): string {
            return $name.' the Great';
        });

        // Another filter with lower priority that capitalizes it
        $registry->listen('format_name', function (string $name): string {
            return strtoupper($name);
        }, 15);

        $result = $registry->filter('format_name', 'Alexander');

        // Initial: 'Alexander'
        // Filter 1: 'Alexander the Great'
        // Filter 2: 'ALEXANDER THE GREAT'
        $this->assertEquals('ALEXANDER THE GREAT', $result);
    }

    /**
     * Test that the global Hook facade works exactly as designed.
     */
    public function test_hook_facade_alias_integration(): void
    {
        $executionFlag = false;

        // Register hook via global Hook alias
        Hook::listen('on_facade_test', function () use (&$executionFlag): void {
            $executionFlag = true;
        });

        // Trigger action via global Hook alias
        Hook::action('on_facade_test');

        $this->assertTrue($executionFlag);
    }

    /**
     * Test filters with multiple arguments.
     */
    public function test_filters_can_receive_extra_arguments(): void
    {
        Hook::listen('calculate_price', function (float $price, float $taxRate, float $discount): float {
            return ($price - $discount) * (1 + $taxRate);
        });

        $finalPrice = Hook::filter('calculate_price', 100.0, 0.1, 10.0);

        // (100.0 - 10.0) * (1 + 0.1) = 90.0 * 1.1 = 99.0
        $this->assertEqualsWithDelta(99.0, $finalPrice, 0.0001);
    }

    /**
     * Test that a crashing action callback is isolated and does not stop other listeners.
     */
    public function test_hook_action_error_isolation_prevents_system_crash(): void
    {
        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        $executionFlag = false;

        // Register a listener that throws a fatal exception
        $registry->listen('on_crash_action', function (): void {
            throw new \RuntimeException('Simulated plugin failure');
        });

        // Register a second listener that should still execute
        $registry->listen('on_crash_action', function () use (&$executionFlag): void {
            $executionFlag = true;
        }, 15);

        // This call should not crash the PHP execution
        $registry->action('on_crash_action');

        $this->assertTrue($executionFlag);
    }

    /**
     * Test that a crashing filter callback is isolated and the pipeline retains the previous state.
     */
    public function test_hook_filter_error_isolation_retains_previous_state(): void
    {
        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        // Filter 1: Valid transformation
        $registry->listen('format_isolated', function (string $val): string {
            return $val.' Step1';
        });

        // Filter 2: Crashes
        $registry->listen('format_isolated', function (string $val): string {
            throw new \RuntimeException('Simulated filter failure');
        }, 12);

        // Filter 3: Valid transformation, should receive "Alexander Step1"
        $registry->listen('format_isolated', function (string $val): string {
            return $val.' Step3';
        }, 15);

        $result = $registry->filter('format_isolated', 'Alexander');

        $this->assertEquals('Alexander Step1 Step3', $result);
    }
}
