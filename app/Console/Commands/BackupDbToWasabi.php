<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDbToWasabi extends Command
{
    protected $signature = 'obatis:backup-db-to-wasabi';
    protected $description = 'Dump de la BDD PostgreSQL + Upload vers Wasabi';

    public function handle()
    {
        $this->info("📦 Démarrage du dump PostgreSQL...");

        $backupDir = '/root/backups';
        $date = now()->format('Y-m-d_H-i');
        $filename = "obatis_prod_$date.sql.gz";

        $fullpath = "$backupDir/$filename";
        $dayFolder = now()->format('Y-m-d');
        $pgHost = '91.99.197.55';
        $pgPort = '5432';
        $pgUser = 'obatis_app';
        $pgDb   = 'obatis_prod';
        $pgPass = 'TonMotDePasseObatisApp'; // remplace ici ou utilise ~/.pgpass

        // Commande pg_dump compressée
        $cmd = "pg_dump -h $pgHost -p $pgPort -U $pgUser $pgDb | gzip > $fullpath";

        $exitCode = null;
        $output = [];
        exec($cmd, $output, $exitCode);

        if (!file_exists($fullpath) || filesize($fullpath) < 100) {
            $this->error("❌ Échec du dump : fichier vide ou introuvable");
            return 1;
        }

        $this->info("✅ Dump terminé : $filename");

        // Upload vers Wasabi
        $this->info("📤 Envoi vers Wasabi...");
        try {
            $stream = fopen($fullpath, 'r');
            Storage::disk('wasabi')->put("db/$dayFolder/$filename", $stream);
            fclose($stream);
            $this->info("✅ Upload terminé vers Wasabi → db/$filename");
        } catch (\Exception $e) {
            $this->error("❌ Erreur upload : " . $e->getMessage());
            return 2;
        }

        // (Optionnel) Purge des dumps locaux de +7 jours
        $this->info("🧹 Suppression des dumps locaux de +7 jours...");
        exec("find $backupDir -name '*.sql.gz' -type f -mtime +7 -delete");

        return 0;
    }
}
