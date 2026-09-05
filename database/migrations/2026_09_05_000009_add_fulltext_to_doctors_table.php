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
                DB::statement('ALTER TABLE doctors ADD FULLTEXT INDEX ft_doctors (name_bn, degrees_line_bn, current_position_bn) WITH PARSER ngram');
            } catch (\Throwable $e) {
                // Fallback for MariaDB / engines without ngram parser plugin
                DB::statement('ALTER TABLE doctors ADD FULLTEXT INDEX ft_doctors (name_bn, degrees_line_bn, current_position_bn)');
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE doctors DROP INDEX ft_doctors');
        }
    }
};
