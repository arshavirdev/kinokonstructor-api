<?php

namespace App\Console\Commands;

use App\Service\Media\MediaService;
use Illuminate\Console\Command;
use App\Service\Resources\CinemapScraper;

class ScrapeResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources:scrape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape image, title, link,from resources';

    public function __construct(
        private CinemapScraper $cinemapScraper
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

        $this->info('Starting cinemap scraping...');

        $this->processSource($this->cinemapScraper);

        $this->info('Resources scraping finished.');
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
