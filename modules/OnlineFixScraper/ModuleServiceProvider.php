<?php

namespace Modules\OnlineFixScraper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\OnlineFixScraper\Console\Commands\ScrapHomePage;
use Modules\OnlineFixScraper\Infrastructure\Models\Article;
use Modules\OnlineFixScraper\Jobs\SendTelegramArticle;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // if (!config('modules.gaming_scraper')) return;

        $this->mergeConfigFrom(__DIR__ . '/config/onlinefix_scraper.php', 'onlinefix_scraper');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'ofs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScrapHomePage::class,
                // ajoute ici toutes tes commandes
            ]);
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(ScrapHomePage::class)
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