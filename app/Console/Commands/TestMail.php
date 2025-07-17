<?php

namespace App\Console\Commands;

use App\Service\News\MovieStartScraper;
use App\Service\StrapiService;
use Illuminate\Console\Command;
use App\Service\News\KinoNewsScraper;
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
            $message->to(config('mail.from.address'));
        });
        $this->info('Mail successfully sent.');
    }
}
