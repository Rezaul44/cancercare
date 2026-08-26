<?php

namespace Tests\Feature;

use App\Models\CancerType;
use App\Models\Doctor;
use App\Enums\DoctorStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_successfully_with_hero_and_cards(): void
    {
        $cancer = CancerType::create([
            'name_bn' => 'স্তন',
            'name_en' => 'Breast Cancer',
            'slug' => 'breast-cancer',
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তনের কোষে অস্বাভাবিক বৃদ্ধি',
            'is_common' => true,
            'doctor_count_cache' => 0,
            'hospital_count_cache' => 0,
            'guide_published' => false,
            'sort_order' => 1,
        ]);

        Doctor::create([
            'name_bn' => 'ডা. তানিয়া আহমেদ',
            'name_en' => 'Dr. Tania Ahmed',
            'slug' => 'dr-tania-ahmed-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'bmdc_verified_at' => now()->subMonth(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 12,
            'current_position_bn' => 'সহযোগী অধ্যাপক',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now()->subMonth(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('ক্যান্সার মানেই');
        $response->assertSee('পথ শেষ নয়');
        $response->assertSee('ডাক্তার খুঁজুন');
        $response->assertSee('হাসপাতাল তুলনা');
        $response->assertSee('খরচের হিসাব');
        $response->assertSee('রোগীদের সহায়তা');
        $response->assertSee('১০০% স্বাধীন ও নিরপেক্ষ');
        $response->assertSee('স্তন ক্যান্সার');
        $response->assertSee(route('doctors.index'));
        $response->assertSee(route('doctors.apply'));
    }

    public function test_navbar_and_footer_links_point_to_active_routes(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        // Navbar
        $response->assertSee('href="'.route('doctors.index').'"', false);
        $response->assertSee('href="'.route('doctors.apply').'"', false);
        $response->assertSee('href="'.url('/admin').'"', false);
    }
}
