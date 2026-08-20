<?php

namespace App\Console\Commands;

use App\Service\News\MovieStartScraper;
use App\Service\StrapiService;
use App\Service\TelegramService;
use Illuminate\Console\Command;
use App\Service\News\KinoNewsScraper;

class ScrapeNews extends Command
{
    protected $signature = 'news:scrape {--purge}';
    protected $description = 'Scrape news from external sources';

    public function __construct(
        private KinoNewsScraper $kinoNewsScraper,
        private MovieStartScraper $movieStartScraper,
        private StrapiService $strapiService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $sources = [
            $this->kinoNewsScraper,
            $this->movieStartScraper,
        ];

        if ($this->option('purge')) {
            foreach ($sources as $source) {
                $this->strapiService->deleteNewsByVendor($source::VENDOR);
            }
            $this->info('News successfully removed.');
            return;
        }

        $this->info('Starting news scraping...');

        foreach ($sources as $source) {
            $this->processSource($source);
        }

        $this->info('News scraping finished.');
    }

    private function processSource(object $scraper): void
    {
        $vendor = $scraper::VENDOR;

        try {
            $news = $scraper->process();
        } catch (\Throwable $e) {
            $this->error("Failed to process $vendor: " . $e->getMessage());
            $this->notifyError("Failed to process news:scrap $vendor", $e);
            return;
        }

        if (empty($news)) {
            $this->info("No news found for $vendor.");
            return;
        }

        $this->info("Found " . count($news) . " items for $vendor.");

        try {
            $this->strapiService->deleteNewsByVendor($vendor);
            $this->info("Deleting $vendor news.");
        } catch (\Throwable $e) {
            $this->error("Failed to delete existing news for $vendor: " . $e->getMessage());
            $this->notifyError("Failed to delete existing news for $vendor", $e);
            return;
        }

        foreach ($news as $newsData) {
            try {
                $this->strapiService->createNews($newsData);
            } catch (\Throwable $e) {
                $this->error("Failed to create news item for $vendor: " . $e->getMessage());
                $this->notifyError("Failed to create news item for $vendor", $e);
            }
        }
    }

    private function notifyError(string $context, \Throwable $e): void
    {
        $message = TelegramService::formatException($e) . "\nContext: `$context`";
        TelegramService::sendMessage($message);
    }
}
