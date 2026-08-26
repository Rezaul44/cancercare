<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('doctor_applications')->nullOnDelete();
            $table->string('name_bn', 150);
            $table->string('name_en', 150);
            $table->string('slug', 160)->unique();
            $table->string('bmdc_number', 40)->unique();
            $table->timestamp('bmdc_verified_at')->nullable();
            $table->foreignId('bmdc_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('photo_path', 255);
            $table->string('degrees_line_bn', 400);
            $table->tinyInteger('experience_years')->unsigned();
            $table->string('current_position_bn', 200);
            $table->enum('gender', ['male', 'female']);
            $table->text('philosophy_intro_bn')->nullable();
            $table->integer('patients_treated')->nullable();
            $table->boolean('offers_second_opinion')->default(false);
            $table->boolean('offers_whatsapp')->default(false);
            $table->integer('whatsapp_fee')->nullable();
            $table->string('whatsapp_response_hours', 40)->nullable();
            $table->integer('second_opinion_fee')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'published', 'suspended'])->default('draft');
            $table->timestamp('doctor_approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->date('last_verified_at')->nullable();
            $table->integer('rotation_seed');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
