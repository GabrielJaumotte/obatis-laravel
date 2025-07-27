<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WasabiUploadFile extends Command
{
    protected $signature = 'obatis:upload-file-to-wasabi {source} {target}';
    protected $description = 'Upload d’un fichier local vers Wasabi via Storage::disk';

    public function handle()
    {
        $source = $this->argument('source');
        $target = $this->argument('target');

        if (!file_exists($source)) {
            $this->error("❌ Fichier source introuvable : $source");
            return 1;
        }

        try {
            $stream = fopen($source, 'r');
            Storage::disk('wasabi')->put($target, $stream);
            fclose($stream);

            $this->info("✅ Upload terminé vers Wasabi → $target");
        } catch (\Exception $e) {
            $this->error("❌ Échec : " . $e->getMessage());
            return 2;
        }

        return 0;
    }
}
