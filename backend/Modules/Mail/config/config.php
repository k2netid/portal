<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Queue outbound mail (optional)
    |--------------------------------------------------------------------------
    |
    | When true, OutboundMailPortInterface::send() defaults to queueing via
    | SendOutboundMailJob. Pass queue: false to force synchronous send.
    | The webmail HTTP compose path stays synchronous regardless of this flag.
    |
    */
    'queue_outbound' => (bool) env('MAIL_QUEUE_OUTBOUND', false),
];
