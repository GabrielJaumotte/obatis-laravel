<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentOcr extends Model
{
    // Clé primaire UUID, pas d'autoincrement
    public $incrementing = false;
    protected $keyType = 'string';

    // Nom de la table (précaution si Laravel veut "document_ocrs")
    protected $table = 'document_ocrs';

    // Attributs remplissables
    protected $fillable = [
        'id',
        'source_file_id',
        'project_id',
        'worker_id',
        'invoice_id',
        'contract_id',
        'raw_text',
        'parsed_data',
        'ocr_confidence',
        'language_detected',
        'status',
        'alerts',
        'summary_ai',
        'metadata',
        'created_by',
        'created_at',
        'updated_at'
    ];

    // Casts pour les champs spéciaux
    protected $casts = [
        'parsed_data'      => 'array',
        'alerts'           => 'array',
        'summary_ai'       => 'array',
        'metadata'         => 'array',
        'ocr_confidence'   => 'float',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Boot : Génération automatique de l’UUID à la création
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // (Optionnel) Ajoute ici des relations Eloquent si tu veux, ex :
    // public function project() { return $this->belongsTo(Project::class); }
    // public function worker() { return $this->belongsTo(WorkerProfile::class); }
    // public function sourceFile() { return $this->belongsTo(ProjectDocument::class, 'source_file_id'); }
}
