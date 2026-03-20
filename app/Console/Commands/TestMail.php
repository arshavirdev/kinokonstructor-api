<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;

class TestMail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Test Mail Service Connection';

    public function __construct(
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        Mail::raw('Hello world', function ($message) {
            $message->to('arshavir.dev@gmail.com');
        });
        $this->info('Mail successfully sent.');
    }
}
