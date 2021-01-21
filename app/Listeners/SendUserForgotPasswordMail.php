<?php

namespace App\Listeners;

use App\Events\UserForgotPassword;
use App\Mail\UserForgotPasswordMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserForgotPasswordMail implements ShouldQueue
{
    public $connection = 'database';
    public $delay = '10';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserForgotPassword  $event
     * @return void
     */
    public function handle(UserForgotPassword $event)
    {
        Mail::to($event->user->email)->send(
            new UserForgotPasswordMail($event->url, $event->user)
        );
    }
}
