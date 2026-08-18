<?php

declare(strict_types=1);

namespace Modules\Core\Security\Services;

use Illuminate\Support\Arr;
use Modules\Core\System\Models\AbacPolicy;
use Modules\Core\System\Models\User;

class AbacEvaluator
{
    /**
     * Evaluate if a user meets the ABAC policies for a given action and resource.
     * Returns true if there are no policies restricting it, or if all applicable policies are met.
     * Returns false if any applicable policy condition fails.
     */
    public function evaluate(User $user, string $action, ?string $targetResource = null): bool
    {
        // Find policies matching this resource and action
        $query = AbacPolicy::where('is_active', true);

        if ($targetResource) {
            $query->where(function ($q) use ($targetResource) {
                $q->where('target_resource', $targetResource)
                    ->orWhereNull('target_resource')
                    ->orWhere('target_resource', '*');
            });
        }

        $query->where(function ($q) use ($action) {
            $q->where('action', $action)
                ->orWhereNull('action')
                ->orWhere('action', '*');
        });

        $policies = $query->get();

        if ($policies->isEmpty()) {
            return true; // No specific ABAC restrictions for this action/resource
        }

        // Extract context for evaluation
        $context = [
            'user' => $user->toArray(),
        ];

        foreach ($policies as $policy) {
            if (! $this->evaluateConditions($policy->conditions ?? [], $context)) {
                return false; // Failed a required policy condition
            }
        }

        return true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $conditions
     * @param  array<string, mixed>  $context
     */
    /**
     * @param  array<int, array<string, mixed>>  $conditions
     * @param  array<string, mixed>  $context
     */
    protected function evaluateConditions(array $conditions, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $attributePath = $condition['attribute'] ?? null;
            $operator = $condition['operator'] ?? '==';
            $expectedValue = $condition['value'] ?? null;

            if (! is_string($attributePath) || $attributePath === '') {
                continue;
            }

            $actualValue = Arr::get($context, $attributePath);

            // For custom mappings (like kyc_level levels)
            $actualStr = is_string($actualValue) ? $actualValue : (is_scalar($actualValue) ? (string) $actualValue : '');
            $expectedStr = is_string($expectedValue) ? $expectedValue : (is_scalar($expectedValue) ? (string) $expectedValue : '');
            if (str_starts_with($actualStr, 'level_') && str_starts_with($expectedStr, 'level_')) {
                $actualValue = (int) str_replace('level_', '', $actualStr);
                $expectedValue = (int) str_replace('level_', '', $expectedStr);
            }

            $passed = match ($operator) {
                '==' => $actualValue == $expectedValue,
                '===' => $actualValue === $expectedValue,
                '!=' => $actualValue != $expectedValue,
                '>' => $actualValue > $expectedValue,
                '>=' => $actualValue >= $expectedValue,
                '<' => $actualValue < $expectedValue,
                '<=' => $actualValue <= $expectedValue,
                'in' => is_array($expectedValue) && in_array($actualValue, $expectedValue, true),
                'not_in' => is_array($expectedValue) && ! in_array($actualValue, $expectedValue, true),
                default => false,
            };

            if (! $passed) {
                return false;
            }
        }

        return true;
    }
}
