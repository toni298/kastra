<?php

return [
    'company_id' => env('KIOSK_COMPANY_ID'),
    'device_token' => env('KIOSK_DEVICE_TOKEN'),
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('KIOSK_ALLOWED_IPS', ''))))),
    'latitude' => env('KIOSK_LATITUDE'),
    'longitude' => env('KIOSK_LONGITUDE'),
    'radius_meters' => (int) env('KIOSK_GEOFENCE_RADIUS_METERS', 250),
    'cooldown_seconds' => 180,
    'minimum_work_seconds' => 900,
];