<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\UserConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserConfirmationMail implements ShouldQueue
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
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)->send(
            new UserConfirmationMail($event->url, $event->user)
        );
    }
}
