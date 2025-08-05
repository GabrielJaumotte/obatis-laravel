<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            // Appel sécurisé et robuste
            $response = Http::retry(3, 500)
                            ->timeout(120)
                            ->attach(
                                'file',
                                Storage::disk('wasabi')->get($this->filePath),
                                basename($this->filePath)
                            )
                            ->post(env('PDF_PARSER_API_URL'));

            if ($response->successful()) {
                $text = $response->json('text');

                // Exemple stockage en DB
                \App\Models\DocumentResult::create([
                    'file_path' => $this->filePath,
                    'text' => $text,
                    'status' => 'completed',
                ]);

                Log::info("Document traité avec succès", ['file' => $this->filePath]);
            } else {
                throw new \Exception("Échec API IA : HTTP " . $response->status());
            }

        } catch (\Throwable $e) {
            Log::error("Erreur IA PDF Parser", [
                'file' => $this->filePath,
                'error' => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }
}
