<?php
namespace App\Service\News;

use Illuminate\Support\Str;
use Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MovieStartScraper
{
    const VENDOR = 'moviestart';
    const BASE_URL = 'https://moviestart.ru';
    const NEWS_LIMIT = 5;
    const REQUEST_TIMEOUT = 10;

    public function process()
    {
        $news = [];

        $client = HttpClient::create(['timeout' => self::REQUEST_TIMEOUT]);
        try {
            $response = $client->request('GET', self::BASE_URL . '/category/news/');
            $html = $response->getContent();
        } catch (TimeoutExceptionInterface $e) {
            throw new \RuntimeException("Timed out fetching news articles after " . self::REQUEST_TIMEOUT . "s: " . $e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch news articles: " . $e->getMessage());
        }

        $crawler = new Crawler($html);

        $newsLinks = $crawler->filter('div.post-item')
            ->slice(0, self::NEWS_LIMIT)
            ->each(function (Crawler $node) {
                $linkUrl = $node->filter('a')->attr('href');
                return $linkUrl;
            });

        if (!count($newsLinks)) {
            return [];
        }

        foreach ($newsLinks as $link) {
            try {
                $news[] = $this->scrapeSingleNews($link);
            } catch (\Throwable $th) {
                Log::error('' . $th->getMessage());
            }
        }

        return $news;
    }

    private function scrapeSingleNews(string $link)
    {
        $client = HttpClient::create(['timeout' => self::REQUEST_TIMEOUT]);

        try {
            $response = $client->request('GET', self::BASE_URL . $link);
            $html = $response->getContent();
        } catch (TimeoutExceptionInterface $e) {
            throw new \RuntimeException("Timed out fetching single news article after " . self::REQUEST_TIMEOUT . "s: " . $e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch single news article: " . $e->getMessage());
        }

        $crawler = new Crawler($html);
        try {
            return $this->parseSingleNews($crawler, $link);
        } catch (\Throwable $th) {
            echo "" . $th->getMessage();
        }
    }

    private function parseSingleNews(Crawler $crawler, string $link): array
    {
        $parsedLink = explode('/', $link);

        $date = $crawler->filter('meta[property="article:published_time"]')->attr('content', '');
        $galleryLink = $crawler->filter('div.images .image-wrap a');
        $galleryImg = $crawler->filter('div.images .image-wrap img');
        if ($galleryLink->count()) {
            $imgUrl = self::BASE_URL . $galleryLink->first()->attr('href');
        } elseif ($galleryImg->count()) {
            $imgSrc = $galleryImg->first()->attr('data-src') ?: $galleryImg->first()->attr('src');
            $imgUrl = self::BASE_URL . $imgSrc;
        } else {
            $imgUrl = $crawler->filter('meta[property="og:image"]')->attr('content', '');
        }
        $brief = $crawler->filter('meta[name="description"]')->attr('content', '');

        $title = $crawler->filter('h1.title')->text('');
        $content = $crawler->filter('div.container div.wysiwyg')->count() ? $crawler->filter('div.container div.wysiwyg')->html() : '';

        $brief = mb_strlen($brief, 'UTF-8') > 100 ? mb_substr($brief, 0, 80, 'UTF-8') . "..." : $brief;
        $slug = self::VENDOR . '-' . (Str::slug($parsedLink[4] ?? '') ?: time());

        return [
            'vendor' => self::VENDOR,
            'slug' => $slug,
            'brief' => $brief,
            'title' => $title,
            'imgUrl' => $imgUrl,
            'content' => $content,
            'date' => $date
        ];
    }
}
