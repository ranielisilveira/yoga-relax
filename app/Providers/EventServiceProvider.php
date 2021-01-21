<?php

namespace App\Providers;

use App\Events\UserForgotPassword;
use App\Events\UserRegistered;
use App\Listeners\SendUserConfirmationMail;
use App\Listeners\SendUserForgotPasswordMail;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserRegistered::class => [
            SendUserConfirmationMail::class
        ],
        UserForgotPassword::class => [
            SendUserForgotPasswordMail::class
        ]
    ];
}
