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

class CinemapScraper
{
    const BASE_URL = 'https://cinemap.ru/';

    public function process(MediaService $mediaService)
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('GET', self::BASE_URL . 'devices/skejter-professionalnyj');
            $html = $response->getContent();
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch news articles: " . $e->getMessage());
        }

        $crawler = new Crawler($html);

        $resourceTitle = $crawler->filter('h1.entry-title')->text();
        $resourceDescription = $crawler->filter('div._expandable-inner')->text();

        $data = [
            'title' => $resourceTitle,
            'short_description' => 'test',
            'description' => $resourceDescription,
            'category' => [],
            'owner_id' => 1,
            'region_id' => 1,
            'parameters' => [],
            'company' => [],
            'is_archived' => false
        ];
        
        $resource = Resource::create($data);
        
        $imageUrls = $crawler->filter('.gallery .swiper-slide a')->each(fn (Crawler $node) => $node->attr('href'));

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

        $imagesMediaDto = new MediaSyncDataDTO($uploadedFiles, []);
        $mediaService->syncMediaCollection($resource, $imagesMediaDto, Resource::IMAGES_FILES);

        return "Resource created with " . count($imageUrls) . " images.";

    }

    private function makeAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return rtrim(self::BASE_URL, '/') . '/' . ltrim($url, '/');
    }

}