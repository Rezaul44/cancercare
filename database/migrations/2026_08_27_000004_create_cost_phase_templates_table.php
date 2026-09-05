<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_phase_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancer_type_id')->nullable()->constrained('cancer_types')->nullOnDelete();
            $table->string('service_key', 50); // diagnosis, surgery, chemo, radiation, targeted, followup
            $table->string('phase_title_bn', 150);
            $table->string('when_bn', 150);
            $table->json('breakdown'); // e.g. [["বায়োপসি ও প্যাথলজি", 0.45], ...]
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_phase_templates');
    }
};
