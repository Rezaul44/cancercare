<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chambers', function (Blueprint $table) {
            $table->foreign('hospital_id')
                ->references('id')
                ->on('hospitals')
                ->nullOnDelete();

            $table->index('hospital_id');
        });
    }

    public function down(): void
    {
        Schema::table('chambers', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropIndex(['hospital_id']);
        });
    }
};
