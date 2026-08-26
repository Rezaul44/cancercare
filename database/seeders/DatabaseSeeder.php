<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DivisionDistrictSeeder::class,
            CancerTypeSeeder::class,
            DoctorTypeSeeder::class,
            CapabilitySeeder::class,
            RatingCriteriaSeeder::class,
            SettingsSeeder::class,
            RolePermissionSeeder::class,
            DemoUserSeeder::class,
        ]);
    }
}
