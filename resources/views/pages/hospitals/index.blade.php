@extends('layouts.app')

@section('title', 'হাসপাতাল ও ক্যান্সার সেন্টার — কোথায় কী চিকিৎসা হয় | CancerCare Bangladesh')
@section('meta_description', 'বাংলাদেশের ক্যান্সার হাসপাতালসমূহের পূর্ণাঙ্গ তালিকা। কোন হাসপাতালে রেডিওথেরাপি, কেমোথেরাপি, সার্জারি বা বিএমটি সুবিধা আছে এবং কোনটি নেই — সম্পূর্ণ যাচাইকৃত তথ্য।')

@section('content')
<div class="bg-white border-b border-line py-9 pb-7">
    <div class="max-w-[1240px] mx-auto px-4 md:px-10">
        <div class="text-[11.5px] uppercase tracking-widest text-slate-400 font-semibold mb-2.5 font-bn">
            হাসপাতাল ও ক্যান্সার সেন্টার
        </div>
        <h1 class="font-serif text-3xl md:text-[34px] font-medium tracking-tight text-ink leading-tight mb-2.5">
            কোথায় আসলে কী চিকিৎসা হয়
        </h1>
        <p class="text-[15px] text-slate-500 leading-relaxed max-w-[700px] font-bn">
            অনেক হাসপাতাল ক্যান্সার চিকিৎসার কথা বললেও রেডিওথেরাপি বা বোন ম্যারো ট্রান্সপ্লান্টের সুবিধা সব জায়গায় নেই। এখানে প্রতিটি হাসপাতালে কী কী আছে আর কী নেই — দুটোই স্পষ্ট করে দেওয়া আছে।
        </p>
    </div>
</div>

<div class="py-8 pb-16 bg-mist min-h-[600px]"
     x-data="hospitalDirectory({
         baseUrl: '{{ route('hospitals.index') }}',
         capabilities: @js($filters['capabilities'] ?? []),
         types: @js($filters['types'] ?? []),
         divisions: @js($filters['division_ids'] ?? []),
         facilities: @js($filters['facilities'] ?? []),
         districtId: @js($filters['district_id'] ?? null),
         sort: '{{ $sort }}'
     })">
    <div class="max-w-[1240px] mx-auto px-4 md:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-[250px_1fr] gap-6 items-start">
            {{-- Filter Sidebar --}}
            @include('pages.hospitals._sidebar')

            {{-- Results Content with loading overlay --}}
            <div class="relative min-w-0">
                <div x-show="loading"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-10 rounded-2xl flex items-center justify-center min-h-[300px]"
                     style="display: none;">
                    <div class="flex items-center gap-2.5 text-sm font-medium text-slate-700 bg-white px-4 py-2 rounded-xl shadow-md border border-line">
                        <i class="ti ti-loader-2 animate-spin text-teal-600 text-lg"></i>
                        <span class="font-bn">হাসপাতাল লোড হচ্ছে...</span>
                    </div>
                </div>

                @include('pages.hospitals._results')
            </div>
        </div>
    </div>
</div>
@endsection
