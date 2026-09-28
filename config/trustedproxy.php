<?php

return [
    // Comma-separated list of proxy IPs/CIDRs to trust X-Forwarded-* headers from,
    // or '*' to trust the immediate connecting peer. Leave empty to trust nothing
    // (default). Read by Illuminate\Http\Middleware\TrustProxies.
    'proxies' => env('TRUSTED_PROXIES'),
];
