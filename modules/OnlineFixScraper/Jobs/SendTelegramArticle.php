<?php

namespace Modules\OnlineFixScraper\Jobs;

use App\Services\TelegramService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Modules\OnlineFixScraper\Infrastructure\Models\Article;

class SendTelegramArticle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Article $article;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Exécution du job.
     */
    public function handle()
    {
        try {
            $telegramToken = config('onlinefix_scraper.telegram.bot_token');
            $chatId = $this->article->data["channel"] ?? config('services.telegram.chat_id');

            $localPath = $this->downloadImage($this->article->image);
            // $json = json_decode($this->article->data, true);

            // 2️⃣ Envoi du message avec l'image
            $telegramService = new TelegramService(botToken: $telegramToken, chatId: $chatId);
            $result = $telegramService->sendWithImage(View::make("ofs::games", ["article" => $this->article]), $localPath);

            if ($result->status() != 200) {
                Log::error("Erreur pour l'article : " . $this->article->id);
                Log::error($result->json());
                Log::error($result);
            }

            // 3️⃣ Suppression du fichier temporaire
            Storage::delete(basename($localPath));

            $this->article->sended = true;
            $this->article->save();
            // Optionnel : log du résultat
            // Log::info('Telegram response', ['result' => $result]);
        } catch (Exception $e) {
            Log::error("Erreur pour l'article : " . $this->article->id . " " . $e->getMessage());
        }
    }

    private function downloadImage(string $url): string
    {
        $fileName = 'temp/' . basename($url);
        // dump($fileName);

        if (Storage::exists($fileName)) {
            Storage::delete($fileName);
        }

        // 1️⃣ Téléchargement de l'image en local
        $imageContent = file_get_contents($url, false, stream_context_create([
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36"
            ]
        ]));
        Storage::put($fileName, $imageContent);
        // $localPath = Storage::path($fileName);

        return Storage::path($fileName);
    }
}