<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chambers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            // hospitals টেবিল এখনো নেই (schema doc ধারা ৫, এই migration ব্যাচের বাইরে) —
            // কলাম রাখা হলো schema অনুযায়ী, FK constraint hospitals migration আসার পর যোগ হবে।
            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->string('name_bn', 160);
            $table->string('address_bn', 255);
            $table->foreignId('district_id')->constrained();
            $table->enum('type', ['govt', 'private', 'npo']);
            $table->integer('fee');
            $table->string('days_bn', 120);
            $table->time('time_from');
            $table->time('time_to');
            $table->integer('avg_wait_minutes')->nullable();
            $table->string('next_available_note', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->index(['district_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chambers');
    }
};
