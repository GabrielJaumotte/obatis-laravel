<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeOldWasabiBackups extends Command
{
    protected $signature = 'obatis:purge-old-wasabi-backups';
    protected $description = 'Supprime les dossiers de dumps Wasabi de plus de 30 jours';

    public function handle()
    {
        $disk = Storage::disk('wasabi');
        $now = Carbon::now();
        $threshold = $now->subDays(30);
        $prefix = 'db/';

        $this->info("🔍 Recherche des dossiers de dump à supprimer (avant {$threshold->toDateString()})...");

        $folders = $disk->directories($prefix);

        foreach ($folders as $folder) {
            $dateStr = basename($folder);

            if (!preg_match('/\d{4}-\d{2}-\d{2}/', $dateStr)) {
                $this->warn("⏭️  Dossier ignoré : $folder (nom non conforme)");
                continue;
            }

            try {
                $folderDate = Carbon::createFromFormat('Y-m-d', $dateStr);
                if ($folderDate->lessThan($threshold)) {
                    $this->warn("🧹 Suppression du dossier Wasabi : $folder");
                    $disk->deleteDirectory($folder);
                }
            } catch (\Exception $e) {
                $this->error("⚠️ Erreur avec $folder : " . $e->getMessage());
            }
        }

        $this->info("✅ Purge terminée.");
        return 0;
    }
}
