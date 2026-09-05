<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * layouts/app.blade.php ও এর কম্পোনেন্টগুলো (x-nav, x-footer, x-search-box,
 * x-medical-disclaimer, x-ranking-notice) docs/prototypes/homepage.html ও
 * doctor_directory.html/intake_flow.html অনুযায়ী সঠিকভাবে রেন্ডার হচ্ছে কিনা যাচাই করে।
 */
class LayoutComponentsTest extends TestCase
{
    public function test_nav_component_renders_topbar_and_menu(): void
    {
        $html = Blade::render('<x-nav />');

        $this->assertStringContainsString('হেল্পলাইন ০৯৬১১-৭৭৭৮৮৮', $html);
        $this->assertStringContainsString('কেউ টাকা দিয়ে তালিকায় ওঠে না', $html);
        $this->assertStringContainsString('ডাক্তার', $html);
        $this->assertStringContainsString('ক্যান্সার গাইড', $html);
        $this->assertStringContainsString('সাইন ইন', $html);
        $this->assertStringContainsString('শুরু করুন', $html);
        $this->assertStringContainsString(asset('images/logo.png'), $html);
        $this->assertStringNotContainsString('<style', $html);
    }

    public function test_footer_component_renders_columns_and_copyright(): void
    {
        $html = Blade::render('<x-footer />');

        $this->assertStringContainsString('যাচাইকরণের পদ্ধতি', $html);
        $this->assertStringContainsString('গোপনীয়তা নীতি', $html);
        $this->assertStringContainsString('© ২০২৬ CancerCare Bangladesh', $html);
        $this->assertStringContainsString(asset('images/logo.png'), $html);
        $this->assertStringNotContainsString('<style', $html);
    }

    public function test_search_box_has_alpine_behaviour_and_categories(): void
    {
        $html = Blade::render('<x-search-box />');

        $this->assertStringContainsString('x-data="searchSuggest()"', $html);
        $this->assertStringContainsString('x-model="query"', $html);
        $this->assertStringContainsString('@click.outside="open = false"', $html);
        // Phase 8.1 — ড্রপডাউন এখন /ajax/search/suggest থেকে real ডেটা দেখায়, ৪টি ক্যাটাগরি: ডাক্তার/হাসপাতাল/গাইড/রোগীর সহায়তা
        $this->assertStringContainsString('@input="onInput()"', $html);
        $this->assertStringContainsString('x-ref="doctorsList"', $html);
        $this->assertStringContainsString('x-ref="hospitalsList"', $html);
        $this->assertStringContainsString('x-ref="guidesList"', $html);
        $this->assertStringContainsString('x-ref="patient_casesList"', $html);
        $this->assertStringContainsString('ডাক্তার', $html);
        $this->assertStringContainsString('হাসপাতাল', $html);
        $this->assertStringContainsString('ক্যান্সার গাইড', $html);
        $this->assertStringContainsString('রোগীর সহায়তা', $html);
        $this->assertStringContainsString('হেল্পলাইনে কল করুন', $html);
        $this->assertStringNotContainsString('<style', $html);
    }

    public function test_medical_disclaimer_shows_default_text_when_no_slot_given(): void
    {
        $html = Blade::render('<x-medical-disclaimer />');

        $this->assertStringContainsString('এটি চিকিৎসা পরামর্শ নয়', $html);
        $this->assertStringContainsString('নিজে থেকে কোনো পরীক্ষা বা চিকিৎসা শুরু করবেন না', $html);
    }

    public function test_medical_disclaimer_slot_overrides_default_text(): void
    {
        $html = Blade::render('<x-medical-disclaimer>এই পাতার জন্য কাস্টম বার্তা</x-medical-disclaimer>');

        $this->assertStringContainsString('এই পাতার জন্য কাস্টম বার্তা', $html);
        $this->assertStringNotContainsString('নিজে থেকে কোনো পরীক্ষা বা চিকিৎসা শুরু করবেন না', $html);
    }

    public function test_ranking_notice_lists_basis_with_bengali_conjunction(): void
    {
        $html = Blade::render(
            '<x-ranking-notice :basis="$basis" />',
            ['basis' => ['বিশেষত্ব', 'দূরত্ব', 'রোগীর মতামত']]
        );

        $this->assertStringContainsString('বিশেষত্ব, দূরত্ব ও রোগীর মতামত', $html);
        $this->assertStringContainsString('টাকা দিয়ে উপরে আসতে পারে না', $html);
    }

    public function test_ranking_notice_without_basis_still_renders_base_sentence(): void
    {
        $html = Blade::render('<x-ranking-notice />');

        $this->assertStringContainsString('কোনো ডাক্তার বা হাসপাতাল টাকা দিয়ে উপরে আসতে পারে না', $html);
    }

    public function test_layout_wraps_yielded_content_with_nav_and_footer(): void
    {
        $html = Blade::render(<<<'BLADE'
            @extends('layouts.app')
            @section('content')
                <div id="page-content">টেস্ট পাতার কনটেন্ট</div>
            @endsection
            BLADE);

        // x-nav
        $this->assertStringContainsString('হেল্পলাইন ০৯৬১১-৭৭৭৮৮৮', $html);
        // yielded content, in the right place between nav and footer
        $this->assertStringContainsString('টেস্ট পাতার কনটেন্ট', $html);
        // x-footer
        $this->assertStringContainsString('যাচাইকরণের পদ্ধতি', $html);

        $navPos = strpos($html, 'হেল্পলাইন ০৯৬১১-৭৭৭৮৮৮');
        $contentPos = strpos($html, 'টেস্ট পাতার কনটেন্ট');
        $footerPos = strpos($html, 'যাচাইকরণের পদ্ধতি');
        $this->assertTrue($navPos < $contentPos && $contentPos < $footerPos);

        // fonts + icons + Alpine wiring, no leftover inline <style>
        $this->assertStringContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('tabler-icons', $html);
        $this->assertStringNotContainsString('<style>', $html);
    }
}
