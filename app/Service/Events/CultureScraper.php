<?php

namespace App\Service\Events;

use App\Models\Resource;
use App\Service\Media\MediaService;
use App\DTOs\MediaSyncDataDTO;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Models\User;

class CultureScraper
{
    const BASE_URL = 'https://www.culture.ru/';
    const EVENTS_LIMIT = 5;

    public function process(MediaService $mediaService)
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('GET', self::BASE_URL . 'afisha/russia');
            $html = $response->getContent();
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

            $location = $node->filter('.styles_BaseCard__Location__N5Zpj')->count()
                ? trim($node->filter('.styles_BaseCard__Location__N5Zpj')->text())
                : null;

            $date = $node->filter('.styles_BaseCard__DateText__KKhXl')->count()
                ? trim($node->filter('.styles_BaseCard__DateText__KKhXl')->text())
                : (
                    $node->filter('.styles_BaseCard__TextLabel__eWmWr')->count()
                        ? trim($node->filter('.styles_BaseCard__TextLabel__eWmWr')->text())
                        : null
                );

            $price = $node->filter('.styles_BaseCard__Price__OQHI5')->count()
                ? trim($node->filter('.styles_BaseCard__Price__OQHI5')->text())
                : null;

            // Scrape detail page
            $detailHtml = $client->request('GET', $link)->getContent();
            $detailCrawler = new Crawler($detailHtml);

            $detailTitle = $detailCrawler->filter('h1.styles_ArticlePoster__Title__mhEwE')->count()
                ? trim($detailCrawler->filter('h1.styles_ArticlePoster__Title__mhEwE')->text())
                : $title;

            $description = $detailCrawler->filter('.styles_DescriptionImage_Text__wY0LK')->count()
                ? trim($detailCrawler->filter('.styles_DescriptionImage_Text__wY0LK')->text())
                : '';

            $detailDate = $detailCrawler->filter('div.styles_list__wSs_g .styles_item___gfFA')->count()
                ? trim($detailCrawler->filter('div.styles_list__wSs_g .styles_item___gfFA')->first()->text())
                : $date;

            // Create resource
            $data = [
                'title' => $detailTitle,
                'short_description' => '',
                'description' => $description,
                'category' => ["Культура"],
                'format' => '',
                'parameters' => [],
                'location' => $location,
                'owner_id' => $user->profile->id,
                'date' => '2025-09-17',
                'company' => [],
            ];

            $resource = Event::create($data);

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
                $mediaService->syncMediaCollection($resource, $imagesMediaDto, Resource::IMAGES_FILES);
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