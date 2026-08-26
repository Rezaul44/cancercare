<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * docs/CCB_system_documentation.md ধারা ৬ (ইউজার রোল ও পারমিশন) — permission
 * matrix হুবহু এখানে কোডে রূপান্তরিত। ম্যাট্রিক্সের প্রতিটি সারি একটি মডিউল, প্রতিটি
 * কলাম একটি রোল; ✓ = ওই মডিউলের সংশ্লিষ্ট সব পারমিশন, R = শুধু view, "own" = শুধু
 * নিজের রেকর্ডে। ম্যাট্রিক্স বদলালে শুধু এই ফাইলটাই বদলাতে হবে।
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * সব পারমিশন — 'module.action' ফরম্যাটে।
     *
     * @var list<string>
     */
    private const PERMISSIONS = [
        'doctor_applications.view',
        'doctor_applications.manage',

        'doctors.view',
        'doctors.view_own',
        'doctors.manage',
        'doctors.publish',
        'doctors.approve_own',

        'ratings.view',
        'ratings.collect',
        'ratings.verify',

        'hospitals.view',
        'hospitals.manage',

        'guides.view',
        'guides.manage',
        'guides.medical_approve',
        'guides.publish',

        'cases.view',
        'cases.create',
        'cases.manage',
        'cases.verify',
        'cases.publish',

        'cost_rates.view',
        'cost_rates.manage',

        'second_opinions.view',
        'second_opinions.manage',
        'second_opinions.respond_own',

        'payments.manage',
        'payments.view_own',

        'helpline.view',
        'helpline.manage',

        'settings.manage',
        'users.manage',

        'activity_logs.view',
        'activity_logs.view_own',
    ];

    /**
     * super_admin বাদে বাকি ৮টি রোলের পারমিশন। super_admin সব পারমিশন পায় (নিচে run() দেখো)।
     *
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSIONS = [
        'verification_officer' => [
            'doctor_applications.view', 'doctor_applications.manage',
            'doctors.view', 'doctors.manage', 'doctors.publish',
            'ratings.view', 'ratings.verify',
            'hospitals.view',
            'cases.view', 'cases.manage', 'cases.verify', 'cases.publish',
            'second_opinions.view',
            'helpline.view',
            'activity_logs.view_own',
        ],

        'content_editor' => [
            'doctors.view',
            'guides.view', 'guides.manage',
            'cost_rates.view',
        ],

        'medical_reviewer' => [
            'guides.view', 'guides.medical_approve',
            'cost_rates.view',
        ],

        'field_agent' => [
            'doctors.view',
            'ratings.collect',
            'hospitals.view',
            'cases.view', 'cases.create',
            'helpline.view',
        ],

        'hospital_manager' => [
            'hospitals.view', 'hospitals.manage',
        ],

        'support_agent' => [
            'doctor_applications.view',
            'doctors.view',
            'hospitals.view',
            'guides.view',
            'cases.view', 'cases.create',
            'second_opinions.view',
            'helpline.view', 'helpline.manage',
        ],

        'finance' => [
            'second_opinions.view',
            'payments.manage',
            'activity_logs.view_own',
        ],

        'doctor' => [
            'doctors.view_own',
            'doctors.approve_own',
            'second_opinions.respond_own',
            'payments.view_own',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        $this->seedSuperAdminUser();
    }

    /**
     * .env-এর SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD থেকে ডিফল্ট super_admin ইউজার।
     * দুটোর একটাও না থাকলে স্কিপ — প্রোডাকশনে হার্ডকোড পাসওয়ার্ড রাখা যাবে না।
     */
    private function seedSuperAdminUser(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn(
                'SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD .env-এ সেট নেই — ডিফল্ট super_admin ইউজার তৈরি করা হয়নি।'
            );

            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
                'password' => Hash::make($password),
            ]
        );

        $user->assignRole('super_admin');
    }
}
