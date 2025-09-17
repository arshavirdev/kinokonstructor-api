<?php

namespace App\Service\Resources;

use App\Models\Resource;
use App\Service\Media\MediaService;
use App\DTOs\MediaSyncDataDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Models\User;

class CinemapScraper
{
    const BASE_URL = 'https://cinemap.ru/';

    const DEVICES_LIMIT = 3;

    public function process(MediaService $mediaService)
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('GET', self::BASE_URL . 'devices/rent/');
            $html = $response->getContent();
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch news articles: " . $e->getMessage());
        }

        $crawler = new Crawler($html);

        $user = User::where('role', '=', 'admin')->oldest()->first();

        $devices = $crawler->filter('ul.devices li.device')->slice(0, self::DEVICES_LIMIT);

        foreach ($devices as $deviceNode) {
            $deviceCrawler = new Crawler($deviceNode);

            $title = $deviceCrawler->filter('h1.title a')->text();
            $detailUrl = $deviceCrawler->filter('h1.title a')->attr('href');

            $detailHtml = $client->request('GET', $detailUrl)->getContent();
            $detailCrawler = new Crawler($detailHtml);

            $description = $detailCrawler->filter('div._expandable-inner')->count()
                ? $detailCrawler->filter('div._expandable-inner')->text()
                : '';

            $data = [
                'title' => $title,
                'short_description' => '',
                'description' => $description,
                'category' => ["Трюковые Съемки"],
                'owner_id' => $user->profile->id,
                'region_id' => 1,
                'parameters' => [],
                'company' => [],
                'is_archived' => false,
            ];

            $resource = Resource::create($data);

            $imageUrls = $detailCrawler->filter('.gallery .slide_one')->count() > 0 ?
                $detailCrawler->filter('.gallery .slide_one a')
                    ->each(fn (Crawler $node) => $node->attr('href')) :
                $detailCrawler->filter('.gallery .swiper-slide a')
                    ->each(fn (Crawler $node) => $node->attr('href'));


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

        return "Scraped " . count($devices) . " resources with images.";

    }

    private function makeAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return rtrim(self::BASE_URL, '/') . '/' . ltrim($url, '/');
    }

}