<?php
namespace App\Service\News;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class KinoNewsScraper
{
    const VENDOR = 'kinonews';
    const BASE_URL = 'https://www.kinonews.ru';
    const NEWS_LIMIT = 5;
    const REQUEST_TIMEOUT = 10;

    public function process()
    {
        $news = [];

        $client = HttpClient::create(['timeout' => self::REQUEST_TIMEOUT]);
        try {
            $response = $client->request('GET', self::BASE_URL . '/news');
            $html = $response->getContent();
        } catch (TimeoutExceptionInterface $e) {
            throw new \RuntimeException("Timed out fetching news articles after " . self::REQUEST_TIMEOUT . "s: " . $e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch news articles: " . $e->getMessage());
        }

        $crawler = new Crawler($html);

        $newsLinks = $crawler->filter('.dopblock')
            ->slice(0, self::NEWS_LIMIT)
            ->each(function (Crawler $node) {
                $linkUrl = $node->filter('a')->attr('href');
                return $linkUrl;
            });

        if (!count($newsLinks)) {
            return [];
        }

        foreach ($newsLinks as $link) {
            $news[] = $this->scrapeSingleNews($link);
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
        return $this->parseSingleNews($crawler, $link);
    }

    private function parseSingleNews(Crawler $crawler, string $link): array
    {
        $newsBlock = $crawler->filter('div#current_news');

        $date = $crawler->filter('meta[property="article:published_time"]')->attr('content');
        $imgUrl = $crawler->filter('meta[property="og:image"]')->attr('content');
        $brief = $crawler->filter('meta[name="description"]')->attr('content', '');

        $title = $newsBlock->filter('h1.new')->count() ? $newsBlock->filter('h1.new')->text() : '';
        $content = $newsBlock->filter('div.textart')->count() ? $newsBlock->filter('div.textart')->html() : '';

        $brief = mb_strlen($brief, 'UTF-8') > 100 ? mb_substr($brief, 0, 80, 'UTF-8') . "..." : $brief;
        $slug = self::VENDOR . '-' . (Str::slug($link) ?: time());

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
