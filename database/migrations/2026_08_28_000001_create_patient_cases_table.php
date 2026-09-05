<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_code', 20)->unique();
            $table->string('real_name', 150);
            $table->string('display_name_bn', 150);
            $table->unsignedTinyInteger('age');
            $table->string('gender', 20); // male, female, other
            $table->foreignId('cancer_type_id')->constrained('cancer_types');
            $table->string('stage', 80)->nullable();
            $table->foreignId('district_id')->constrained('districts');
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->nullOnDelete();
            $table->string('treating_doctor_name', 150)->nullable();
            $table->text('story_bn');
            $table->unsignedInteger('amount_needed');
            $table->string('photo_path', 255)->nullable();
            $table->boolean('show_photo')->default(false);
            $table->string('anonymity_level', 30)->default('full_name'); // full_name, partial, changed_name, initials_only
            $table->string('consent_form_path', 255)->nullable();
            $table->date('consent_signed_at')->nullable();
            $table->string('status', 30)->default('draft'); // draft, verifying, published, expired, fulfilled, withdrawn
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expires_at']);
            $table->index(['cancer_type_id', 'status']);
            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_cases');
    }
};
