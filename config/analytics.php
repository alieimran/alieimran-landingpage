<?php

return [
    // Only explicit reverse-proxy IPs/CIDRs, never trust arbitrary forwarded headers.
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', env('ANALYTICS_TRUSTED_PROXIES', ''))), fn ($ip) => $ip !== '' && $ip !== '*')),
];
