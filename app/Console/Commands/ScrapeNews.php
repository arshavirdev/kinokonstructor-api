<?php

namespace App\Console\Commands;

use App\Service\News\MovieStartScraper;
use App\Service\StrapiService;
use Illuminate\Console\Command;
use App\Service\News\KinoNewsScraper;

class ScrapeNews extends Command
{
    protected $signature = 'news:scrape {--purge}';
    protected $description = 'Scrape news from external source';

    public function __construct(
        private KinoNewsScraper $kinoNewsScraper,
        private MovieStartScraper $movieStartScraper,
        private StrapiService $strapiService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('purge')) {
            $this->strapiService->deleteNewsByVendor(KinoNewsScraper::VENDOR);
            $this->strapiService->deleteNewsByVendor(MovieStartScraper::VENDOR);
            return $this->info('News successfully removed');
        }

        $this->info('Starting news scrapping');
        $this->processKinoNews();
        $this->processMovieStart();
        $this->info('News scrape finished.');
    }

    private function processKinoNews()
    {
        $news = $this->kinoNewsScraper->process();

        if (!count($news)) {
            $this->info('No news found for ' . KinoNewsScraper::VENDOR);
            return;
        }

        $this->info('Found ' . count($news) . ' ' . KinoNewsScraper::VENDOR);

        $this->strapiService->deleteNewsByVendor(KinoNewsScraper::VENDOR);

        foreach ($news as $newsData) {
            try {
                $this->strapiService->createNews($newsData);
            } catch (\Throwable $th) {
                $this->error('' . $th->getMessage());
            }
        }
    }

    private function processMovieStart()
    {
        $news = $this->movieStartScraper->process();

        if (!count($news)) {
            $this->info('No news found for ' . MovieStartScraper::VENDOR);
            return;
        }

        $this->info('Found ' . count($news) . ' ' . MovieStartScraper::VENDOR);

        $this->strapiService->deleteNewsByVendor(MovieStartScraper::VENDOR);

        foreach ($news as $newsData) {
            try {
                $this->strapiService->createNews($newsData);
            } catch (\Throwable $th) {
                $this->error('' . $th->getMessage());
            }
        }
    }
}
