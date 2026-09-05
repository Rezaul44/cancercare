<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE patient_cases ADD FULLTEXT INDEX ft_patient_cases (display_name_bn, story_bn) WITH PARSER ngram');
            } catch (\Throwable $e) {
                // Fallback for MariaDB / engines without ngram parser plugin
                DB::statement('ALTER TABLE patient_cases ADD FULLTEXT INDEX ft_patient_cases (display_name_bn, story_bn)');
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE patient_cases DROP INDEX ft_patient_cases');
        }
    }
};
