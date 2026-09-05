<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_base_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancer_type_id')->constrained('cancer_types')->cascadeOnDelete();
            $table->string('service_key', 50); // diagnosis, surgery, chemo, radiation, targeted
            $table->unsignedInteger('govt_amount')->default(0);
            $table->unsignedTinyInteger('default_months')->default(8);
            $table->boolean('is_applicable')->default(true);
            $table->timestamps();

            $table->unique(['cancer_type_id', 'service_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_base_rates');
    }
};
