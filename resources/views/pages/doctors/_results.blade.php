{{--
    AJAX swap target — এখানে কোনো Alpine directive (x-*) থাকা যাবে না। Alpine innerHTML দিয়ে বসানো
    HTML নিজে থেকে স্ক্যান করে না; বাইরের persistent Alpine component (doctorDirectory()) swap-এর পর
    plain addEventListener দিয়ে [data-sort]/[data-pagination] বাইন্ড করে।
--}}
@php
    $cancerTypeName = $filters['cancer_type_id']
        ? optional($cancerTypes->firstWhere('id', $filters['cancer_type_id']))->name_bn
        : null;
    $districtName = $filters['district_id']
        ? optional($districts->firstWhere('id', $filters['district_id']))->name_bn
        : null;
    $breadcrumb = collect([$cancerTypeName, $districtName])->filter()->implode(' · ');

    $sortOptions = [
        'relevance' => 'প্রাসঙ্গিকতা',
        'nearest' => 'নিকটতম',
        'fee' => 'কম ফি',
        'rating' => 'রেটিং',
        'wait' => 'দ্রুত সময়',
    ];
@endphp

<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div class="font-bn text-[14px] text-slate-500">
        <b class="text-ink">{{ $doctors->total() }} জন</b> ডাক্তার
        @if ($breadcrumb !== '')
            — {{ $breadcrumb }}
        @endif
    </div>
    <div class="flex gap-[7px] flex-wrap">
        @foreach ($sortOptions as $value => $label)
            <button type="submit" name="sort" value="{{ $value }}" form="filters-form" data-sort="{{ $value }}"
                class="font-bn px-4 py-2 rounded-full text-[13px] border {{ $sort === $value ? 'bg-slate-900 border-slate-900 text-white font-medium' : 'bg-white border-line text-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>

<x-ranking-notice :basis="['বিশেষত্ব', 'দূরত্ব', 'যাচাইকৃত রেটিং', 'অ্যাপয়েন্টমেন্টের সহজলভ্যতা']" />

<div class="flex flex-col gap-[13px]">
    @forelse ($doctors as $doctor)
        @include('pages.doctors._card', ['doctor' => $doctor])
    @empty
        <div class="font-bn bg-white border border-line rounded-2xl p-10 text-center text-slate-500">
            এই ফিল্টারে কোনো ডাক্তার পাওয়া যায়নি। ফিল্টার কমিয়ে আবার চেষ্টা করুন।
        </div>
    @endforelse
</div>

<div data-pagination class="mt-5 font-bn">
    {{ $doctors->links() }}
</div>

<div class="font-bn flex items-start gap-2 text-[12.5px] text-slate-500 leading-[1.6] mt-[18px] p-[14px_16px] bg-white border border-line rounded-xl">
    <i class="ti ti-refresh text-[15px] mt-0.5"></i>
    <span>সমান যোগ্যতার ডাক্তারদের ক্রম নিয়মিত ঘোরানো হয়, যাতে প্রত্যেকে সমান দৃশ্যমানতা পান। {{ $doctors->total() }} জনের মধ্যে {{ $doctors->count() }} জন দেখানো হচ্ছে।</span>
</div>
