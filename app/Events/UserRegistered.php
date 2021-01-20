<?php

namespace App\Events;

class UserRegistered extends Event
{

    public $url;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param $url
     * @param $user
     */
    public function __construct($url, $user)
    {
        $this->url = $url;
        $this->user = $user;
    }
}
