<?php

namespace App\Console\Commands;

use App\Service\Media\MediaService;
use Illuminate\Console\Command;
use App\Service\Events\CultureScraper;

class ScrapeEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:scrape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape title, image, description, date from events';

    public function __construct(
        private CultureScraper $cultureScraper
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info('Starting events scraping...');

        $this->processSource($this->cultureScraper);

        $this->info('Events scraping finished.');
    }

    public function processSource($scraper)
    {
        try {
            $mediaService = new MediaService();
            $scraper->process($mediaService);
        } catch (\Throwable $e) {
            $this->error("Failed to process " . $e->getMessage());
            return;
        }
    }
}
