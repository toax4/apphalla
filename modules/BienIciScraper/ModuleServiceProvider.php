<?php

namespace Modules\BienIciScraper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\BienIciScraper\Console\Commands\ScrapProperties;
use Modules\BienIciScraper\Infrastructure\Models\Article;
use Modules\BienIciScraper\Jobs\SendTelegramArticle;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // if (!config('modules.gaming_scraper')) return;

        $this->mergeConfigFrom(__DIR__ . '/config/bienici_scraper.php', 'bienici_scraper');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'bis');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScrapProperties::class,
                // ajoute ici toutes tes commandes
            ]);
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(ScrapProperties::class)
                ->everyFifteenMinutes();

            $schedule->call(function () {
                $articles = Article::where("sended", 0)->orderBy("published_at", "asc")->orderby("id", "asc")->limit(3)->get();

                foreach ($articles as $article) {
                    if ($article) {
                        SendTelegramArticle::dispatch(article: $article);
                    }
                }
            })->everyMinute();
        });
    }
}