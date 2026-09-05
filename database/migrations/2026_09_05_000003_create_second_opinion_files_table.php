<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('second_opinion_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('second_opinion_requests')->cascadeOnDelete();
            $table->string('file_path', 255); // s3_private ডিস্কে, signed URL দিয়ে সার্ভ হয়
            $table->string('original_name', 255);
            $table->string('mime', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('second_opinion_files');
    }
};
