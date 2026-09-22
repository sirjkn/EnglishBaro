<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Development Region Fallback
    |--------------------------------------------------------------------------
    |
    | Local/private IP addresses (127.0.0.1, LAN ranges, etc.) cannot be
    | resolved to a real geographic region. In local development this lets
    | you simulate a region for testing regional pricing. Leave null in
    | production so private IPs simply fall back to base track pricing.
    |
    */

    'dev_fallback_region' => env('GEO_DEV_FALLBACK_REGION'),

];
