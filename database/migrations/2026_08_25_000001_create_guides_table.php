<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancer_type_id')->unique()->constrained('cancer_types')->cascadeOnDelete();
            $table->string('title_bn', 200);
            $table->text('intro_bn');
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->foreignId('reviewed_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->date('reviewed_at')->nullable();
            $table->string('sources_note_bn', 200)->default('WHO ও NCCN নির্দেশনা');
            $table->unsignedTinyInteger('read_minutes')->default(5);
            $table->string('status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->date('last_updated_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
