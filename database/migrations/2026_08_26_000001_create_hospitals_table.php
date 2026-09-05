<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn', 200);
            $table->string('name_en', 200);
            $table->string('slug', 200)->unique();
            $table->enum('type', ['govt', 'private', 'npo']);
            $table->foreignId('district_id')->constrained('districts');
            $table->string('address_bn', 255);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 60);
            $table->unsignedSmallInteger('established_year')->nullable();
            $table->integer('bed_count')->nullable();
            $table->integer('oncologist_count')->nullable();
            $table->integer('outdoor_fee')->nullable();
            $table->boolean('emergency_24h')->default(false);
            $table->string('annual_patients', 80)->nullable();
            $table->string('cover_photo_path', 255)->nullable();
            $table->text('description_bn');
            $table->date('last_verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'suspended'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['district_id', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
