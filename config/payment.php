<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bank Transfer Details
    |--------------------------------------------------------------------------
    | Displayed to retailers on the payment proof upload prompt so they know
    | exactly where to transfer. Set these in your .env file.
    */

    'bank_name'       => env('PAYMENT_BANK_NAME', 'Meezan Bank'),
    'account_title'   => env('PAYMENT_ACCOUNT_TITLE', 'OZ Tech Pvt Ltd'),
    'account_number'  => env('PAYMENT_ACCOUNT_NUMBER', ''),
    'iban'            => env('PAYMENT_IBAN', ''),
    'branch_code'     => env('PAYMENT_BRANCH_CODE', ''),
    'branch_name'     => env('PAYMENT_BRANCH_NAME', ''),

];
