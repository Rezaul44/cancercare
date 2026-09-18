<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Filament\Resources\DoctorResource;
use App\Filament\Resources\DoctorResource\Pages\CreateDoctor;
use App\Filament\Resources\DoctorResource\Pages\EditDoctor;
use App\Filament\Resources\DoctorResource\Pages\ListDoctors;
use App\Filament\Resources\DoctorResource\RelationManagers\CancerTypesRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\ChambersRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\HospitalsRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\PatientStoriesRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\PatientTestimonialsRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\PhilosophyPointsRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\ServicesRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\TimelineRelationManager;
use App\Filament\Resources\DoctorResource\RelationManagers\VideosRelationManager;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Doctor;
use App\Models\DoctorType;
use App\Models\Hospital;
use App\Models\User;
use App\Support\YouTubeHelper;
use Database\Seeders\CancerTypeSeeder;
use Database\Seeders\DivisionDistrictSeeder;
use Database\Seeders\DoctorTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected DoctorType $doctorType;
    protected CancerType $cancerType;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
        (new DivisionDistrictSeeder)->run();
        (new DoctorTypeSeeder)->run();
        (new CancerTypeSeeder)->run();

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');

        $this->regularUser = User::factory()->create();

        $this->doctorType = DoctorType::first();
        $this->cancerType = CancerType::first();
        $this->district = District::first();
    }

    private function makeDoctor(array $overrides = []): Doctor
    {
        return Doctor::create(array_merge([
            'name_bn' => 'ডা. পরীক্ষা '.uniqid(),
            'name_en' => 'Test Doctor '.uniqid(),
            'slug' => 'test-doctor-'.uniqid(),
            'bmdc_number' => 'BMDC-'.uniqid(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 10,
            'current_position_bn' => 'কনসালট্যান্ট',
            'gender' => 'male',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
        ], $overrides));
    }

    public function test_guest_cannot_access_doctor_resource(): void
    {
        $this->get(DoctorResource::getUrl('index'))->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_doctor_resource_index(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListDoctors::class)
            ->assertSuccessful();
    }

    public function test_admin_can_create_doctor(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateDoctor::class)
            ->fillForm([
                'name_bn' => 'অধ্যাপক ডা. টেস্ট ডাক্তার',
                'name_en' => 'Prof. Dr. Test Doctor',
                'slug' => 'prof-dr-test-doctor',
                'bmdc_number' => 'BMDC-TEST-999',
                'degrees_line_bn' => 'MBBS, FCPS, MS (Surgical Oncology)',
                'current_position_bn' => 'অধ্যাপক ও বিভাগীয় প্রধান',
                'gender' => 'male',
                'experience_years' => 15,
                'patients_treated' => 3000,
                'status' => DoctorStatus::Published->value,
                'doctor_approved_at' => now()->toDateTimeString(),
                'doctorTypes' => [$this->doctorType->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('doctors', [
            'name_bn' => 'অধ্যাপক ডা. টেস্ট ডাক্তার',
            'bmdc_number' => 'BMDC-TEST-999',
            'slug' => 'prof-dr-test-doctor',
        ]);
    }

    public function test_chambers_relation_manager_adds_chamber(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        Livewire::test(ChambersRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'name_bn' => 'ল্যাবএইড স্পেশালাইজড হাসপাতাল',
                'district_id' => $this->district->id,
                'address_bn' => 'বাড়ি-১, রোড-৪, ধানমন্ডি, ঢাকা',
                'type' => 'private',
                'fee' => 1500,
                'days_bn' => 'শনি, সোম, বুধ',
                'time_from' => '17:00',
                'time_to' => '21:00',
                'is_active' => true,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('chambers', [
            'doctor_id' => $doctor->id,
            'name_bn' => 'ল্যাবএইড স্পেশালাইজড হাসপাতাল',
            'fee' => 1500,
        ]);
    }

    public function test_videos_relation_manager_supports_direct_and_embed_youtube_links(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        // Test with standard watch URL
        Livewire::test(VideosRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'type' => 'intro',
                'title_bn' => 'পরিচিতি ভিডিও',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'platform' => 'youtube',
                'duration_seconds' => 180,
            ])
            ->assertHasNoTableActionErrors();

        // Test with embed link
        Livewire::test(VideosRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'type' => 'educational',
                'title_bn' => 'স্তন ক্যান্সার সচেতনতা',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'platform' => 'youtube',
                'duration_seconds' => 240,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('doctor_videos', [
            'doctor_id' => $doctor->id,
            'type' => 'intro',
        ]);

        $this->assertDatabaseHas('doctor_videos', [
            'doctor_id' => $doctor->id,
            'type' => 'educational',
        ]);
    }

    public function test_youtube_helper_parses_various_youtube_url_formats(): void
    {
        $videoId = 'dQw4w9WgXcQ';

        $formats = [
            "https://www.youtube.com/watch?v={$videoId}",
            "https://youtu.be/{$videoId}",
            "https://www.youtube.com/embed/{$videoId}",
            "https://www.youtube.com/shorts/{$videoId}",
            "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/{$videoId}\" frameborder=\"0\"></iframe>",
        ];

        foreach ($formats as $format) {
            $extractedId = YouTubeHelper::extractVideoId($format);
            $this->assertEquals($videoId, $extractedId, "Failed on format: {$format}");

            $watchUrl = YouTubeHelper::toWatchUrl($format);
            $this->assertEquals("https://www.youtube.com/watch?v={$videoId}", $watchUrl);

            $embedUrl = YouTubeHelper::toEmbedUrl($format);
            $this->assertEquals("https://www.youtube.com/embed/{$videoId}", $embedUrl);

            $thumbUrl = YouTubeHelper::getThumbnailUrl($format);
            $this->assertEquals("https://img.youtube.com/vi/{$videoId}/hqdefault.jpg", $thumbUrl);
        }
    }

    public function test_services_relation_manager_adds_service(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'cancer_type_id' => $this->cancerType->id,
                'title_bn' => 'ব্রেস্ট কনজারভেশন সার্জারি',
                'description_bn' => 'টিউমার অপসারণের আধুনিক সার্জারি',
                'icon' => 'cut',
                'badge_text_bn' => 'বিশেষ অভিজ্ঞতা',
                'badge_color' => 'teal',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('doctor_services', [
            'doctor_id' => $doctor->id,
            'title_bn' => 'ব্রেস্ট কনজারভেশন সার্জারি',
        ]);
    }

    public function test_timeline_relation_manager_adds_timeline(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        Livewire::test(TimelineRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'year_label' => '২০১৮ - বর্তমান',
                'title_bn' => 'সহযোগী অধ্যাপক',
                'institution_bn' => 'ঢাকা মেডিকেল কলেজ ও হাসপাতাল',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('doctor_timeline', [
            'doctor_id' => $doctor->id,
            'title_bn' => 'সহযোগী অধ্যাপক',
        ]);
    }

    public function test_patient_testimonials_relation_manager_adds_testimonial(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        Livewire::test(PatientTestimonialsRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'anonymized_label_bn' => 'রোগী "ক"',
                'cancer_type_id' => $this->cancerType->id,
                'stage' => '2',
                'outcome_bn' => 'সম্পূর্ণ সুস্থ',
                'year' => 2024,
                'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'thumbnail_color_key' => 'teal',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('doctor_patient_testimonials', [
            'doctor_id' => $doctor->id,
            'anonymized_label_bn' => 'রোগী "ক"',
        ]);
    }

    public function test_patient_stories_relation_manager_adds_story(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        Livewire::test(PatientStoriesRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'patient_label_bn' => 'কামাল হোসেন',
                'cancer_type_id' => $this->cancerType->id,
                'outcome_duration_bn' => '৩ বছর সুস্থ',
                'quote_bn' => 'ডাক্তার বাবুর চিকিৎসা পেয়ে আমি ভালো আছি।',
                'then_bn' => 'তখন অসহ্য যন্ত্রণা ছিল',
                'now_bn' => 'এখন সম্পূর্ণ সুস্থ',
                'year' => 2023,
                'is_name_changed' => true,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('doctor_patient_stories', [
            'doctor_id' => $doctor->id,
            'patient_label_bn' => 'কামাল হোসেন',
        ]);
    }

    public function test_philosophy_points_relation_manager_adds_point(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($this->adminUser);

        Livewire::test(PhilosophyPointsRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('create', data: [
                'title_bn' => 'রোগীর মতামত ও সম্মতি প্রধান',
                'description_bn' => 'রোগী ও তার পরিবারের সাথে খোলামেলা আলোচনা।',
                'icon' => 'heart',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('doctor_philosophy_points', [
            'doctor_id' => $doctor->id,
            'title_bn' => 'রোগীর মতামত ও সম্মতি প্রধান',
        ]);
    }

    public function test_hospitals_relation_manager_attaches_hospital(): void
    {
        $doctor = $this->makeDoctor();
        $hospital = Hospital::create([
            'name_bn' => 'জাতীয় ক্যান্সার ইনস্টিটিউট',
            'name_en' => 'NICRH',
            'slug' => 'nicrh-'.uniqid(),
            'description_bn' => 'জাতীয় ক্যান্সার গবেষণা ইনস্টিটিউট ও হাসপাতাল।',
            'type' => 'govt',
            'district_id' => $this->district->id,
            'phone' => '01700000000',
            'address_bn' => 'মহাখালী, ঢাকা',
            'status' => 'published',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(HospitalsRelationManager::class, [
            'ownerRecord' => $doctor,
            'pageClass' => EditDoctor::class,
        ])
            ->callTableAction('attach', data: [
                'recordId' => $hospital->id,
                'schedule_note_bn' => 'প্রতি রবি ও মঙ্গলবার',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('hospital_doctor', [
            'doctor_id' => $doctor->id,
            'hospital_id' => $hospital->id,
            'schedule_note_bn' => 'প্রতি রবি ও মঙ্গলবার',
        ]);
    }

    public function test_filament_default_filesystem_disk_is_public(): void
    {
        $this->assertEquals('public', config('filament.default_filesystem_disk'));
    }

    public function test_doctor_photo_uses_public_disk_with_same_origin_relative_url(): void
    {
        $url = \Illuminate\Support\Facades\Storage::disk('public')->url('doctors/test.jpg');
        $this->assertSame('/storage/doctors/test.jpg', $url);
    }

    public function test_admin_can_access_edit_doctor_with_photo(): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->put('doctors/sample.jpg', 'fake-image-content');

        $doctor = $this->makeDoctor([
            'photo_path' => 'doctors/sample.jpg',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(EditDoctor::class, [
            'record' => $doctor->getRouteKey(),
        ])
            ->assertSuccessful()
            ->assertHasNoFormErrors();
    }
}

