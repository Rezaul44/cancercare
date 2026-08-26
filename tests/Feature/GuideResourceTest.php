<?php

namespace Tests\Feature;

use App\Enums\DoctorStatus;
use App\Enums\GuideStatus;
use App\Filament\Resources\GuideResource;
use App\Filament\Resources\GuideResource\Pages\CreateGuide;
use App\Filament\Resources\GuideResource\Pages\EditGuide;
use App\Filament\Resources\GuideResource\Pages\ListGuides;
use App\Filament\Resources\GuideResource\RelationManagers\FaqsRelationManager;
use App\Filament\Resources\GuideResource\RelationManagers\MythsRelationManager;
use App\Filament\Resources\GuideResource\RelationManagers\StagesRelationManager;
use App\Filament\Resources\GuideResource\RelationManagers\StepsRelationManager;
use App\Filament\Resources\GuideResource\RelationManagers\TermsRelationManager;
use App\Filament\Resources\GuideResource\RelationManagers\VideosRelationManager;
use App\Models\CancerType;
use App\Models\Doctor;
use App\Models\Guide;
use App\Models\GuideFaq;
use App\Models\GuideMyth;
use App\Models\GuideStage;
use App\Models\GuideStep;
use App\Models\GuideTerm;
use App\Models\GuideVideo;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuideResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeDoctor(string $nameBn = 'ডা. সাদিয়া রহমান'): Doctor
    {
        return Doctor::create([
            'name_bn' => $nameBn,
            'name_en' => 'Dr. Sadia Rahman',
            'slug' => 'dr-sadia-rahman-'.uniqid(),
            'bmdc_number' => 'BMDC-'.rand(10000, 99999),
            'bmdc_verified_at' => now(),
            'photo_path' => 'doctors/photo.jpg',
            'degrees_line_bn' => 'এমবিবিএস, এফসিপিএস',
            'experience_years' => 12,
            'current_position_bn' => 'সার্জিক্যাল অনকোলজিস্ট',
            'gender' => 'female',
            'status' => DoctorStatus::Published,
            'doctor_approved_at' => now(),
        ]);
    }

    private function makeCancerType(string $nameBn = 'স্তন', string $slug = 'breast-cancer'): CancerType
    {
        return CancerType::create([
            'name_bn' => $nameBn,
            'name_en' => 'Breast Cancer',
            'slug' => $slug.'-'.uniqid(),
            'icon' => 'ribbon',
            'color_key' => 'pink',
            'short_description_bn' => 'স্তনের কোষে অস্বাভাবিক বৃদ্ধি',
            'is_common' => true,
            'guide_published' => false,
            'sort_order' => 1,
        ]);
    }

    private function makeGuide(?CancerType $cancerType = null, array $attributes = []): Guide
    {
        $cancer = $cancerType ?? $this->makeCancerType();

        return Guide::create(array_merge([
            'cancer_type_id' => $cancer->id,
            'title_bn' => 'স্তন ক্যান্সার — বিস্তারিত গাইড',
            'intro_bn' => 'স্তন ক্যান্সার সম্পর্কে বিস্তারিত তথ্য ও রূপরেখা।',
            'meta_title' => 'স্তন ক্যান্সার গাইড',
            'meta_description' => 'লক্ষণ, স্টেজ ও চিকিৎসা।',
            'read_minutes' => 6,
            'status' => GuideStatus::Draft,
        ], $attributes));
    }

    public function test_super_admin_can_access_guide_resource_pages(): void
    {
        $user = $this->makeUser('super_admin');
        $guide = $this->makeGuide();

        $this->actingAs($user)->get(GuideResource::getUrl('index'))->assertOk();
        $this->actingAs($user)->get(GuideResource::getUrl('create'))->assertOk();
        $this->actingAs($user)->get(GuideResource::getUrl('edit', ['record' => $guide]))->assertOk();
        $this->actingAs($user)->get(GuideResource::getUrl('view', ['record' => $guide]))->assertOk();
    }

    public function test_content_editor_can_access_list_create_and_edit_pages(): void
    {
        $user = $this->makeUser('content_editor');
        $guide = $this->makeGuide();

        $this->actingAs($user)->get(GuideResource::getUrl('index'))->assertOk();
        $this->actingAs($user)->get(GuideResource::getUrl('create'))->assertOk();
        $this->actingAs($user)->get(GuideResource::getUrl('edit', ['record' => $guide]))->assertOk();
    }

    public function test_unauthorized_role_cannot_access_guide_resource(): void
    {
        $user = $this->makeUser('field_agent');
        $guide = $this->makeGuide();

        $this->actingAs($user)->get(GuideResource::getUrl('index'))->assertForbidden();
        $this->actingAs($user)->get(GuideResource::getUrl('create'))->assertForbidden();
        $this->actingAs($user)->get(GuideResource::getUrl('edit', ['record' => $guide]))->assertForbidden();
    }

    public function test_content_editor_cannot_see_or_execute_publish_or_medical_approve_actions(): void
    {
        $user = $this->makeUser('content_editor');
        $guide = $this->makeGuide();

        Livewire::actingAs($user)
            ->test(ListGuides::class)
            ->assertTableActionHidden('publish', $guide)
            ->assertTableActionHidden('medicalApprove', $guide);

        Livewire::actingAs($user)
            ->test(EditGuide::class, ['record' => $guide->getRouteKey()])
            ->assertActionHidden('publish')
            ->assertActionHidden('medicalApprove');
    }

    public function test_medical_reviewer_can_approve_guide_medical_info(): void
    {
        $reviewer = $this->makeUser('medical_reviewer');
        $doctor = $this->makeDoctor('ডা. তানভীর আহমেদ');
        $guide = $this->makeGuide();

        Livewire::actingAs($reviewer)
            ->test(ListGuides::class)
            ->callTableAction('medicalApprove', $guide, data: [
                'doctor_id' => $doctor->id,
                'note' => 'চিকিৎসা তথ্য ও স্টেজ গাইডলাইন নির্ভুল',
            ])
            ->assertHasNoTableActionErrors();

        $guide->refresh();

        $this->assertEquals($doctor->id, $guide->reviewed_by_doctor_id);
        $this->assertNotNull($guide->reviewed_at);
        $this->assertEquals(GuideStatus::InReview, $guide->status);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Guide::class,
            'subject_id' => $guide->id,
            'causer_id' => $reviewer->id,
            'description' => 'guide.medical_approved',
        ]);
    }

    public function test_publish_action_is_disabled_if_not_reviewed(): void
    {
        $admin = $this->makeUser('super_admin');
        $guide = $this->makeGuide(); // unreviewed (reviewed_by_doctor_id & reviewed_at are null)

        Livewire::actingAs($admin)
            ->test(ListGuides::class)
            ->assertTableActionDisabled('publish', $guide);

        Livewire::actingAs($admin)
            ->test(EditGuide::class, ['record' => $guide->getRouteKey()])
            ->assertActionDisabled('publish');
    }

    public function test_super_admin_can_publish_reviewed_guide(): void
    {
        $admin = $this->makeUser('super_admin');
        $doctor = $this->makeDoctor();
        $cancer = $this->makeCancerType();
        $guide = $this->makeGuide($cancer, [
            'reviewed_by_doctor_id' => $doctor->id,
            'reviewed_at' => now()->subDay(),
            'status' => GuideStatus::InReview,
        ]);

        Livewire::actingAs($admin)
            ->test(ListGuides::class)
            ->assertTableActionEnabled('publish', $guide)
            ->callTableAction('publish', $guide)
            ->assertHasNoTableActionErrors();

        $guide->refresh();
        $cancer->refresh();

        $this->assertEquals(GuideStatus::Published, $guide->status);
        $this->assertNotNull($guide->published_at);
        $this->assertTrue($cancer->guide_published);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Guide::class,
            'subject_id' => $guide->id,
            'causer_id' => $admin->id,
            'description' => 'guide.published',
        ]);
    }

    public function test_super_admin_can_unpublish_published_guide(): void
    {
        $admin = $this->makeUser('super_admin');
        $doctor = $this->makeDoctor();
        $cancer = $this->makeCancerType();
        $guide = $this->makeGuide($cancer, [
            'reviewed_by_doctor_id' => $doctor->id,
            'reviewed_at' => now()->subDay(),
            'status' => GuideStatus::Published,
            'published_at' => now()->subDay(),
        ]);
        $cancer->update(['guide_published' => true]);

        Livewire::actingAs($admin)
            ->test(ListGuides::class)
            ->callTableAction('unpublish', $guide)
            ->assertHasNoTableActionErrors();

        $guide->refresh();
        $cancer->refresh();

        $this->assertEquals(GuideStatus::Draft, $guide->status);
        $this->assertFalse($cancer->guide_published);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Guide::class,
            'subject_id' => $guide->id,
            'causer_id' => $admin->id,
            'description' => 'guide.unpublished',
        ]);
    }

    public function test_guide_relation_managers_crud(): void
    {
        $editor = $this->makeUser('content_editor');
        $doctor = $this->makeDoctor();
        $guide = $this->makeGuide();

        // 1. Terms Relation Manager
        Livewire::actingAs($editor)
            ->test(TermsRelationManager::class, ['ownerRecord' => $guide, 'pageClass' => EditGuide::class])
            ->callTableAction('create', data: [
                'code' => 'ER Positive',
                'hint_bn' => 'হরমোন রিসেপ্টর',
                'plain_explanation_bn' => 'ইস্ট্রোজেন হরমোন কোষে বেশি উপস্থিত।',
                'why_matters_bn' => 'হরমোন থেরাপিতে কাজ করে।',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('guide_terms', [
            'guide_id' => $guide->id,
            'code' => 'ER Positive',
            'hint_bn' => 'হরমোন রিসেপ্টর',
        ]);

        // 2. Stages Relation Manager
        Livewire::actingAs($editor)
            ->test(StagesRelationManager::class, ['ownerRecord' => $guide, 'pageClass' => EditGuide::class])
            ->callTableAction('create', data: [
                'stage' => 'স্টেজ ১',
                'title_bn' => 'টিউমার ছোট, ছড়ায়নি',
                'description_bn' => 'প্রাথমিক পর্যায়',
                'typical_treatment_bn' => 'অপারেশন',
                'duration_bn' => '৩–৬ মাস',
                'cost_min' => 100000,
                'cost_max' => 200000,
                'severity_color' => 'teal',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('guide_stages', [
            'guide_id' => $guide->id,
            'stage' => 'স্টেজ ১',
        ]);

        // 3. Steps Relation Manager
        Livewire::actingAs($editor)
            ->test(StepsRelationManager::class, ['ownerRecord' => $guide, 'pageClass' => EditGuide::class])
            ->callTableAction('create', data: [
                'step_no' => 1,
                'title_bn' => 'অনকোলজিস্ট দেখান',
                'description_bn' => 'ক্যান্সার বিশেষজ্ঞের কাছে যান',
                'when_label_bn' => 'দ্রুত',
                'urgency' => 'urgent',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('guide_steps', [
            'guide_id' => $guide->id,
            'title_bn' => 'অনকোলজিস্ট দেখান',
        ]);

        // 4. Myths Relation Manager
        Livewire::actingAs($editor)
            ->test(MythsRelationManager::class, ['ownerRecord' => $guide, 'pageClass' => EditGuide::class])
            ->callTableAction('create', data: [
                'myth_bn' => 'বায়োপসি করলে ক্যান্সার ছড়ায়',
                'truth_bn' => 'এটি সম্পূর্ণ ভুল ধারণা। বায়োপসি ছাড়া সঠিক চিকিৎসা সম্ভব নয়।',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('guide_myths', [
            'guide_id' => $guide->id,
            'myth_bn' => 'বায়োপসি করলে ক্যান্সার ছড়ায়',
        ]);

        // 5. Faqs Relation Manager
        Livewire::actingAs($editor)
            ->test(FaqsRelationManager::class, ['ownerRecord' => $guide, 'pageClass' => EditGuide::class])
            ->callTableAction('create', data: [
                'question_bn' => 'স্তন ক্যান্সার কি বংশগত?',
                'answer_bn' => 'প্রায় ৫–১০% ক্ষেত্রে বংশগত হতে পারে (BRCA জিন)।',
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('guide_faqs', [
            'guide_id' => $guide->id,
            'question_bn' => 'স্তন ক্যান্সার কি বংশগত?',
        ]);

        // 6. Videos Relation Manager
        Livewire::actingAs($editor)
            ->test(VideosRelationManager::class, ['ownerRecord' => $guide, 'pageClass' => EditGuide::class])
            ->callTableAction('create', data: [
                'title_bn' => 'স্তন ক্যান্সার পরিচিতি',
                'video_url' => 'https://youtube.com/watch?v=sample',
                'platform' => 'youtube',
                'doctor_id' => $doctor->id,
                'duration_seconds' => 180,
                'sort_order' => 1,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('guide_videos', [
            'guide_id' => $guide->id,
            'title_bn' => 'স্তন ক্যান্সার পরিচিতি',
        ]);
    }
}
