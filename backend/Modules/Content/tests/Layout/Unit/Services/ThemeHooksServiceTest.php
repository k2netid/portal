<?php

namespace Modules\Content\Layout\Tests\Unit\Services;

use Modules\Content\Layout\Services\ThemeHooksService;
use Tests\TestCase;

class ThemeHooksServiceTest extends TestCase
{
    protected ThemeHooksService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThemeHooksService;
    }

    public function test_add_and_do_action(): void
    {
        $flag = false;
        $this->service->addAction('test.action', function () use (&$flag): void {
            $flag = true;
        });

        $this->service->doAction('test.action');
        $this->assertTrue($flag);
    }

    public function test_action_priority(): void
    {
        $output = [];
        $this->service->addAction('test.priority', function () use (&$output): void {
            $output[] = 'second';
        }, 20);

        $this->service->addAction('test.priority', function () use (&$output): void {
            $output[] = 'first';
        }, 10);

        $this->service->doAction('test.priority');
        $this->assertEquals(['first', 'second'], $output);
    }

    public function test_do_action_with_args(): void
    {
        $received = null;
        $this->service->addAction('test.args', function ($arg) use (&$received): void {
            $received = $arg;
        });

        $this->service->doAction('test.args', 'hello');
        $this->assertEquals('hello', $received);
    }

    public function test_do_action_no_registered(): void
    {
        // Should not throw
        $this->service->doAction('non.existent');
        $this->assertFalse($this->service->hasAction('non.existent'));
    }

    public function test_add_and_apply_filter(): void
    {
        $this->service->addFilter('test.filter', fn ($value) => $value.' modified');

        $result = $this->service->applyFilter('test.filter', 'original');
        $this->assertEquals('original modified', $result);
    }

    public function test_filter_priority(): void
    {
        $this->service->addFilter('test.priority', fn ($value) => $value.' 1', 20);

        $this->service->addFilter('test.priority', fn ($value) => $value.' 2', 10);

        $result = $this->service->applyFilter('test.priority', 'start');
        // 10 runs first (start 2), then 20 (start 2 1)
        $this->assertEquals('start 2 1', $result);
    }

    public function test_apply_filter_no_registered(): void
    {
        $result = $this->service->applyFilter('non.existent', 'original');
        $this->assertEquals('original', $result);
    }

    public function test_remove_action(): void
    {
        $count = 0;
        $callback = function () use (&$count): void {
            $count++;
        };

        $this->service->addAction('test.remove', $callback);
        $this->service->doAction('test.remove');
        $this->assertEquals(1, $count);

        $this->service->removeAction('test.remove', $callback);
        $this->service->doAction('test.remove');
        $this->assertEquals(1, $count);
    }

    public function test_remove_action_non_existent_hook(): void
    {
        $this->service->removeAction('non.existent', function (): void {});
        $this->assertFalse($this->service->hasAction('non.existent'));
    }

    public function test_remove_filter(): void
    {
        $callback = (fn ($val) => $val.'x');

        $this->service->addFilter('test.remove', $callback);
        $this->assertEquals('ax', $this->service->applyFilter('test.remove', 'a'));

        $this->service->removeFilter('test.remove', $callback);
        $this->assertEquals('a', $this->service->applyFilter('test.remove', 'a'));
    }

    public function test_remove_filter_non_existent_hook(): void
    {
        $this->service->removeFilter('non.existent', function (): void {});
        $this->assertFalse($this->service->hasFilter('non.existent'));
    }

    public function test_has_action_filter(): void
    {
        $this->service->addAction('act', function (): void {});
        $this->service->addFilter('filt', fn ($v) => $v);

        $this->assertTrue($this->service->hasAction('act'));
        $this->assertFalse($this->service->hasAction('non'));

        $this->assertTrue($this->service->hasFilter('filt'));
        $this->assertFalse($this->service->hasFilter('non'));
    }

    public function test_get_registered_hooks(): void
    {
        $this->service->addAction('act1', function (): void {});
        $this->service->addFilter('filt1', fn ($v) => $v);

        $hooks = $this->service->getRegisteredHooks();
        $this->assertContains('act1', $hooks['actions']);
        $this->assertContains('filt1', $hooks['filters']);
    }
}
