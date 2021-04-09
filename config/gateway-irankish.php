<?php

return [

    /**
     *  driver class namespace
     */
    'driver' => Omalizadeh\MultiPayment\Drivers\Irankish::class,

    /**
     *  soap client options
     */
    'request_headers' => [
        'Accept' => 'application/json'
    ],

    /**
     *  gateway configurations
     */
    'default' => [
        'merchant_id' => '',
        'callback_url' => 'http://yoursite.com/path/to',
        'description' => 'payment using irankish',
    ],
    'other' => [
        'merchant_id' => '',
        'callback_url' => 'http://yoursite.com/path/to',
        'description' => 'payment using irankish',
    ]
];
