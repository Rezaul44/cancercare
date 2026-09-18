{{--
    docs/prototypes/doctor_directory.html-এর "ডাক্তার খুঁজছি" (flow2) অংশ অনুসরণ করে।
    ফর্মটাই আসল কাজের ইউনিট (GET, method="GET") — JS বন্ধ থাকলেও পুরোপুরি কাজ করে।
    Alpine শুধু submit/click ধরে axios দিয়ে পাল্টায় আর history.pushState() করে।
--}}
@extends('layouts.app')

@php
    $seoDescription = 'বাংলাদেশে যাচাইকৃত অনকোলজিস্ট ডাক্তারদের তালিকা। ক্যান্সারের ধরন ও জেলা অনুযায়ী বিশেষজ্ঞ খুঁজুন।';
    $seoKeywords = 'অনকোলজিস্ট, ক্যান্সার ডাক্তার বাংলাদেশ, ক্যান্সার বিশেষজ্ঞ ঢাকা, সার্জিক্যাল অনকোলজিস্ট, মেডিকেল অনকোলজিস্ট';
@endphp

@section('title', 'ডাক্তার খুঁজুন — যাচাই করা অনকোলজিস্ট | CancerCare Bangladesh')

@section('content')

<div x-data="doctorDirectory()" x-ref="root">
    <div class="bg-white border-b border-line py-9">
        <div class="max-w-[1240px] mx-auto px-10">
            <div class="font-bn text-[13px] font-semibold text-teal-700 mb-2">ডাক্তার খুঁজুন</div>
            <h1 class="font-serif text-[32px] font-medium leading-[1.2] mb-2.5">যাচাই করা অনকোলজিস্ট</h1>
            <p class="font-bn text-[15px] text-slate-500 leading-[1.7] max-w-[640px]">
                দুটো তথ্য দিলেই আপনার জন্য প্রাসঙ্গিক ডাক্তার দেখাব। সব ডাক্তারের BMDC নিবন্ধন ও ডিগ্রি আমরা যাচাই করেছি।
            </p>

            <form id="filters-form" x-ref="form" method="GET" action="{{ route('doctors.index') }}"
                @submit.prevent="submitFilters($event)"
                @change="handleFormChange()">
                <input type="hidden" name="sort" x-ref="sortInput" value="{{ $sort }}">
                <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">

                @if (!empty($filters['q']))
                    <div class="mt-4 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-pink-700 font-bn text-[13.5px]">
                            <i class="ti ti-search text-xs"></i>
                            <span>"{{ $filters['q'] }}"-এর ফলাফল</span>
                            <a href="{{ route('doctors.index', collect(request()->query())->except('q')->all()) }}" class="ml-1 text-pink-500 hover:text-pink-800" title="অনুসন্ধান মুছুন">
                                <i class="ti ti-x text-xs"></i>
                            </a>
                        </span>
                    </div>
                @endif

                <div class="grid grid-cols-[1fr_1fr_auto] gap-3 mt-6 max-w-[820px] items-end">
                    <div>
                        <label class="font-bn block text-[12.5px] font-medium text-slate-500 mb-1.5">ক্যান্সারের ধরন</label>
                        <select name="cancer" class="font-bn w-full border border-line rounded-lg px-4 py-3 text-[14px] text-ink bg-white focus:outline-none focus:border-slate-900">
                            <option value="">এখনো জানি না</option>
                            @foreach ($cancerTypes as $cancerType)
                                <option value="{{ $cancerType->slug }}" @selected($filters['cancer_slug'] === $cancerType->slug)>{{ $cancerType->name_bn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-bn block text-[12.5px] font-medium text-slate-500 mb-1.5">জেলা</label>
                        <select name="district" class="font-bn w-full border border-line rounded-lg px-4 py-3 text-[14px] text-ink bg-white focus:outline-none focus:border-slate-900">
                            <option value="">সব জেলা</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->slug }}" @selected($filters['district_slug'] === $district->slug)>{{ $district->name_bn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="font-bn px-[26px] py-3 rounded-[10px] font-medium bg-slate-900 text-white hover:bg-slate-700 whitespace-nowrap">ডাক্তার দেখুন</button>
                </div>
                <a href="{{ route('doctors.index') }}" @click.prevent="clearFilters()" class="font-bn block mt-3 text-[13.5px] text-slate-500 underline">
                    অথবা ফিল্টার ছাড়াই সব {{ $totalPublishedDoctors }} জন ডাক্তার দেখুন →
                </a>

                <div class="grid grid-cols-[250px_1fr] gap-[26px] items-start mt-8">
                    @include('pages.doctors._sidebar')

                    <div x-ref="results" :class="{ 'opacity-50 pointer-events-none transition-opacity duration-200': loading }">
                        @include('pages.doctors._results')
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
