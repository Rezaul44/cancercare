@extends('layouts.app')

@php
    $seoDescription = 'বাংলাদেশে ক্যান্সার চিকিৎসার সবচেয়ে নির্ভরযোগ্য প্ল্যাটফর্ম — সঠিক অনকোলজিস্ট, ভরসার হাসপাতাল ও চিকিৎসার খরচের সত্যিকারের হিসাব।';
    $seoKeywords = 'ক্যান্সার বাংলাদেশ, অনকোলজিস্ট ঢাকা, ক্যান্সার হাসপাতাল, স্তন ক্যান্সার চিকিৎসা, কেমোথেরাপি খরচ বাংলাদেশ, ক্যান্সার গাইড';
@endphp

@section('title', 'CancerCare Bangladesh — ক্যান্সার চিকিৎসায় বিশ্বস্ত পথপ্রদর্শক')

@section('content')
{{-- 1. SEARCH HERO --}}
<section class="relative bg-white pt-[60px] pb-[56px] overflow-hidden border-b border-line">
    <div class="absolute -top-[220px] -left-[120px] w-[760px] h-[520px] bg-[radial-gradient(ellipse_at_center,var(--pink-50)_0%,rgba(255,255,255,0)_70%)] pointer-events-none"></div>

    <div class="max-w-[1240px] mx-auto px-10 relative z-[2]">
        <div class="grid grid-cols-[1fr_470px] gap-14 items-center">
            <div>
                <h1 class="font-serif text-[44px] font-medium tracking-[-0.022em] leading-[1.14] mb-3.5 text-ink">
                    ক্যান্সার মানেই<br><em class="not-italic text-pink-600 font-semibold">পথ শেষ নয়</em>
                </h1>
                <p class="font-bn text-[16.5px] text-slate-500 leading-[1.62] mb-7 max-w-[520px]">
                    সবচেয়ে কঠিন সময়ে মানুষ যা খোঁজে — সঠিক ডাক্তার, ভরসার হাসপাতাল, খরচের সত্যিকারের হিসাব। সব এক জায়গায়, নিজের ভাষায়।
                </p>

                {{-- Interactive Search Box --}}
                <x-search-box />

                {{-- Suggestion Chips --}}
                <div class="flex gap-2.5 flex-wrap mt-5 max-w-[560px]">
                    <a href="{{ route('doctors.index', ['cancer' => 'breast-cancer']) }}" class="font-bn text-[13.5px] px-4 py-2 rounded-full border border-line text-slate-500 hover:border-slate-300 hover:text-ink hover:bg-mist bg-white font-medium transition">
                        স্তন ক্যান্সার
                    </a>
                    <a href="{{ route('doctors.index', ['cancer' => 'lung-cancer']) }}" class="font-bn text-[13.5px] px-4 py-2 rounded-full border border-line text-slate-500 hover:border-slate-300 hover:text-ink hover:bg-mist bg-white font-medium transition">
                        ফুসফুস ক্যান্সার
                    </a>
                    <a href="{{ route('doctors.index', ['cancer' => 'blood-cancer']) }}" class="font-bn text-[13.5px] px-4 py-2 rounded-full border border-line text-slate-500 hover:border-slate-300 hover:text-ink hover:bg-mist bg-white font-medium transition">
                        রক্তের ক্যান্সার
                    </a>
                    <a href="{{ route('doctors.index', ['cancer' => 'cervical-cancer']) }}" class="font-bn text-[13.5px] px-4 py-2 rounded-full border border-line text-slate-500 hover:border-slate-300 hover:text-ink hover:bg-mist bg-white font-medium transition">
                        জরায়ু মুখ
                    </a>
                    <a href="{{ route('doctors.index', ['district' => 'dhaka']) }}" class="font-bn text-[13.5px] px-4 py-2 rounded-full border border-line text-slate-500 hover:border-slate-300 hover:text-ink hover:bg-mist bg-white font-medium transition">
                        ঢাকা জেলা
                    </a>
                    <a href="{{ route('doctors.index') }}" class="font-bn text-[13.5px] px-4 py-2 rounded-full border border-line text-slate-500 hover:border-slate-300 hover:text-ink hover:bg-mist bg-white font-medium transition">
                        সব ডাক্তার দেখুন
                    </a>
                </div>

                {{-- Helpline banner --}}
                <div class="mt-6 flex items-center gap-2.5 font-bn text-[14px] text-slate-500">
                    <i class="ti ti-phone text-pink-600 text-[18px]"></i>
                    <span>সরাসরি কথা বলতে চান? হেল্পলাইন <b class="text-ink font-semibold">০৯৬১১-৭৭৭৮৮৮</b> (সকাল ৯টা – রাত ৯টা)</span>
                </div>
            </div>

            {{-- Hero Visual --}}
            <div class="relative">
                <div class="w-full h-[410px] rounded-[20px] overflow-hidden bg-slate-800 shadow-[0_22px_56px_rgba(20,23,25,0.15)] relative">
                    <img
                        src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80"
                        alt="ক্যান্সার চিকিৎসা সহায়তা"
                        class="w-full h-full object-cover opacity-85"
                        fetchpriority="high"
                        decoding="async"
                        width="800"
                        height="410"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                </div>

                {{-- Card 1 --}}
                <div class="absolute -bottom-6 -left-7 bg-white border border-line rounded-[15px] p-4 shadow-[0_16px_42px_rgba(20,23,25,0.14)] flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-teal-100 flex items-center justify-center shrink-0 text-teal-700">
                        <i class="ti ti-shield-check text-2xl"></i>
                    </div>
                    <div>
                        <div class="font-serif text-[22px] font-semibold leading-none text-ink">{{ $totalDoctorsCount > 0 ? $totalDoctorsCount : '৩৪২' }}</div>
                        <div class="font-bn text-[12.5px] text-slate-500 mt-1">যাচাই করা অনকোলজিস্ট</div>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div class="absolute top-6 -right-6 bg-white border border-line rounded-[14px] px-4 py-3 shadow-[0_14px_36px_rgba(20,23,25,0.13)] flex items-center gap-3">
                    <div class="w-9 h-9 rounded-[10px] bg-pink-100 flex items-center justify-center shrink-0 text-pink-700">
                        <i class="ti ti-heart-handshake text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[13.5px] font-semibold text-ink">১০০% স্বাধীন ও নিরপেক্ষ</div>
                        <div class="font-bn text-[11.5px] text-slate-400 mt-0.5">কোনো বিজ্ঞাপন বা কমিশন নেই</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 2. 4 CORE ACTION CARDS --}}
<section class="pt-2 pb-16 bg-white">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="grid grid-cols-4 gap-4">
            {{-- Doctor Directory Link --}}
            <a href="{{ route('doctors.index') }}" class="border border-line rounded-2xl p-6 bg-white flex flex-col transition hover:border-slate-300 hover:shadow-[0_12px_32px_rgba(20,23,25,0.08)] hover:-translate-y-1 group text-left">
                <div class="w-[46px] h-[46px] rounded-[13px] bg-blue-100 text-blue-700 flex items-center justify-center mb-4">
                    <i class="ti ti-stethoscope text-2xl"></i>
                </div>
                <h3 class="font-bn text-[17px] font-semibold text-ink mb-1.5 group-hover:text-pink-600 transition">ডাক্তার খুঁজুন</h3>
                <p class="font-bn text-[13.5px] text-slate-500 leading-[1.6] mb-4 flex-1">
                    ভুল বিশেষজ্ঞের কাছে গেলে সময় নষ্ট হয়। আপনার ক্যান্সারের ধরন অনুযায়ী কে উপযুক্ত, দেখে নিন।
                </p>
                <div class="font-bn text-[13px] font-semibold text-ink flex items-center gap-1.5 group-hover:text-pink-600">
                    <span>{{ $totalDoctorsCount > 0 ? $totalDoctorsCount . ' জন বিশেষজ্ঞ' : 'ডাক্তারদের তালিকা' }}</span>
                    <i class="ti ti-arrow-right text-sm transition group-hover:translate-x-1"></i>
                </div>
            </a>

            {{-- Hospital Comparison --}}
            <a href="{{ route('doctors.index') }}" class="border border-line rounded-2xl p-6 bg-white flex flex-col transition hover:border-slate-300 hover:shadow-[0_12px_32px_rgba(20,23,25,0.08)] hover:-translate-y-1 group text-left">
                <div class="w-[46px] h-[46px] rounded-[13px] bg-teal-100 text-teal-700 flex items-center justify-center mb-4">
                    <i class="ti ti-building-hospital text-2xl"></i>
                </div>
                <h3 class="font-bn text-[17px] font-semibold text-ink mb-1.5 group-hover:text-teal-700 transition">হাসপাতাল তুলনা</h3>
                <p class="font-bn text-[13.5px] text-slate-500 leading-[1.6] mb-4 flex-1">
                    সব হাসপাতালে রেডিওথেরাপি নেই। যাওয়ার আগেই জেনে নিন কোথায় কী আছে, অপেক্ষা কত দিন।
                </p>
                <div class="font-bn text-[13px] font-semibold text-ink flex items-center gap-1.5 group-hover:text-teal-700">
                    <span>৮৭টি কেন্দ্রের তথ্য</span>
                    <i class="ti ti-arrow-right text-sm transition group-hover:translate-x-1"></i>
                </div>
            </a>

            {{-- Cost Estimator --}}
            <a href="{{ route('guides.index') }}" class="border border-line rounded-2xl p-6 bg-white flex flex-col transition hover:border-slate-300 hover:shadow-[0_12px_32px_rgba(20,23,25,0.08)] hover:-translate-y-1 group text-left">
                <div class="w-[46px] h-[46px] rounded-[13px] bg-amber-100 text-amber-700 flex items-center justify-center mb-4">
                    <i class="ti ti-calculator text-2xl"></i>
                </div>
                <h3 class="font-bn text-[17px] font-semibold text-ink mb-1.5 group-hover:text-amber-700 transition">খরচের হিসাব</h3>
                <p class="font-bn text-[13.5px] text-slate-500 leading-[1.6] mb-4 flex-1">
                    মাঝপথে টাকা ফুরিয়ে যাওয়াই সবচেয়ে বড় ভয়। যাতায়াত ও থাকা সহ পুরো হিসাব আগে দেখুন।
                </p>
                <div class="font-bn text-[13px] font-semibold text-ink flex items-center gap-1.5 group-hover:text-amber-700">
                    <span>হিসাব শুরু করুন</span>
                    <i class="ti ti-arrow-right text-sm transition group-hover:translate-x-1"></i>
                </div>
            </a>

            {{-- Patient Support --}}
            <a href="{{ route('doctors.index') }}" class="border border-line rounded-2xl p-6 bg-white flex flex-col transition hover:border-slate-300 hover:shadow-[0_12px_32px_rgba(20,23,25,0.08)] hover:-translate-y-1 group text-left">
                <div class="w-[46px] h-[46px] rounded-[13px] bg-pink-100 text-pink-700 flex items-center justify-center mb-4">
                    <i class="ti ti-heart-handshake text-2xl"></i>
                </div>
                <h3 class="font-bn text-[17px] font-semibold text-ink mb-1.5 group-hover:text-pink-700 transition">রোগীদের সহায়তা</h3>
                <p class="font-bn text-[13.5px] text-slate-500 leading-[1.6] mb-4 flex-1">
                    টাকার অভাবে কারো চিকিৎসা যেন না থামে। যাচাই করা রোগীর কাছে সরাসরি পাঠান।
                </p>
                <div class="font-bn text-[13px] font-semibold text-ink flex items-center gap-1.5 group-hover:text-pink-700">
                    <span>২৪ জন অপেক্ষায়</span>
                    <i class="ti ti-arrow-right text-sm transition group-hover:translate-x-1"></i>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- 3. TRUST STRIP --}}
<section class="bg-slate-900 text-white py-[26px]">
    <div class="max-w-[1240px] mx-auto px-10 grid grid-cols-4 divide-x divide-white/15">
        <div class="pr-7 text-left">
            <div class="font-serif text-[30px] font-semibold leading-none text-white">{{ $totalDoctorsCount > 0 ? $totalDoctorsCount : '৩৪২' }}</div>
            <div class="font-bn text-[12.5px] text-white/60 mt-1.5">যাচাই করা অনকোলজিস্ট</div>
        </div>

        <div class="px-7 text-left">
            <div class="font-serif text-[30px] font-semibold leading-none text-white">৮৭</div>
            <div class="font-bn text-[12.5px] text-white/60 mt-1.5">চিকিৎসা কেন্দ্রের পূর্ণ তথ্য</div>
        </div>

        <div class="px-7 text-left">
            <div class="font-serif text-[30px] font-semibold leading-none text-white">৬৪</div>
            <div class="font-bn text-[12.5px] text-white/60 mt-1.5">জেলা থেকে খোঁজা যায়</div>
        </div>

        <div class="pl-7 text-left">
            <div class="font-serif text-[30px] font-semibold leading-none text-white">১,২৪০</div>
            <div class="font-bn text-[12.5px] text-white/60 mt-1.5">পরিবার পথ খুঁজে পেয়েছে</div>
        </div>
    </div>
</section>

{{-- 4. CANCER TYPES GRID --}}
<section class="py-16 bg-white border-b border-line">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.12em] mb-2.5">দ্রুত খুঁজুন</div>
                <h2 class="font-serif text-[33px] font-medium tracking-[-0.018em] text-ink leading-tight">কোন ক্যান্সার, কার কাছে যাবেন</h2>
            </div>
            <a href="{{ route('guides.index') }}" class="font-bn text-[14px] font-semibold text-ink hover:text-pink-600 flex items-center gap-1.5">
                <span>সব ১৮টি ধরন</span>
                <i class="ti ti-arrow-right text-sm"></i>
            </a>
        </div>

        <div class="grid grid-cols-5 gap-3">
            @foreach($commonCancerTypes as $type)
                @php
                    $bgStyle = 'bg-pink-100 text-pink-700';
                    if ($type->slug === 'lung-cancer') $bgStyle = 'bg-blue-100 text-blue-700';
                    elseif ($type->slug === 'blood-cancer') $bgStyle = 'bg-red-100 text-red-700';
                    elseif ($type->slug === 'cervical-cancer') $bgStyle = 'bg-amber-100 text-amber-700';
                    elseif ($type->slug === 'oral-cancer') $bgStyle = 'bg-emerald-100 text-emerald-800';
                @endphp
                <a href="{{ url('/guide/' . $type->slug) }}" class="bg-white border border-line rounded-[14px] p-5 transition hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-[0_8px_22px_rgba(20,23,25,0.07)] text-left group">
                    <div class="w-10 h-10 rounded-[11px] {{ $bgStyle }} flex items-center justify-center mb-3">
                        <i class="ti ti-{{ $type->icon ?: 'ribbon' }} text-xl"></i>
                    </div>
                    <div class="font-bn text-[15px] font-semibold text-ink group-hover:text-pink-600 transition">{{ $type->name_bn }} ক্যান্সার</div>
                    <div class="font-bn text-[12px] text-slate-400 mt-1">
                        {{ $type->doctors_count ?? 15 }} জন ডাক্তার
                    </div>
                </a>
            @endforeach

            {{-- More types card --}}
            <a href="{{ route('guides.index') }}" class="bg-slate-900 border border-slate-900 rounded-[14px] p-5 flex flex-col justify-center items-center text-center hover:bg-slate-800 transition text-white">
                <div class="font-bn text-[15px] font-semibold text-white">আরও ৯টি</div>
                <div class="font-bn text-[12px] text-white/60 mt-1">সব দেখুন →</div>
            </a>
        </div>
    </div>
</section>

{{-- 5. HELP & GETTING STARTED --}}
<section class="py-16 bg-mist">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="grid grid-cols-2 gap-5 items-stretch">
            {{-- Dual 1: Patient Support --}}
            <div class="bg-white border border-line rounded-[18px] p-7 flex flex-col justify-between text-left">
                <div>
                    <div class="w-11 h-11 rounded-[13px] bg-pink-100 text-pink-700 flex items-center justify-center mb-4">
                        <i class="ti ti-heart-handshake text-2xl"></i>
                    </div>
                    <h3 class="font-serif text-[23px] font-semibold text-ink mb-2.5 tracking-[-0.012em]">চিকিৎসা যেন মাঝপথে না থামে</h3>
                    <p class="font-bn text-[14px] text-slate-500 leading-[1.7] mb-4">
                        বাংলাদেশে অনেক পরিবার তিনটা কেমো করিয়ে থেমে যায় — টাকা ফুরিয়ে যায়। এই রোগীদের প্রতিটি কাগজ আমরা নিজে গিয়ে যাচাই করেছি। আপনার সাহায্য সরাসরি তাঁদের কাছে যাবে।
                    </p>

                    <div class="flex flex-col gap-2 mb-6">
                        <div class="font-bn text-[13.5px] text-slate-500 flex gap-2.5">
                            <i class="ti ti-check text-teal-600 text-base shrink-0 mt-0.5"></i>
                            <span>হাসপাতালের কাগজ, রিপোর্ট ও পরিচয় — সব মিলিয়ে দেখা</span>
                        </div>
                        <div class="font-bn text-[13.5px] text-slate-500 flex gap-2.5">
                            <i class="ti ti-check text-teal-600 text-base shrink-0 mt-0.5"></i>
                            <span>টাকা সরাসরি রোগীর কাছে — আমাদের হাত দিয়ে যায় না</span>
                        </div>
                        <div class="font-bn text-[13.5px] text-slate-500 flex gap-2.5">
                            <i class="ti ti-check text-teal-600 text-base shrink-0 mt-0.5"></i>
                            <span>এক টাকাও কমিশন নিই না</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('doctors.index') }}" class="font-bn text-sm px-6 py-3 rounded-lg font-semibold bg-pink-600 text-white hover:bg-pink-700 transition self-start inline-flex items-center gap-2">
                    <span>যাঁরা অপেক্ষা করছেন, দেখুন</span>
                </a>
            </div>

            {{-- Dual 2: Doctor Recruitment --}}
            <div class="bg-white border border-line rounded-[18px] p-7 flex flex-col justify-between text-left">
                <div>
                    <div class="w-11 h-11 rounded-[13px] bg-blue-100 text-blue-700 flex items-center justify-center mb-4">
                        <i class="ti ti-stethoscope text-2xl"></i>
                    </div>
                    <h3 class="font-serif text-[23px] font-semibold text-ink mb-2.5 tracking-[-0.012em]">আপনি কি অনকোলজিস্ট?</h3>
                    <p class="font-bn text-[14px] text-slate-500 leading-[1.7] mb-4">
                        প্রতিদিন হাজারো পরিবার খুঁজছে — কার কাছে যাব, কে ভরসা করার মতো। আপনার প্রোফাইল থাকলে দূরের জেলার রোগীও আপনাকে খুঁজে পাবেন।
                    </p>

                    <div class="flex flex-col gap-2 mb-6">
                        <div class="font-bn text-[13.5px] text-slate-500 flex gap-2.5">
                            <i class="ti ti-check text-teal-600 text-base shrink-0 mt-0.5"></i>
                            <span>তালিকাভুক্তি সম্পূর্ণ বিনামূল্যে</span>
                        </div>
                        <div class="font-bn text-[13.5px] text-slate-500 flex gap-2.5">
                            <i class="ti ti-check text-teal-600 text-base shrink-0 mt-0.5"></i>
                            <span>টাকা দিয়ে কেউ উপরে উঠতে পারেন না — কখনোই না</span>
                        </div>
                        <div class="font-bn text-[13.5px] text-slate-500 flex gap-2.5">
                            <i class="ti ti-check text-teal-600 text-base shrink-0 mt-0.5"></i>
                            <span>প্রোফাইল আমরা আপনার সাথে কথা বলে সাজিয়ে দিই</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('doctors.apply') }}" class="font-bn text-sm px-6 py-3 rounded-lg font-semibold bg-slate-900 text-white hover:bg-slate-700 transition self-start inline-flex items-center gap-2">
                    <span>আবেদন করুন</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- 6. STORIES OF HOPE (তাঁরাও একদিন ভয় পেয়েছিলেন) --}}
<section class="py-16 bg-white">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="flex items-end justify-between mb-8">
            <div>
                <div class="font-bn text-[11.5px] font-semibold text-slate-400 uppercase tracking-[0.12em] mb-2.5">সুস্থ হয়ে ওঠা</div>
                <h2 class="font-serif text-[33px] font-medium tracking-[-0.018em] text-ink leading-tight">তাঁরাও একদিন ভয় পেয়েছিলেন</h2>
            </div>
            <span class="font-bn text-[14px] font-semibold text-ink hover:text-pink-600 flex items-center gap-1.5 cursor-pointer">
                <span>সব গল্প</span>
                <i class="ti ti-arrow-right text-sm"></i>
            </span>
        </div>

        <div class="grid grid-cols-[1.4fr_1fr] gap-5">
            {{-- Big Story --}}
            <div class="rounded-[20px] overflow-hidden relative min-h-[340px] flex flex-col justify-end">
                <img src="https://images.unsplash.com/photo-1544027993-37dbfe43562a?w=900&q=80" alt="ক্যান্সার জয়ী গল্প" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover">
                <div class="relative z-[2] p-8 bg-gradient-to-t from-slate-900/95 via-slate-900/60 to-transparent text-white text-left">
                    <div class="inline-flex items-center gap-1.5 font-bn text-[11.5px] bg-white/20 px-3 py-1 rounded-full font-semibold mb-3.5">
                        <i class="ti ti-confetti text-xs"></i> ৩ বছর ক্যান্সার-মুক্ত
                    </div>
                    <div class="font-serif text-[22px] leading-[1.42] italic mb-4 font-normal">
                        "স্টেজ ৩ শুনে ভেবেছিলাম মেয়ের বিয়ে দেখে যেতে পারব না। আজ সে বিবাহিত, আর আমি তার বিয়েতে নেচেছি।"
                    </div>
                    <div class="font-bn text-[13.5px] text-white/80">
                        <b class="text-white font-semibold">মোহাম্মদ রফিক, ৪৪</b> · কোলন ক্যান্সার · রংপুর
                    </div>
                </div>
            </div>

            {{-- 2 Small Stories --}}
            <div class="flex flex-col gap-4">
                <div class="bg-mist border border-line rounded-[18px] p-6 flex-1 flex flex-col justify-between text-left">
                    <div class="font-serif text-[16px] leading-[1.55] italic text-ink mb-4">
                        "ডাক্তার যা বলতেন কিছুই বুঝতাম না। এখানে বাংলায় পড়ে বুঝলাম আমার কী হয়েছে — সেদিন থেকেই ভয়টা কমতে শুরু করল।"
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-line">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&q=80" alt="সালমা বেগম" loading="lazy" decoding="async" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <div class="font-bn text-[13.5px] font-semibold text-ink">সালমা বেগম, ৩৯</div>
                            <div class="font-bn text-[12px] text-slate-400">স্তন ক্যান্সার · ২ বছর সুস্থ</div>
                        </div>
                    </div>
                </div>

                <div class="bg-mist border border-line rounded-[18px] p-6 flex-1 flex flex-col justify-between text-left">
                    <div class="font-serif text-[16px] leading-[1.55] italic text-ink mb-4">
                        "সিলেট থেকে ঢাকা — প্রতিবার আসা-যাওয়ায় কত লাগবে জানতাম না। হিসাবটা আগে দেখেছিলাম বলে মায়ের চিকিৎসা একদিনও থামেনি।"
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-line">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80" alt="মোহাম্মদ করিম" loading="lazy" decoding="async" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <div class="font-bn text-[13.5px] font-semibold text-ink">মোহাম্মদ করিম, ৩৪</div>
                            <div class="font-bn text-[12px] text-slate-400">মায়ের যত্নকারী · সিলেট</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 7. FINANCIAL HELP CTA BANNER --}}
<section class="py-12 bg-white pb-20">
    <div class="max-w-[1240px] mx-auto px-10">
        <div class="bg-pink-50 border border-pink-200 rounded-[20px] p-10 flex items-center gap-10">
            <div class="text-left">
                <h2 class="font-serif text-[29px] font-medium text-pink-800 leading-tight mb-2.5 tracking-[-0.016em]">
                    টাকার জন্য চিকিৎসা থেমে আছে?
                </h2>
                <p class="font-bn text-[14.5px] text-pink-700 max-w-[540px] leading-[1.68]">
                    সাহায্য চাওয়া লজ্জার কিছু নয়। আপনার কাগজপত্র যাচাই করে আমরা তথ্য প্রকাশ করব, যাতে যাঁরা পাশে দাঁড়াতে চান তাঁরা সরাসরি আপনাকে খুঁজে পান। আমরা কোনো টাকা নিই না।
                </p>
            </div>

            <div class="ml-auto flex gap-3 shrink-0">
                <a href="{{ route('doctors.index') }}" class="font-bn text-[14.5px] px-6 py-3.5 rounded-[10px] font-semibold bg-pink-600 text-white hover:bg-pink-700 transition shadow-sm inline-flex items-center gap-2">
                    <span>কীভাবে আবেদন করবেন</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
