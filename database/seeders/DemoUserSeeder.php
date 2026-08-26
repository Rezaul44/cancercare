<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * শুধু local/dev-এর জন্য — RolePermissionSeeder-এর প্রতিটি role-এর একটা করে ডেমো ইউজার
 * তৈরি করে, যাতে সহজে বিভিন্ন role দিয়ে লগইন করে Filament admin panel টেস্ট করা যায়।
 * Production-এ স্কিপ হয় (hardcoded পাসওয়ার্ড লাইভ ডাটাবেজে যাওয়া থেকে আটকাতে)।
 */
class DemoUserSeeder extends Seeder
{
    private const PASSWORD = 'password';

    /**
     * @var list<string>
     */
    private const ROLES = [
        'super_admin',
        'verification_officer',
        'content_editor',
        'medical_reviewer',
        'field_agent',
        'hospital_manager',
        'support_agent',
        'finance',
        'doctor',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Production environment — ডেমো ইউজার সিড করা হলো না।');

            return;
        }

        foreach (self::ROLES as $role) {
            $user = User::firstOrCreate(
                ['email' => "{$role}@example.com"],
                [
                    'name' => ucwords(str_replace('_', ' ', $role)),
                    'password' => Hash::make(self::PASSWORD),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role]);

            $this->command?->info("{$user->email} → {$role} (password: ".self::PASSWORD.')');
        }
    }
}
