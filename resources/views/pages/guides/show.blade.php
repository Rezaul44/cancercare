@extends('layouts.app')

@php
    $seoDescription = $guide->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($guide->intro_bn), 160);
    $seoType = 'article';
    $seoKeywords = $cancerType->name_bn.' ক্যান্সার, লক্ষণ, চিকিৎসা, বায়োপসি রিপোর্ট, স্টেজ, '.$cancerType->name_en;
@endphp

@section('title', $guide->title_bn . ' — ক্যান্সার গাইড | CancerCare Bangladesh')

@section('content')
<div class="py-4 bg-mist" x-data="{ currentSection: 's-video' }">
    <div class="max-w-[1240px] mx-auto px-10">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 font-bn text-[12.5px] text-slate-400 mb-4 py-2">
            <a href="{{ url('/') }}" class="text-slate-500 hover:text-ink transition">হোম</a>
            <i class="ti ti-chevron-right text-[11px]"></i>
            <a href="{{ route('guides.index') }}" class="text-slate-500 hover:text-ink transition">ক্যান্সার গাইড</a>
            <i class="ti ti-chevron-right text-[11px]"></i>
            <span class="text-ink font-semibold">{{ $cancerType->name_bn }} ক্যান্সার</span>
        </div>

        {{-- 1. GUIDE HEADER --}}
        <div class="bg-white border border-line rounded-[20px] p-8 mb-4 shadow-[0_2px_12px_rgba(20,23,25,0.03)] text-left">
            <div class="flex gap-5 items-start mb-5">
                <div class="w-14 h-14 rounded-2xl bg-pink-100 text-pink-700 flex items-center justify-center shrink-0">
                    <i class="ti ti-{{ $cancerType->icon ?: 'ribbon' }} text-3xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="font-serif text-[34px] font-semibold tracking-[-0.02em] leading-[1.16] mb-2 text-ink">
                        {{ $cancerType->name_bn }} ক্যান্সার
                        <span class="font-sans text-xl font-normal text-slate-400 ml-2">({{ $cancerType->name_en }})</span>
                    </h1>
                    <p class="font-bn text-[15.5px] text-slate-500 leading-[1.7] max-w-[760px]">
                        {{ $guide->intro_bn ?: $cancerType->short_description_bn }}
                    </p>
                </div>
            </div>

            {{-- Meta Badges --}}
            <div class="flex gap-2 flex-wrap pt-4 border-t border-line font-bn text-[12px]">
                @if($guide->reviewedByDoctor)
                    <div class="px-3 py-1.5 rounded-full bg-teal-100 text-teal-800 font-semibold flex items-center gap-1.5">
                        <i class="ti ti-shield-check text-sm text-teal-700"></i>
                        <span>রিভিউ করেছেন: ডা. {{ $guide->reviewedByDoctor->name_bn }} ({{ $guide->reviewedByDoctor->current_position_bn ?? 'সার্জিক্যাল অনকোলজিস্ট' }})</span>
                    </div>
                @endif

                <div class="px-3 py-1.5 rounded-full bg-mist text-slate-600 flex items-center gap-1.5">
                    <i class="ti ti-clock text-slate-400 text-sm"></i>
                    <span>পড়ার সময়: {{ $guide->read_minutes }} মিনিট</span>
                </div>

                @if($guide->last_updated_at)
                    <div class="px-3 py-1.5 rounded-full bg-mist text-slate-600 flex items-center gap-1.5">
                        <i class="ti ti-calendar text-slate-400 text-sm"></i>
                        <span>সর্বশেষ আপডেট: {{ $guide->last_updated_at->format('M Y') }}</span>
                    </div>
                @endif

                <div class="px-3 py-1.5 rounded-full bg-mist text-slate-600 flex items-center gap-1.5">
                    <i class="ti ti-book text-slate-400 text-sm"></i>
                    <span>তথ্যসূত্র: {{ $guide->sources_note_bn ?: 'WHO ও NCCN গাইডলাইন' }}</span>
                </div>
            </div>
        </div>

        {{-- 2. MAIN LAYOUT: CONTENT + STICKY SIDEBAR --}}
        <div class="grid grid-cols-[1fr_280px] gap-6 items-start">

            {{-- LEFT: CONTENT SECTIONS --}}
            <div class="flex flex-col gap-4">

                {{-- Medical Disclaimer --}}
                <div class="bg-amber-50 border border-amber-200 rounded-[16px] p-5 flex gap-3.5 text-amber-900 text-left">
                    <i class="ti ti-alert-triangle text-amber-600 text-2xl shrink-0 mt-0.5"></i>
                    <div class="font-bn text-[14px] leading-[1.72]">
                        <b class="text-amber-950 font-semibold block mb-1 text-[15px]">এটি চিকিৎসা পরামর্শ নয়</b>
                        এই তথ্য আপনাকে বোঝার জন্য — সিদ্ধান্ত নেওয়ার জন্য নয়। আপনার ক্ষেত্রে কী প্রযোজ্য তা একজন নিবন্ধিত অনকোলজিস্টই বলতে পারবেন। <b>নিজে থেকে কোনো পরীক্ষা বা ওষুধ শুরু করবেন না।</b>
                    </div>
                </div>

                {{-- DOCTOR VIDEO SECTION --}}
                @if($guide->videos->isNotEmpty())
                    @php $video = $guide->videos->first(); @endphp
                    <div class="bg-white border border-line rounded-[18px] p-7 text-left scroll-mt-24" id="s-video">
                        <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                            ২ মিনিটে বুঝে নিন
                        </div>
                        <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-4">
                            ডাক্তার নিজে বলছেন
                        </h2>

                        <div class="border border-line rounded-[15px] overflow-hidden flex flex-row bg-white hover:border-slate-300 transition group">
                            <div class="w-[250px] h-[158px] relative shrink-0 bg-slate-800 flex items-center justify-center">
                                <img
                                    src="https://images.unsplash.com/photo-1666214280557-f1b5022eb634?w=600&q=80"
                                    alt="{{ $video->title_bn }}"
                                    loading="lazy"
                                    decoding="async"
                                    width="250"
                                    height="158"
                                    class="w-full h-full object-cover opacity-70 group-hover:scale-105 transition duration-300"
                                >
                                <a href="{{ $video->video_url }}" target="_blank" class="absolute w-[52px] h-[52px] rounded-full bg-white/95 text-slate-900 flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                                    <i class="ti ti-player-play-filled text-xl ml-0.5 text-pink-600"></i>
                                </a>
                                @if($video->duration_seconds > 0)
                                    <span class="absolute bottom-2.5 right-2.5 font-bn text-[11px] bg-black/70 text-white px-2 py-0.5 rounded">
                                        {{ gmdate('i:s', $video->duration_seconds) }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-5 flex-1 flex flex-col justify-center">
                                <h3 class="font-bn text-[17.5px] font-semibold text-ink leading-[1.4] mb-2 group-hover:text-pink-600 transition">
                                    {{ $video->title_bn }}
                                </h3>
                                <p class="font-bn text-[13.5px] text-slate-500 leading-[1.68] mb-3 line-clamp-2">
                                    {{ $video->description_bn ?: 'লক্ষণ কী, কখন ডাক্তার দেখাবেন, আর কেন দেরি করলে চিকিৎসা কঠিন হয়ে যায় — সহজ বাংলায় ২ মিনিটে।' }}
                                </p>
                                <div class="font-bn text-[12px] text-slate-400 flex items-center gap-1.5">
                                    <i class="ti ti-user text-slate-400 text-sm"></i>
                                    <span>ডা. {{ $video->doctor?->name_bn ?? $guide->reviewedByDoctor?->name_bn }} · {{ $video->doctor?->current_position_bn ?? 'সার্জিক্যাল অনকোলজিস্ট' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- REPORT DECODER SECTION --}}
                <div
                    class="bg-white border border-line rounded-[18px] p-7 text-left scroll-mt-24"
                    id="s-report"
                    x-data="{
                        search: '',
                        openId: '{{ $guide->terms->first()?->slug }}',
                        toggle(id) {
                            this.openId = this.openId === id ? null : id;
                        },
                        match(code, hint, plain, kw) {
                            if (!this.search) return true;
                            const s = this.search.toLowerCase().trim();
                            const full = (code + ' ' + hint + ' ' + plain + ' ' + (kw || '')).toLowerCase();
                            return full.includes(s);
                        }
                    }"
                >
                    <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                        রিপোর্ট বুঝুন
                    </div>
                    <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-2">
                        আপনার রিপোর্টে এই শব্দগুলো আছে?
                    </h2>
                    <p class="font-bn text-[14.5px] text-slate-500 leading-[1.72] mb-5">
                        বায়োপসি রিপোর্ট হাতে পেয়ে বেশিরভাগ মানুষ কিছুই বোঝেন না — অথচ এই শব্দগুলোই ঠিক করে দেয় চিকিৎসা কেমন হবে। যেটা খুঁজছেন সেটায় ক্লিক করুন।
                    </p>

                    {{-- Client-side Filter Input --}}
                    <div class="relative mb-4">
                        <i class="ti ti-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="রিপোর্ট থেকে শব্দ লিখুন — যেমন Grade, ER, HER2"
                            class="font-bn w-full py-3.5 pl-11 pr-4 border border-line rounded-xl text-sm text-ink bg-white focus:outline-none focus:border-slate-900 transition"
                        >
                    </div>

                    {{-- Terms Accordion List --}}
                    <div class="flex flex-col gap-2.5">
                        @forelse($guide->terms as $term)
                            <div
                                id="{{ $term->slug }}"
                                x-show="match('{{ addslashes($term->code) }}', '{{ addslashes($term->hint_bn) }}', '{{ addslashes($term->plain_explanation_bn) }}', '{{ addslashes($term->search_keywords ?? '') }}')"
                                :class="openId === '{{ $term->slug }}' ? 'border-slate-900 shadow-sm' : 'border-line'"
                                class="border rounded-xl overflow-hidden bg-white transition scroll-mt-24"
                            >
                                <div
                                    @click="toggle('{{ $term->slug }}')"
                                    class="p-4 flex items-center gap-3 cursor-pointer hover:bg-mist/70 transition"
                                >
                                    <span class="font-sans text-[14.5px] font-semibold text-ink">{{ $term->code }}</span>
                                    @if($term->hint_bn)
                                        <span class="font-bn text-[13px] text-slate-400">({{ $term->hint_bn }})</span>
                                    @endif
                                    <i
                                        class="ti ti-chevron-down ml-auto text-slate-400 text-base transition-transform duration-200"
                                        :class="openId === '{{ $term->slug }}' ? 'rotate-180 text-ink' : ''"
                                    ></i>
                                </div>

                                <div
                                    x-show="openId === '{{ $term->slug }}'"
                                    x-cloak
                                    x-transition
                                    class="p-5 pt-2 border-t border-line/60 bg-white"
                                >
                                    <div class="font-bn text-[14.5px] text-ink leading-[1.75] mb-3">
                                        {!! nl2br(e($term->plain_explanation_bn)) !!}
                                    </div>

                                    @if(!empty($term->scale) && is_array($term->scale))
                                        <div class="flex gap-2 my-3">
                                            @foreach($term->scale as $scaleItem)
                                                <div class="flex-1 p-2.5 rounded-lg text-center font-bn text-[12.5px] bg-mist border border-line">
                                                    <div class="font-semibold text-[13.5px] mb-0.5 text-ink">{{ $scaleItem['label'] ?? '' }}</div>
                                                    <span class="text-slate-500">{{ $scaleItem['desc'] ?? '' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($term->why_matters_bn)
                                        <div class="bg-mist border border-line/70 rounded-xl p-3.5 font-bn text-[13.5px] text-slate-600 leading-[1.7] flex gap-2.5 items-start mt-3">
                                            <i class="ti ti-bulb text-amber-600 text-base shrink-0 mt-0.5"></i>
                                            <span>{{ $term->why_matters_bn }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="font-bn text-sm text-slate-400 text-center py-4">কোনো মেডিকেল টার্ম যুক্ত করা হয়নি।</p>
                        @endforelse
                    </div>
                </div>

                {{-- STAGE SELECTOR SECTION --}}
                @if($guide->stages->isNotEmpty())
                    @php
                        $stagesJson = $guide->stages->map(function ($s) {
                            $costText = 'চিকিৎসা পরিকল্পনা অনুযায়ী';
                            if ($s->cost_min && $s->cost_max) {
                                $costText = '৳' . number_format($s->cost_min) . ' – ' . number_format($s->cost_max);
                            } elseif ($s->cost_min) {
                                $costText = '৳' . number_format($s->cost_min) . '+';
                            }
                            return [
                                'stage' => $s->stage,
                                'title' => $s->title_bn,
                                'desc' => $s->description_bn,
                                'treatment' => $s->typical_treatment_bn,
                                'duration' => $s->duration_bn,
                                'cost' => $costText,
                            ];
                        })->values();
                    @endphp
                    <div
                        class="bg-white border border-line rounded-[18px] p-7 text-left scroll-mt-24"
                        id="s-stage"
                        x-data="{
                            stages: {{ json_encode($stagesJson) }},
                            currentIndex: 0,
                            get current() {
                                return this.stages[this.currentIndex] || this.stages[0];
                            }
                        }"
                    >
                        <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                            স্টেজ বুঝুন
                        </div>
                        <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-2">
                            আপনার স্টেজে কী হয়
                        </h2>
                        <p class="font-bn text-[14.5px] text-slate-500 leading-[1.72] mb-5">
                            স্টেজ বলে ক্যান্সার কতদূর ছড়িয়েছে। নিচে ক্লিক করে দেখুন আপনার স্টেজে কী চিকিৎসা হয়, কতদিন লাগে ও কত খরচ হতে পারে।
                        </p>

                        {{-- 4 Stage Selector Cards --}}
                        <div class="grid grid-cols-4 gap-2.5 mb-4">
                            @foreach($guide->stages as $idx => $stg)
                                <div
                                    @click="currentIndex = {{ $idx }}"
                                    :class="currentIndex === {{ $idx }} ? 'border-slate-900 bg-mist/60 shadow-sm' : 'border-line bg-white hover:border-slate-300'"
                                    class="border rounded-xl p-4 cursor-pointer transition flex flex-col justify-between text-left {{ $idx === 0 ? 'border-slate-900 bg-mist/60 shadow-sm' : 'border-line bg-white' }}"
                                >
                                    <div>
                                        <div class="font-serif text-[18px] font-semibold text-ink mb-1">{{ $stg->stage }}</div>
                                        <div class="font-bn text-[12px] text-slate-500 line-clamp-2">{{ $stg->title_bn }}</div>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-line mt-3 overflow-hidden">
                                        <div
                                            class="h-full rounded-full transition-all {{ $idx === 0 ? 'w-1/4 bg-teal-500' : ($idx === 1 ? 'w-2/4 bg-teal-600' : ($idx === 2 ? 'w-3/4 bg-amber-500' : 'w-full bg-rose-600')) }}"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Active Stage Detail Box --}}
                        @php $firstStage = $guide->stages->first(); @endphp
                        <div class="bg-mist rounded-xl p-5 border border-line/70">
                            <h3 class="font-bn text-[15.5px] font-semibold text-ink mb-1.5" x-text="current.title">{{ $firstStage?->title_bn }}</h3>
                            <p class="font-bn text-[14px] text-slate-600 leading-[1.75] mb-4" x-text="current.desc">{{ $firstStage?->description_bn }}</p>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-white rounded-lg p-4 border border-line/60 text-left">
                                    <div class="font-bn text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">সাধারণ চিকিৎসা</div>
                                    <div class="font-bn text-[14px] font-semibold text-ink leading-snug" x-text="current.treatment">{{ $firstStage?->typical_treatment_bn }}</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-line/60 text-left">
                                    <div class="font-bn text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">আনুমানিক সময়</div>
                                    <div class="font-bn text-[14px] font-semibold text-ink leading-snug" x-text="current.duration">{{ $firstStage?->duration_bn }}</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-line/60 text-left">
                                    <div class="font-bn text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">খরচের পরিসর</div>
                                    <div class="font-bn text-[14px] font-semibold text-ink leading-snug" x-text="current.cost">
                                        @if($firstStage?->cost_min && $firstStage?->cost_max)
                                            ৳{{ number_format($firstStage->cost_min) }} – {{ number_format($firstStage->cost_max) }}
                                        @else
                                            চিকিৎসা পরিকল্পনা অনুযায়ী
                                        @endif
                                    </div>
                                    <span class="font-bn text-[11px] text-teal-700 block mt-1">সরকারি হাসপাতালে অনেক কম</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- NEXT STEPS / CHECKLIST SECTION --}}
                @if($guide->steps->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-7 text-left scroll-mt-24" id="s-next">
                        <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                            এখন কী করবেন
                        </div>
                        <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-2">
                            পরবর্তী ধাপগুলো
                        </h2>
                        <p class="font-bn text-[14.5px] text-slate-500 leading-[1.72] mb-6">
                            রিপোর্ট হাতে পাওয়ার পর সাধারণত যে ক্রমে এগোতে হয়। প্রতিটি ধাপ ডাক্তারের সাথে আলোচনা করে করবেন।
                        </p>

                        <div class="flex flex-col">
                            @foreach($guide->steps as $index => $step)
                                <div class="flex gap-4 items-start">
                                    {{-- Step Circle + Line --}}
                                    <div class="flex flex-col items-center w-8 shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bn font-semibold text-sm">
                                            {{ $step->step_no }}
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 bg-line flex-1 min-h-[30px] my-1"></div>
                                        @endif
                                    </div>

                                    {{-- Step Content --}}
                                    <div class="flex-1 pb-6 text-left">
                                        <div class="flex justify-between items-baseline gap-3 mb-1.5">
                                            <h3 class="font-bn text-[16px] font-semibold text-ink">{{ $step->title_bn }}</h3>
                                            @if($step->when_label_bn)
                                                <span class="font-bn text-[12px] px-2.5 py-0.5 rounded-full font-semibold {{ $step->urgency === 'urgent' ? 'bg-pink-100 text-pink-800' : 'bg-mist text-slate-500' }}">
                                                    {{ $step->when_label_bn }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="font-bn text-[14px] text-slate-600 leading-[1.72] mb-3">
                                            {{ $step->description_bn }}
                                        </p>

                                        @if(!empty($step->items) && is_array($step->items))
                                            <div class="flex flex-col gap-1.5 pl-1">
                                                @foreach($step->items as $item)
                                                    <div class="font-bn text-[13.5px] text-slate-600 flex gap-2.5 items-start leading-[1.6]">
                                                        <i class="ti ti-point text-slate-400 text-lg shrink-0 mt-[-2px]"></i>
                                                        <span>{{ $item }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- MYTHS VS TRUTH SECTION --}}
                @if($guide->myths->isNotEmpty())
                    <div class="bg-white border border-line rounded-[18px] p-7 text-left scroll-mt-24" id="s-myth">
                        <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                            ভুল ধারণা
                        </div>
                        <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-2">
                            যা শুনবেন, কিন্তু সত্যি নয়
                        </h2>
                        <p class="font-bn text-[14.5px] text-slate-500 leading-[1.72] mb-5">
                            বাংলাদেশে এই ভুল ধারণাগুলোর কারণে অনেকে দেরি করেন বা ভুল চিকিৎসা নেন।
                        </p>

                        <div class="flex flex-col gap-3">
                            @foreach($guide->myths as $myth)
                                <div class="border border-line rounded-xl overflow-hidden">
                                    <div class="p-3.5 px-4 bg-red-50 text-red-800 font-bn text-[14px] font-semibold flex items-start gap-2.5 border-b border-red-100">
                                        <i class="ti ti-x text-red-600 text-lg shrink-0 mt-0.5"></i>
                                        <span>"{{ $myth->myth_bn }}"</span>
                                    </div>
                                    <div class="p-4 bg-white font-bn text-[14px] text-slate-600 leading-[1.72] flex items-start gap-2.5">
                                        <i class="ti ti-check text-teal-600 text-lg shrink-0 mt-0.5"></i>
                                        <span>{{ $myth->truth_bn }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- FAQ SECTION --}}
                @if($guide->faqs->isNotEmpty())
                    <div
                        class="bg-white border border-line rounded-[18px] p-7 text-left scroll-mt-24"
                        id="s-faq"
                        x-data="{
                            openFaq: null,
                            toggleFaq(idx) {
                                this.openFaq = this.openFaq === idx ? null : idx;
                            }
                        }"
                    >
                        <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                            সাধারণ প্রশ্ন
                        </div>
                        <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-5">
                            যা সবাই জিজ্ঞেস করেন
                        </h2>

                        <div class="flex flex-col gap-2.5">
                            @foreach($guide->faqs as $fIndex => $faq)
                                <div class="border border-line rounded-xl overflow-hidden bg-white">
                                    <div
                                        @click="toggleFaq({{ $fIndex }})"
                                        class="p-4 flex items-center gap-3 cursor-pointer hover:bg-mist/70 transition"
                                    >
                                        <span class="w-6 h-6 rounded-md bg-mist text-slate-600 font-bn font-semibold text-xs flex items-center justify-center shrink-0">প</span>
                                        <span class="font-bn text-[14.5px] font-semibold text-ink flex-1">{{ $faq->question_bn }}</span>
                                        <i
                                            class="ti ti-chevron-down text-slate-400 text-base transition-transform duration-200"
                                            :class="openFaq === {{ $fIndex }} ? 'rotate-180 text-ink' : ''"
                                        ></i>
                                    </div>
                                    <div
                                        x-show="openFaq === {{ $fIndex }}"
                                        x-cloak
                                        x-transition
                                        class="p-5 pt-2 pl-12 border-t border-line/60 font-bn text-[14px] text-slate-600 leading-[1.78]"
                                    >
                                        {!! nl2br(e($faq->answer_bn)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- NEXT ACTIONS / CTA CARDS --}}
                <div class="bg-white border border-line rounded-[18px] p-7 text-left">
                    <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.09em] mb-2">
                        পরবর্তী পদক্ষেপ
                    </div>
                    <h2 class="font-serif text-[25px] font-semibold tracking-[-0.015em] text-ink mb-5">
                        এখান থেকে কোথায় যাবেন
                    </h2>

                    <div class="grid grid-cols-3 gap-3.5">
                        <a href="{{ route('doctors.index', ['cancer' => $cancerType->slug]) }}" class="border border-line rounded-xl p-5 hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-sm transition bg-white group flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center mb-3">
                                    <i class="ti ti-user-search text-xl"></i>
                                </div>
                                <div class="font-bn text-[15px] font-semibold text-ink group-hover:text-pink-600 transition mb-1">অনকোলজিস্ট খুঁজুন</div>
                                <p class="font-bn text-[12.5px] text-slate-500 leading-[1.6] mb-3">
                                    {{ $cancerType->name_bn }} ক্যান্সারে অভিজ্ঞ {{ $doctorCount > 0 ? $doctorCount . ' জন' : '' }} যাচাইকৃত অনকোলজিস্ট।
                                </p>
                            </div>
                            <div class="font-bn text-[13px] font-semibold text-pink-600 flex items-center gap-1">
                                <span>ডাক্তার দেখুন</span>
                                <i class="ti ti-arrow-right text-xs"></i>
                            </div>
                        </a>

                        <a href="{{ route('doctors.index', ['cancer' => $cancerType->slug]) }}" class="border border-line rounded-xl p-5 hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-sm transition bg-white group flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
                                    <i class="ti ti-calculator text-xl"></i>
                                </div>
                                <div class="font-bn text-[15px] font-semibold text-ink group-hover:text-amber-700 transition mb-1">খরচ হিসাব করুন</div>
                                <p class="font-bn text-[12.5px] text-slate-500 leading-[1.6] mb-3">
                                    আপনার স্টেজে সরকারি ও বেসরকারি হাসপাতালে সম্পূর্ণ চিকিৎসার খরচ।
                                </p>
                            </div>
                            <div class="font-bn text-[13px] font-semibold text-amber-700 flex items-center gap-1">
                                <span>হিসাব করুন</span>
                                <i class="ti ti-arrow-right text-xs"></i>
                            </div>
                        </a>

                        <a href="{{ route('doctors.index', ['cancer' => $cancerType->slug]) }}" class="border border-line rounded-xl p-5 hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-sm transition bg-white group flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center mb-3">
                                    <i class="ti ti-building-hospital text-xl"></i>
                                </div>
                                <div class="font-bn text-[15px] font-semibold text-ink group-hover:text-teal-700 transition mb-1">কাছের কেন্দ্র</div>
                                <p class="font-bn text-[12.5px] text-slate-500 leading-[1.6] mb-3">
                                    আপনার জেলার কাছে কোথায় কোথায় রেডিওথেরাপির সুবিধা আছে।
                                </p>
                            </div>
                            <div class="font-bn text-[13px] font-semibold text-teal-700 flex items-center gap-1">
                                <span>হাসপাতাল দেখুন</span>
                                <i class="ti ti-arrow-right text-xs"></i>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

            {{-- RIGHT: STICKY SIDEBAR (TOC + HELPLINE + OTHER CANCERS) --}}
            <div class="sticky top-24 flex flex-col gap-4">

                {{-- Table of Contents (TOC) --}}
                <div class="bg-white border border-line rounded-2xl p-5 text-left shadow-sm">
                    <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.08em] mb-3">
                        এই পাতায় রয়েছে
                    </div>
                    <div class="flex flex-col gap-1 font-bn text-[13.5px]">
                        @if($guide->videos->isNotEmpty())
                            <a
                                href="#s-video"
                                @click="currentSection = 's-video'"
                                :class="currentSection === 's-video' ? 'bg-mist text-ink font-semibold' : 'text-slate-600 hover:bg-mist hover:text-ink'"
                                class="p-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="ti ti-player-play text-base" :class="currentSection === 's-video' ? 'text-pink-600' : 'text-slate-400'"></i>
                                <span>ডাক্তারের ভিডিও</span>
                            </a>
                        @endif

                        <a
                            href="#s-report"
                            @click="currentSection = 's-report'"
                            :class="currentSection === 's-report' ? 'bg-mist text-ink font-semibold' : 'text-slate-600 hover:bg-mist hover:text-ink'"
                            class="p-2 rounded-lg transition flex items-center gap-2"
                        >
                            <i class="ti ti-file-search text-base" :class="currentSection === 's-report' ? 'text-pink-600' : 'text-slate-400'"></i>
                            <span>রিপোর্ট বুঝুন</span>
                        </a>

                        @if($guide->stages->isNotEmpty())
                            <a
                                href="#s-stage"
                                @click="currentSection = 's-stage'"
                                :class="currentSection === 's-stage' ? 'bg-mist text-ink font-semibold' : 'text-slate-600 hover:bg-mist hover:text-ink'"
                                class="p-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="ti ti-stairs text-base" :class="currentSection === 's-stage' ? 'text-pink-600' : 'text-slate-400'"></i>
                                <span>স্টেজ বুঝুন</span>
                            </a>
                        @endif

                        @if($guide->steps->isNotEmpty())
                            <a
                                href="#s-next"
                                @click="currentSection = 's-next'"
                                :class="currentSection === 's-next' ? 'bg-mist text-ink font-semibold' : 'text-slate-600 hover:bg-mist hover:text-ink'"
                                class="p-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="ti ti-list-check text-base" :class="currentSection === 's-next' ? 'text-pink-600' : 'text-slate-400'"></i>
                                <span>এখন কী করবেন</span>
                            </a>
                        @endif

                        @if($guide->myths->isNotEmpty())
                            <a
                                href="#s-myth"
                                @click="currentSection = 's-myth'"
                                :class="currentSection === 's-myth' ? 'bg-mist text-ink font-semibold' : 'text-slate-600 hover:bg-mist hover:text-ink'"
                                class="p-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="ti ti-alert-circle text-base" :class="currentSection === 's-myth' ? 'text-pink-600' : 'text-slate-400'"></i>
                                <span>ভুল ধারণা</span>
                            </a>
                        @endif

                        @if($guide->faqs->isNotEmpty())
                            <a
                                href="#s-faq"
                                @click="currentSection = 's-faq'"
                                :class="currentSection === 's-faq' ? 'bg-mist text-ink font-semibold' : 'text-slate-600 hover:bg-mist hover:text-ink'"
                                class="p-2 rounded-lg transition flex items-center gap-2"
                            >
                                <i class="ti ti-message-question text-base" :class="currentSection === 's-faq' ? 'text-pink-600' : 'text-slate-400'"></i>
                                <span>সাধারণ প্রশ্ন</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Helpline Box --}}
                <div class="bg-slate-900 text-white rounded-2xl p-5 text-left">
                    <div class="font-bn text-[15px] font-semibold mb-1.5">কিছু বুঝতে পারছেন না?</div>
                    <p class="font-bn text-[13px] text-white/70 leading-[1.65] mb-4">
                        রিপোর্ট বা চিকিৎসা নিয়ে প্রশ্ন থাকলে ফোন করুন। আমরা ডাক্তার নই, কিন্তু সঠিক জায়গায় পৌঁছাতে সাহায্য করতে পারি।
                    </p>
                    <a href="tel:09611777888" class="font-bn w-full py-2.5 rounded-lg bg-white text-slate-900 font-semibold text-center hover:bg-mist transition block">
                        <i class="ti ti-phone text-pink-600 mr-1.5"></i> ০৯৬১১-৭৭৭৮৮৮
                    </a>
                </div>

                {{-- Other Cancers List --}}
                @if($otherCancerTypes->isNotEmpty())
                    <div class="bg-white border border-line rounded-2xl p-5 text-left">
                        <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.08em] mb-3">
                            অন্যান্য ক্যান্সার
                        </div>
                        <div class="flex flex-col gap-1.5 font-bn text-[13px]">
                            @foreach($otherCancerTypes as $other)
                                @php
                                    $dotColor = 'bg-pink-600';
                                    if ($other->slug === 'lung-cancer') $dotColor = 'bg-blue-700';
                                    elseif ($other->slug === 'blood-cancer') $dotColor = 'bg-red-700';
                                    elseif ($other->slug === 'cervical-cancer') $dotColor = 'bg-amber-600';
                                    elseif ($other->slug === 'oral-cancer') $dotColor = 'bg-emerald-700';
                                @endphp
                                <a href="{{ url('/guide/' . $other->slug) }}" class="p-1.5 px-2 rounded-lg text-slate-600 hover:bg-mist hover:text-ink transition flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $dotColor }} shrink-0"></span>
                                    <span>{{ $other->name_bn }} ক্যান্সার</span>
                                </a>
                            @endforeach
                            <a href="{{ route('guides.index') }}" class="font-bn text-[12.5px] text-slate-400 hover:text-pink-600 pt-2 transition">
                                সব ১৮টি দেখুন →
                            </a>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>
</div>
@endsection
