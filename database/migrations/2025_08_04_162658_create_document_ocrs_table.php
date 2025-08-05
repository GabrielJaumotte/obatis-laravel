<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_ocrs', function (Blueprint $table) {
            // UUID comme clé primaire (compatible avec PostgreSQL)
            $table->uuid('id')->primary();

            // 🔗 Relations
            $table->uuid('source_file_id'); // FK project_documents
            $table->uuid('project_id')->nullable();
            $table->uuid('worker_id')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('contract_id')->nullable();

            // 📄 Contenu OCR & analyses IA
            $table->longText('raw_text');
            $table->jsonb('parsed_data')->default(DB::raw("'{}'::jsonb"));
            $table->float('ocr_confidence')->nullable();
            $table->string('language_detected', 8)->default('fr');

            // ⚠️ Métadonnées
            $table->string('status')->default('active');
            $table->jsonb('alerts')->default(DB::raw("'[]'::jsonb"));
            $table->jsonb('summary_ai')->default(DB::raw("'{}'::jsonb"));
            $table->jsonb('metadata')->default(DB::raw("'{}'::jsonb"));

            // 👤 Traçabilité
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            // Indexes pour la perf (optionnels mais recommandé)
            $table->index('project_id');
            $table->index('worker_id');
            $table->index('invoice_id');
            $table->index('contract_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('source_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_ocrs');
    }
};
