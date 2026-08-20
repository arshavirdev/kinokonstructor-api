<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

final readonly class StrapiService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.strapi.url');
        $this->token = config('services.strapi.token');
    }

    /**
     * Create Strapi Resource
     * @param string $collection
     * @param array $data
     * @return array
     */
    public function createNews(array $data): array
    {
        $multipart = [
            [
                'name' => 'data',
                'contents' => json_encode($data)
            ]
        ];

        // Attach image file if present
        if ($data['imgUrl'] ?? '') {
            $multipart[] = [
                'name' => "files.image",
                'contents' => fopen($data['imgUrl'], 'r')
            ];
        }

        $response = Http::withToken($this->token)
            ->asMultipart()
            ->post("{$this->baseUrl}/news", $multipart);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    /**
     * Delete Resources by filters
     * @param array $filters
     * @return void
     */
    public function deleteNewsByVendor(string $vendor): void
    {
        do {
            $query = http_build_query([
                'filters' => [
                    'slug' => [
                        '$startsWith' => "$vendor-"
                    ],
                ],
                'fields[0]' => 'id',
                'populate' => '*',
                'pagination[pageSize]' => 100,
            ]);

            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/news?$query");

            $data = $response->json()['data'] ?? [];

            foreach ($data as $item) {
                $id = $item['id'];
                $mediaId = $item['image']['id'] ?? '';

                $deleteResponse = Http::withToken($this->token)
                    ->delete("{$this->baseUrl}/news/$id");

                if (!$deleteResponse->successful()) {
                    throw new \Exception($deleteResponse->body());
                }

                if ($mediaId) {
                    Http::withToken($this->token)
                        ->delete("{$this->baseUrl}/upload/files/$mediaId");
                }
            }
        } while (count($data) > 0);
    }
}
