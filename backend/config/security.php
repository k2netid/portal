<?php

declare(strict_types=1);

/**
 * Security Configuration
 *
 * This file contains configuration for security headers, CSP policies,
 * and other security-related settings.
 *
 * [SECURITY FIX L-01] External domain allowlists are now managed here
 * instead of being hardcoded in middleware. This allows per-deployment
 * customization without modifying source code.
 */
return [

    // =========================================================================
    // Content Security Policy (CSP) Headers
    // =========================================================================

    'headers' => [
        /**
         * Custom CSP Policy
         * If set to a non-empty string, this policy overrides the auto-generated CSP.
         * Must include 'self' in script-src and connect-src to not break the SPA.
         * Leave null to use the auto-generated policy.
         *
         * @var string|null
         */
        'csp' => env('CSP_POLICY', null),

        /**
         * Report-Only Mode
         * When true, CSP violations are reported but not enforced.
         * Recommended to set to false in production after testing.
         *
         * @var bool
         */
        'csp_report_only' => (bool) env('CSP_REPORT_ONLY', false),
    ],

    // =========================================================================
    // CSP Extra connect-src Domains
    // =========================================================================

    /**
     * Extra domains to allow in the CSP connect-src directive.
     * Use this instead of hardcoding domains in SecurityHeaders middleware.
     *
     * Example .env:
     *   CSP_CONNECT_EXTRA="https://tenant1.example.com,https://tenant2.example.com"
     *
     * [SECURITY FIX L-01] Replaces the hardcoded smkn1cijulang.sch.id entries.
     */
    'csp_connect_extra' => array_filter(
        array_map(
            'trim',
            explode(',', (string) env('CSP_CONNECT_EXTRA', ''))
        )
    ),

];
