<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->string('code', 120);
            $table->string('slug', 140);
            $table->string('hint_bn', 120);
            $table->text('plain_explanation_bn');
            $table->text('why_matters_bn');
            $table->json('scale')->nullable();
            $table->string('search_keywords', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['guide_id', 'sort_order']);
            $table->index(['guide_id', 'slug']);
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE guide_terms ADD FULLTEXT INDEX ft_terms (code, search_keywords, plain_explanation_bn) WITH PARSER ngram');
            } catch (\Throwable $e) {
                // Fallback for MariaDB / engines without ngram parser plugin
                DB::statement('ALTER TABLE guide_terms ADD FULLTEXT INDEX ft_terms (code, search_keywords, plain_explanation_bn)');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_terms');
    }
};
