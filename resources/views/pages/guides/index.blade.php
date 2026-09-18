@extends('layouts.app')

@php
    $seoDescription = 'ক্যান্সারের লক্ষণ, বায়োপসি রিপোর্ট বোঝা, স্টেজ ও চিকিৎসার ধাপ — WHO ও NCCN গাইডলাইন অনুসারে বিশেষজ্ঞ ডাক্তারদের লিখিত গাইড।';
    $seoKeywords = 'ক্যান্সার গাইড, ক্যান্সার লক্ষণ, বায়োপসি রিপোর্ট, ক্যান্সার চিকিৎসা খরচ, ক্যান্সার স্টেজ';
@endphp

@section('title', 'ক্যান্সার গাইড — CancerCare Bangladesh')

@section('content')
{{-- 1. HEADER & SEARCH HERO --}}
<div class="bg-white border-b border-line pt-12 pb-10 relative overflow-hidden" x-data="{ localSearch: '{{ addslashes($search) }}' }">
    <div class="absolute -top-[200px] -right-[80px] w-[620px] h-[440px] bg-[radial-gradient(ellipse_at_center,var(--pink-50)_0%,rgba(255,255,255,0)_70%)] pointer-events-none"></div>

    <div class="max-w-[1240px] mx-auto px-10 relative z-[2]">
        <div class="font-bn text-[11.5px] tracking-[0.12em] uppercase text-slate-400 font-semibold mb-3">
            ক্যান্সার গাইড
        </div>
        <h1 class="font-serif text-[40px] font-medium tracking-[-0.022em] leading-[1.16] mb-3 max-w-[680px] text-ink">
            ক্যান্সার সম্পর্কে জানুন,<br><em class="not-italic text-pink-600 font-semibold">নিজের ভাষায়, নির্ভুলভাবে</em>
        </h1>
        <p class="font-bn text-[16.5px] text-slate-500 leading-[1.7] max-w-[660px] mb-7">
            লক্ষণ, টেস্ট, বায়োপসি রিপোর্ট বোঝা, চিকিৎসার ধাপ ও সম্ভাব্য খরচ — WHO ও NCCN গাইডলাইন অনুসারে দেশের শীর্ষ ক্যান্সার বিশেষজ্ঞদের লিখিত ও যাচাইকৃত গাইড।
        </p>

        {{-- Guide Search Box --}}
        <form action="{{ route('guides.index') }}" method="GET" class="relative max-w-[600px]">
            <div class="relative">
                <i class="ti ti-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 text-[21px] pointer-events-none"></i>
                <input
                    type="text"
                    name="q"
                    x-model="localSearch"
                    placeholder="ক্যান্সারের নাম বা রিপোর্টের শব্দ লিখুন (যেমন: স্তন, HER2, বায়োপসি)"
                    class="font-bn w-full py-[18px] pl-14 pr-12 border-2 border-line rounded-[15px] text-[15.5px] text-ink bg-white shadow-[0_4px_18px_rgba(20,23,25,0.05)] focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition"
                >
                @if($search !== '')
                    <a href="{{ route('guides.index') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-ink">
                        <i class="ti ti-x text-lg"></i>
                    </a>
                @endif
            </div>

            <div class="font-bn text-[13px] text-slate-400 mt-2.5 leading-[1.6]">
                <span>জনপ্রিয়:</span>
                <a href="{{ route('guides.index', ['q' => 'স্তন']) }}" class="text-slate-600 font-medium hover:text-pink-600 ml-1">স্তন ক্যান্সার</a> ·
                <a href="{{ route('guides.index', ['q' => 'HER2']) }}" class="text-slate-600 font-medium hover:text-pink-600 ml-1">HER2 Positive</a> ·
                <a href="{{ route('guides.index', ['q' => 'বায়োপসি']) }}" class="text-slate-600 font-medium hover:text-pink-600 ml-1">বায়োপসি রিপোর্ট</a> ·
                <a href="{{ route('guides.index', ['q' => 'ফুসফুস']) }}" class="text-slate-600 font-medium hover:text-pink-600 ml-1">ফুসফুস ক্যান্সার</a> ·
                <a href="{{ route('guides.index', ['q' => 'স্টেজ ৩']) }}" class="text-slate-600 font-medium hover:text-pink-600 ml-1">স্টেজ ৩</a>
            </div>
        </form>

        {{-- If searching and medical report terms match --}}
        @if($matchingTerms->isNotEmpty())
            <div class="mt-5 max-w-[600px] bg-mist border border-line rounded-xl p-4">
                <div class="font-bn text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    <i class="ti ti-file-search mr-1"></i> রিপোর্টে পাওয়া প্রাসঙ্গিক মেডিকেল শব্দ ({{ $matchingTerms->count() }}টি)
                </div>
                <div class="flex flex-col gap-2">
                    @foreach($matchingTerms as $term)
                        <a href="{{ url('/guide/' . ($term->guide?->cancerType?->slug ?? '')) }}#{{ $term->slug }}" class="flex items-center justify-between p-3 rounded-lg bg-white border border-line/60 hover:border-slate-300 transition text-left group">
                            <div>
                                <span class="font-semibold text-sm text-ink group-hover:text-pink-600">{{ $term->code }}</span>
                                @if($term->hint_bn)
                                    <span class="font-bn text-xs text-slate-400 ml-1.5">({{ $term->hint_bn }})</span>
                                @endif
                                <p class="font-bn text-xs text-slate-500 mt-0.5">{{ Str::limit($term->plain_explanation_bn, 70) }}</p>
                            </div>
                            <i class="ti ti-arrow-right text-slate-400 group-hover:text-pink-600 group-hover:translate-x-0.5 transition shrink-0 ml-2"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- 2. MAIN BODY --}}
<div
    class="pt-8 pb-16 bg-mist"
    x-data="{
        activeTab: 'all',
        tabCount: {
            all: {{ $publishedCancerTypes->count() }}
        }
    }"
>
    <div class="max-w-[1240px] mx-auto px-10">

        {{-- 3 Tool Shortcut Cards --}}
        <div class="grid grid-cols-3 gap-3.5 mb-9">
            {{-- Tool 1 --}}
            <a href="#guide-grid" class="bg-white border border-line rounded-2xl p-6 flex gap-4 items-start transition hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-[0_12px_30px_rgba(20,23,25,0.07)] text-left group">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-file-search text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bn text-[15.5px] font-semibold text-ink mb-1 group-hover:text-blue-700 transition">রিপোর্ট ডিকোডার</h3>
                    <p class="font-bn text-[13px] text-slate-500 leading-[1.6]">বায়োপসি বা হিস্টোপ্যাথলজি রিপোর্টের কঠিন মেডিকেল শব্দের সহজ ও প্র্যাকটিক্যাল ব্যাখ্যা।</p>
                </div>
            </a>

            {{-- Tool 2 --}}
            <a href="{{ route('doctors.index') }}" class="bg-white border border-line rounded-2xl p-6 flex gap-4 items-start transition hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-[0_12px_30px_rgba(20,23,25,0.07)] text-left group">
                <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-calculator text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bn text-[15.5px] font-semibold text-ink mb-1 group-hover:text-amber-700 transition">খরচের হিসাব</h3>
                    <p class="font-bn text-[13px] text-slate-500 leading-[1.6]">কোন ধাপে কত খরচ — টেস্ট, সার্জারি, কেমো ও রেডিয়েশনের সরকারি-বেসরকারি তুলনামূলক হিসাব।</p>
                </div>
            </a>

            {{-- Tool 3 --}}
            <a href="{{ route('doctors.index') }}" class="bg-white border border-line rounded-2xl p-6 flex gap-4 items-start transition hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-[0_12px_30px_rgba(20,23,25,0.07)] text-left group">
                <div class="w-11 h-11 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-stethoscope text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bn text-[15.5px] font-semibold text-ink mb-1 group-hover:text-teal-700 transition">ডাক্তার ডিরেক্টরি</h3>
                    <p class="font-bn text-[13px] text-slate-500 leading-[1.6]">ক্যান্সারের ধরন অনুযায়ী বিশেষজ্ঞ অনকোলজিস্টদের তালিকা, অভিজ্ঞতা ও চেম্বারের সময়সূচি।</p>
                </div>
            </a>
        </div>

        {{-- Section Heading & Filter Tabs --}}
        <div id="guide-grid" class="flex items-end justify-between gap-6 mb-5">
            <div class="text-left">
                <h2 class="font-serif text-[27px] font-semibold tracking-[-0.016em] text-ink mb-1.5">
                    সব ক্যান্সার গাইড
                </h2>
                <p class="font-bn text-[14.5px] text-slate-500 leading-[1.68] max-w-[640px]">
                    আপনার বা আপনার স্বজনের যে ধরনের ক্যান্সার নির্ণয় হয়েছে, সেই গাইডটি বেছে নিয়ে চিকিৎসার সম্পূর্ণ রূপরেখা জানুন।
                </p>
            </div>
            <div class="font-bn text-[13px] text-slate-400 whitespace-nowrap pb-1">
                মোট <b class="text-ink font-semibold">{{ $publishedCancerTypes->count() }}টি</b> প্রকাশিত গাইড
            </div>
        </div>

        {{-- Filter Tabs (Alpine.js) --}}
        <div class="flex gap-2 mb-5 flex-wrap">
            <button
                type="button"
                @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-teal-700 border-teal-700 text-white shadow-xs' : 'bg-white border-line text-slate-600 hover:border-teal-400 hover:text-ink'"
                class="font-bn text-[13.5px] px-4 py-2 rounded-full border font-medium transition cursor-pointer"
            >
                সব ক্যান্সার ({{ $publishedCancerTypes->count() }})
            </button>
            <button
                type="button"
                @click="activeTab = 'female'"
                :class="activeTab === 'female' ? 'bg-teal-700 border-teal-700 text-white shadow-xs' : 'bg-white border-line text-slate-600 hover:border-teal-400 hover:text-ink'"
                class="font-bn text-[13.5px] px-4 py-2 rounded-full border font-medium transition cursor-pointer"
            >
                নারী সংক্রান্ত
            </button>
            <button
                type="button"
                @click="activeTab = 'male'"
                :class="activeTab === 'male' ? 'bg-teal-700 border-teal-700 text-white shadow-xs' : 'bg-white border-line text-slate-600 hover:border-teal-400 hover:text-ink'"
                class="font-bn text-[13.5px] px-4 py-2 rounded-full border font-medium transition cursor-pointer"
            >
                পুরুষ সংক্রান্ত
            </button>
            <button
                type="button"
                @click="activeTab = 'child'"
                :class="activeTab === 'child' ? 'bg-teal-700 border-teal-700 text-white shadow-xs' : 'bg-white border-line text-slate-600 hover:border-teal-400 hover:text-ink'"
                class="font-bn text-[13.5px] px-4 py-2 rounded-full border font-medium transition cursor-pointer"
            >
                শিশু ক্যান্সার
            </button>
            <button
                type="button"
                @click="activeTab = 'has_video'"
                :class="activeTab === 'has_video' ? 'bg-teal-700 border-teal-700 text-white shadow-xs' : 'bg-white border-line text-slate-600 hover:border-teal-400 hover:text-ink'"
                class="font-bn text-[13.5px] px-4 py-2 rounded-full border font-medium transition cursor-pointer"
            >
                ভিডিও গাইড আছে
            </button>
        </div>

        {{-- Guide Cards Grid --}}
        <div class="grid grid-cols-3 gap-4">
            @forelse($publishedCancerTypes as $cancerType)
                @php
                    $guide = $cancerType->guide;
                    $hasVideo = $guide && $guide->videos->isNotEmpty();
                    $genderBias = $cancerType->gender_bias ?? 'all';

                    $bgStyle = 'bg-pink-100 text-pink-700';
                    if ($cancerType->slug === 'lung-cancer') $bgStyle = 'bg-blue-100 text-blue-700';
                    elseif ($cancerType->slug === 'blood-cancer') $bgStyle = 'bg-red-100 text-red-700';
                    elseif ($cancerType->slug === 'cervical-cancer') $bgStyle = 'bg-amber-100 text-amber-700';
                    elseif ($cancerType->slug === 'oral-cancer') $bgStyle = 'bg-emerald-100 text-emerald-800';
                @endphp
                <div
                    x-show="activeTab === 'all' || (activeTab === 'female' && '{{ $genderBias }}' === 'female') || (activeTab === 'male' && '{{ $genderBias }}' === 'male') || (activeTab === 'child' && '{{ $genderBias }}' === 'child') || (activeTab === 'has_video' && {{ $hasVideo ? 'true' : 'false' }})"
                    x-cloak
                    class="bg-white border border-line rounded-[17px] p-6 flex flex-col transition hover:border-slate-300 hover:-translate-y-1 hover:shadow-[0_14px_34px_rgba(20,23,25,0.08)] group text-left"
                >
                    <div class="flex items-start gap-3.5 mb-3.5">
                        <div class="w-[46px] h-[46px] rounded-[13px] {{ $bgStyle }} flex items-center justify-center shrink-0">
                            <i class="ti ti-{{ $cancerType->icon ?: 'ribbon' }} text-2xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bn text-[18px] font-semibold text-ink leading-[1.3] mb-0.5 truncate group-hover:text-pink-600 transition">
                                {{ $cancerType->name_bn }} ক্যান্সার
                            </h3>
                            <div class="text-[12.5px] text-slate-400 font-medium">{{ $cancerType->name_en }}</div>
                        </div>

                        {{-- Tag --}}
                        @if($cancerType->is_common)
                            <span class="font-bn text-[10.5px] px-2.5 py-1 rounded-full font-semibold bg-pink-100 text-pink-800 shrink-0">
                                সবচেয়ে বেশি
                            </span>
                        @elseif($hasVideo)
                            <span class="font-bn text-[10.5px] px-2.5 py-1 rounded-full font-semibold bg-teal-100 text-teal-700 flex items-center gap-1 shrink-0">
                                <i class="ti ti-video"></i> ভিডিও
                            </span>
                        @endif
                    </div>

                    <p class="font-bn text-[13.5px] text-slate-500 leading-[1.65] mb-4 flex-1">
                        {{ $cancerType->short_description_bn ?: 'লক্ষণ, টেস্ট, বায়োপসি রিপোর্ট বোঝা এবং চিকিৎসার সম্পূর্ণ গাইড।' }}
                    </p>

                    {{-- Stats --}}
                    <div class="grid grid-cols-3 gap-2 pt-3 border-t border-line/60 mb-3.5 text-left">
                        <div>
                            <div class="font-bn text-[13.5px] font-semibold text-ink truncate">
                                {{ $guide?->reviewedByDoctor?->name_bn ?? 'অনকোলজিস্ট দল' }}
                            </div>
                            <div class="font-bn text-[11px] text-slate-400 mt-0.5">রিভিউ করেছেন</div>
                        </div>
                        <div>
                            <div class="font-bn text-[13.5px] font-semibold text-ink">
                                {{ $guide?->read_minutes ?? 5 }} মিনিট
                            </div>
                            <div class="font-bn text-[11px] text-slate-400 mt-0.5">পড়ার সময়</div>
                        </div>
                        <div>
                            <div class="font-bn text-[13.5px] font-semibold text-ink">
                                {{ $guide?->terms?->count() ?? 0 }}টি শব্দ
                            </div>
                            <div class="font-bn text-[11px] text-slate-400 mt-0.5">রিপোর্ট ডিকোডার</div>
                        </div>
                    </div>

                    <a href="{{ url('/guide/' . $cancerType->slug) }}" class="font-bn text-[13px] font-semibold text-ink group-hover:text-pink-600 flex items-center gap-1.5 mt-auto">
                        <span>সম্পূর্ণ গাইড পড়ুন</span>
                        <i class="ti ti-arrow-right text-sm transition group-hover:translate-x-1"></i>
                    </a>
                </div>
            @empty
                <div class="col-span-3 bg-white border border-line rounded-2xl p-10 text-center">
                    <i class="ti ti-search-off text-4xl text-slate-300 mb-3 inline-block"></i>
                    <h3 class="font-bn text-lg font-semibold text-ink mb-1">কোনো গাইড পাওয়া যায়নি</h3>
                    <p class="font-bn text-sm text-slate-500 mb-4">আপনার অনুসন্ধানের সাথে মেলে এমন কোনো ক্যান্সার গাইড এই মুহূর্তে নেই।</p>
                    <a href="{{ route('guides.index') }}" class="font-bn text-sm px-5 py-2.5 rounded-lg bg-teal-700 text-white hover:bg-teal-800 border border-teal-600 shadow-sm transition inline-flex items-center font-semibold">সব গাইড দেখুন</a>
                </div>
            @endforelse

            {{-- Coming Soon Card --}}
            @if($unpublishedCount > 0)
                <div class="bg-white border border-dashed border-line rounded-[17px] p-6 flex flex-col justify-center items-center text-center gap-2 text-slate-400">
                    <div class="w-10 h-10 rounded-full bg-mist flex items-center justify-center text-slate-400 mb-1">
                        <i class="ti ti-writing text-xl"></i>
                    </div>
                    <div class="font-bn text-[15px] font-semibold text-slate-600">আরও {{ $unpublishedCount }}টি ক্যান্সার গাইড আসছে</div>
                    <p class="font-bn text-[12.5px] text-slate-400 leading-[1.6]">
                        লিভার, খাদ্যনালী, থাইরয়েড, ডিম্বাশয়, মূত্রথলি সহ বাকিগুলো লেখা ও যাচাইয়ের কাজ চলছে।
                    </p>
                </div>
            @endif
        </div>

        {{-- 3. HELPLINE BAND --}}
        <div class="bg-gradient-to-r from-teal-950 via-teal-900 to-slate-900 text-white rounded-[20px] p-9 flex items-center gap-9 mt-9 text-left border border-teal-800 shadow-md">
            <div>
                <h3 class="font-serif text-[26px] font-medium leading-[1.24] mb-2 tracking-[-0.016em] text-white">
                    রিপোর্ট হাতে আছে, কিন্তু কিছুই বুঝছেন না?
                </h3>
                <p class="font-bn text-[14.5px] text-white/70 leading-[1.7] max-w-[560px]">
                    এটা স্বাভাবিক — রিপোর্ট ডাক্তারদের ভাষায় লেখা হয়। ফোন করুন, আমরা বুঝিয়ে বলব কোন শব্দের কী মানে এবং কোন ধরনের বিশেষজ্ঞ দেখাতে হবে। আমরা ডাক্তার নই, তাই চিকিৎসার পরামর্শ দিই না — শুধু সঠিক জায়গায় পৌঁছাতে সাহায্য করি।
                </p>
            </div>
            <div class="ml-auto flex gap-3 shrink-0">
                <a href="tel:09611777888" class="font-bn text-[14.5px] px-6 py-3.5 rounded-[10px] bg-white text-slate-900 font-semibold hover:bg-mist transition flex items-center gap-2">
                    <i class="ti ti-phone text-pink-600"></i> ০৯৬১১-৭৭৭৮৮৮
                </a>
                <a href="https://wa.me/8809611777888" target="_blank" class="font-bn text-[14.5px] px-6 py-3.5 rounded-[10px] bg-transparent text-white border border-white/30 font-semibold hover:bg-white/10 transition flex items-center gap-2">
                    <i class="ti ti-brand-whatsapp text-emerald-400"></i> WhatsApp করুন
                </a>
            </div>
        </div>

        {{-- 4. SOURCES & TRANSPARENCY NOTE --}}
        <div class="bg-white border border-line rounded-[14px] p-5 mt-4 flex items-start gap-3 text-left">
            <i class="ti ti-shield-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
            <div class="font-bn text-[12.5px] text-slate-500 leading-[1.7]">
                প্রতিটি গাইড WHO ও NCCN-এর প্রকাশিত নির্দেশনার ভিত্তিতে লেখা এবং বাংলাদেশে কর্মরত নিবন্ধিত অনকোলজিস্ট যাচাই করেছেন। তবু এই তথ্য চিকিৎসা পরামর্শের বিকল্প নয় — আপনার ক্ষেত্রে কী প্রযোজ্য তা একজন ডাক্তারই বলতে পারবেন।
            </div>
        </div>

    </div>
</div>
@endsection
