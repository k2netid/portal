<?php

declare(strict_types=1);

return [
    'subscription_domain_header' => (string) env('MEMBER_SUBSCRIPTION_DOMAIN_HEADER', 'X-Subscription-Domain'),
];
