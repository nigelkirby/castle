<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'google' => [
        'client_id' => env('AUTH_GOOGLE_CLIENT_ID'),
        'client_secret' => env('AUTH_GOOGLE_CLIENT_SECRET'),
        'redirect' => 'http://castle.dev/login/google/callback',
    ],

];
