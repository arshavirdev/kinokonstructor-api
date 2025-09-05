<?php

namespace App\Service\Resources;

use Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CinemapScraper
{
    const BASE_URL = 'https://cinemap.ru/';

    public function process()
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('GET', self::BASE_URL . 'devices/skejter-professionalnyj');
            $html = $response->getContent();
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch news articles: " . $e->getMessage());
        }

        $crawler = new Crawler($html);

        $resourceTitle = $crawler->filter('h1.entry-title');
        dd($resourceTitle);

    }

}