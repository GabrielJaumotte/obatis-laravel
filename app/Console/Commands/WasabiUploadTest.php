<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WasabiUploadTest extends Command
{
    protected $signature = 'obatis:upload-test';
    protected $description = 'Teste la connexion et l’upload vers Wasabi S3';

    public function handle()
    {
        $disk = Storage::disk('wasabi');
        $filename = 'obatis_test_' . now()->format('Ymd_His') . '.txt';

        try {
            $ok = $disk->put($filename, 'Fichier test envoyé à ' . now());

            if ($ok) {
                $this->info("✅ Upload réussi : `$filename` envoyé sur Wasabi.");
                $this->info("📁 Liste des fichiers :");
                foreach ($disk->files('') as $file) {
                    $this->line(" - $file");
                }
            } else {
                $this->error("❌ Échec de l’envoi. Le disque a retourné false.");
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception : " . $e->getMessage());
        }

        return 0;
    }
}
