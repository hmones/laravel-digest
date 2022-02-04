<?php

namespace Hmones\LaravelDigest\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DefaultMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): Mailable
    {
        return $this->view('digest')->to('email@test.com');
    }
}