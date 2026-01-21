<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ADMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to ADMS (Attendance Data Management System)
    | REST API for fetching attendance data from ZKTeco devices.
    |
    */

    'api_url' => env('ADMS_API_URL', 'https://adms.usbypkp.ac.id/api/v1'),
    
    'api_token' => env('ADMS_API_TOKEN'),

    'timeout' => env('ADMS_API_TIMEOUT', 30),
];
