<?php

return [
    /**
     * string - path to clamav.sock file
     */
    'clamd_sock' => env('CLAMD_SOCK', '/var/run/clamav/clamd.sock'),

    /**
     * int - default 20000
     */
    'clamd_sock_len' => env('CLAMD_SOCK_LEN', 20000),

    /**
     * string|null
     */
    'clamd_ip' => env('CLAMD_IP', null),

    /**
     * int - default 3310
     */
    'clamd_port' => env('CLAMD_PORT', 3310),

    /**
     * bool - should file upload be processed in a queue. Default true
     */
    'log_scan_data' => env('FILE_UPLOAD_LOG_SCAN_DATA', false),

    /**
     * string - input name. Default 'file'
     */
    'input' => env('FILE_UPLOAD_INPUT', 'file'),

    /**
     * string - Default 'public'
     */
    'path' => env('FILE_UPLOAD_PATH', 'public'),

    /**
     * string - Default 'local'
     */
    'disk' => env('FILE_UPLOAD_DISK', 'local'),

    /**
     * bool - should file upload be processed in a queue. Default true
     */
    'hashed' => env('HASHED', false),

    /**
     * bool - should file upload be processed in a queue. Default true
     */
    'visible' => env('VISIBLE', true),

];
