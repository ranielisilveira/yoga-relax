<?php

return [

    /*
    |--------------------------------------------------------------------------
    | System Messages
    |--------------------------------------------------------------------------
    */

    'register' => [
        'error' => 'There was an error trying to register, please try again later.',
        'success' => 'Your Account was created successfully.',
        'confirm' => 'Your account has been successfully verified.',
        'confirm_error' => 'Your user has been previously activated.',
    ],
    'auth' => [
        'invalid_data' => 'Invalid data',
        'invalid_data_try_again' => 'Invalid data. Try again',
        'unverified_user' => 'Email not yet verified, you must confirm your account to login.',
        'passport_error' => 'Problems with the authentication server (passport). Try again later.',
        'logout_success' => 'You have successfully logout'
    ],
    'user_forgot_password' => [
        'success' => 'A recovery email has been sent.',
    ],
    'user_reset_password' => [
        'success' => 'Your password has been created.',
        'mail_token_invalid' => 'The mail token submitted is invalid.'
    ]

];
