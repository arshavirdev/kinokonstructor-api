<?php

namespace App\Service\Events;

use App\Service\Media\MediaService;
use App\DTOs\MediaSyncDataDTO;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Models\User;
use Carbon\Carbon;

class CultureScraper
{
    const BASE_URL = 'https://www.culture.ru/';
    const EVENTS_LIMIT = 5;
    const REQUEST_TIMEOUT = 10;

    public function process(MediaService $mediaService)
    {
        $client = HttpClient::create(['timeout' => self::REQUEST_TIMEOUT]);

        try {
            $response = $client->request('GET', self::BASE_URL . 'afisha/russia/kino');
            $html = $response->getContent();
        } catch (TimeoutExceptionInterface $e) {
            throw new \RuntimeException("Timed out fetching culture.ru events after " . self::REQUEST_TIMEOUT . "s: " . $e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch culture.ru events: " . $e->getMessage());
        }

        $crawler = new Crawler($html);
        $user = User::where('role', '=', 'admin')->oldest()->first();

        // Select event cards
        $eventNodes = $crawler->filter('.main_col__f5Jmt a.styles_BaseCard__HuNmK')->slice(0, self::EVENTS_LIMIT);

        foreach ($eventNodes as $eventNode) {
            $node = new Crawler($eventNode);

            $link = $node->attr('href');
            if ($link && strpos($link, 'http') === false) {
                $link = self::BASE_URL . ltrim($link, '/');
            }

            $title = $node->filter('.styles_BaseCard__Title__NkcLR')->count()
                ? trim($node->filter('.styles_BaseCard__Title__NkcLR')->text())
                : null;

            // Scrape detail page
            $detailHtml = $client->request('GET', $link)->getContent();
            $detailCrawler = new Crawler($detailHtml);


            $metaData = $detailCrawler->filter('script[type="application/ld+json"]')->count() > 1
                ? $detailCrawler->filter('script[type="application/ld+json"]')->last()->text()
                : '';
            $metaData = json_decode($metaData ?? "", true);

            $detailTitle = $detailCrawler->filter('h1.styles_ArticlePoster__Title__mhEwE')->count()
                ? trim($detailCrawler->filter('h1.styles_ArticlePoster__Title__mhEwE')->text())
                : $title;

            $description = $detailCrawler->filter('.styles_body__WEo9w')->count()
                ? trim($detailCrawler->filter('.styles_body__WEo9w')->text())
                : '';

            // Create resource
            $data = [
                'title' => $detailTitle,
                'short_description' => '',
                'description' => $description,
                'category' => ["Кинопоказ"],
                'format' => "Offline",
                'parameters' => [],
                'location' => 'Москва',
                'owner_id' => $user->profile->id,
                'date' => $metaData['startDate'] ?? Carbon::now(),
                'company' => [],
                'external_link' => $link,
                'region_ids' => [1]
            ];

            $event = Event::firstOrCreate(
                ['title' => $detailTitle],
                $data
            );

            if ($event->wasRecentlyCreated) {
                // Get images (main poster from detail page)
                $imageUrls = $detailCrawler->filter('span[data-cy="thumbnail"] img')->count()
                    ? [$detailCrawler->filter('span[data-cy="thumbnail"] img')->attr('src')]
                    : [];
    
                $uploadedFiles = [];
    
                foreach ($imageUrls as $url) {
                    $absoluteUrl = $this->makeAbsoluteUrl($url);
                    $imageContent = $client->request('GET', $absoluteUrl)->getContent();
    
                    $filename = basename(parse_url($absoluteUrl, PHP_URL_PATH));
                    $tmpPath = Storage::disk('local')->path("tmp/{$filename}");
                    Storage::disk('local')->put("tmp/{$filename}", $imageContent);
    
                    $uploadedFiles[] = new UploadedFile(
                        $tmpPath,
                        $filename,
                        mime_content_type($tmpPath),
                        null,
                        true
                    );
                }
    
                if ($uploadedFiles) {
                    $imagesMediaDto = new MediaSyncDataDTO($uploadedFiles, []);
                    $mediaService->syncMediaCollection($event, $imagesMediaDto, Event::IMAGES_FILES);
                }
            }
        }

        return "Scraped " . count($eventNodes) . " culture.ru events with images.";
    }

    private function makeAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return rtrim(self::BASE_URL, '/') . '/' . ltrim($url, '/');
    }
}