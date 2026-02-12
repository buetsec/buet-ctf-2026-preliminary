<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flare API Key (not used in CTF)
    |--------------------------------------------------------------------------
    */
    'key' => env('FLARE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Censor Request Body Fields (CTF: empty to show all)
    |--------------------------------------------------------------------------
    */
    'censor_request_body_fields' => [],

    /*
    |--------------------------------------------------------------------------
    | Reporting - disable for CTF
    |--------------------------------------------------------------------------
    */
    'reporting' => [
        'anonymize_ips' => false,
        'collect_git_information' => false,
        'report_logs' => false,
        'report_queries' => false,
        'maximum_number_of_collected_logs' => 0,
        'maximum_number_of_collected_queries' => 0,
    ],
];
