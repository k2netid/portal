<?php

namespace Modules\Core\System\Services;

class DashboardRegistry
{
    /**
     * @var array<string, callable>
     */
    protected array $statsProviders = [];

    /**
     * @var array<string, callable>
     */
    protected array $chartProviders = [];

    /**
     * Register a stats provider.
     */
    public function registerStatsProvider(string $key, callable $provider): void
    {
        $this->statsProviders[$key] = $provider;
    }

    /**
     * Register a chart provider.
     */
    public function registerChartProvider(string $key, callable $provider): void
    {
        $this->chartProviders[$key] = $provider;
    }

    /**
     * Get all registered stats.
     *
     * @return array<string, mixed>
     */
    public function getAllStats(): array
    {
        $stats = [];
        foreach ($this->statsProviders as $key => $provider) {
            $stats[$key] = $provider();
        }

        return $stats;
    }

    /**
     * Get all registered charts.
     *
     * @return array<string, mixed>
     */
    public function getAllCharts(): array
    {
        $charts = [];
        foreach ($this->chartProviders as $key => $provider) {
            $charts[$key] = $provider();
        }

        return $charts;
    }
}
