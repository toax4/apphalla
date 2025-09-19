<?php

namespace Modules\OnlineFixScraper\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\OnlineFixScraper\Infrastructure\Models\Article;
use Symfony\Component\DomCrawler\Crawler;

class ScrapHomePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onlinefix:scrap-homepage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uri = "https://online-fix.me";

        $response = (new Client())->get($uri);
        $html = $response->getBody()->getContents();

        $crawler = new Crawler($html);

        $articles = $crawler->filter("article.news")->each(function (Crawler $node) {
            return [
                'title' => trim(trim($node->filter(".article-content h2.title")->first()->text(), "по сети")),
                'link' => $node->filter("a.big-link")->first()->attr('href'),
                'image' => $node->filter(".image img")->first()->attr('data-src'),
            ];
        });

        krsort($articles);

        foreach ($articles as $article) {
            $art = Article::firstOrCreate(
                [
                    'link' => $article['link'],
                ],
                [
                    'title' => $article['title'],
                    'image' => $article['image'],
                    'data' => []
                ]
            );
        }
    }
}