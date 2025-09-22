<?php

namespace Modules\BienIciScraper\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\BienIciScraper\Infrastructure\Models\Article;
use Symfony\Component\DomCrawler\Crawler;

class ScrapProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bienici:scrap-properties';

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
        $searchs = [
            [
                "uri" => "https://www.bienici.com/realEstateAds.json",
                "datas" => [
                    "filters" => [
                        "size" => 24,
                        "from" => 0,
                        "showAllModels" => false,
                        "filterType" => "buy",
                        "sortBy" => "publicationDate",
                        "sortOrder" => "desc",
                        "propertyType" => [
                            "house",
                        ],
                        "maxPrice" => 250000,
                        "page" => 1,
                        "onTheMarket" => ["true"],
                        "zoneIdsByTypes" => [
                            "zoneIds" => [
                                "-241355",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($searchs as $search) {
            $response = Http::get($search["uri"], $search["datas"]);
            $properties = $response->json();

            dump($properties["total"]);

            foreach ($properties["realEstateAds"] as $property) {
                $link = "https://www.bienici.com/annonce/" . $property["id"];

                $json = [
                    "roomsQuantity" => $property["roomsQuantity"],
                    "bedroomsQuantity" => $property["bedroomsQuantity"],
                    "bathroomsQuantity" => $property["bathroomsQuantity"] ?? null,
                    "floorQuantity" => $property["floorQuantity"] ?? null,
                    "surfaceArea" => $property["surfaceArea"],
                    "city" => $property["city"],
                    "postalCode" => $property["postalCode"],
                    "price" => is_array($property["price"]) ? $property["price"][0] : $property["price"],
                    "pricePerSquareMeter" => $property["pricePerSquareMeter"],
                ];

                $art = Article::firstOrCreate(
                    [
                        'link' => $link,
                    ],
                    [
                        'title' => $property["id"],
                        'image' => $property["photos"][0]["url_photo"],
                        'data' => $json
                    ]
                );
            }
        }


        // krsort($articles);

        // foreach ($articles as $article) {
        //     $art = Article::firstOrCreate(
        //         [
        //             'link' => $article['link'],
        //         ],
        //         [
        //             'title' => $article['title'],
        //             'image' => $article['image'],
        //             'data' => []
        //         ]
        //     );
        // }
    }
}
