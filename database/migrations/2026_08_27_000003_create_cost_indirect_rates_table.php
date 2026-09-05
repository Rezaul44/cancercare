<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_indirect_rates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique(); // travel, stay, food, outside_medicine, income_loss_far, income_loss_near, misc
            $table->string('label_bn', 100);
            $table->unsignedInteger('base_amount')->default(0);
            $table->string('unit', 50); // per_trip, per_month, percent_of_direct
            $table->decimal('percent_value', 5, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_indirect_rates');
    }
};
