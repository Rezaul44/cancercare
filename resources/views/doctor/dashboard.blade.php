@extends('layouts.doctor')

@section('title', 'ড্যাশবোর্ড — ডাক্তার পোর্টাল')

@section('content')
<div class="font-serif text-[26px] font-medium mb-6"><span class="font-bn">নমস্কার, {{ $doctor->name_bn }}</span></div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
    <a href="{{ route('doctor.second-opinions.index') }}" class="block border border-line rounded-[16px] px-6 py-5 bg-white hover:border-slate-300">
        <div class="font-bn text-[13px] text-slate-500 mb-1">অপেক্ষমাণ দ্বিতীয় মতামত</div>
        <div class="font-serif text-[32px] font-medium">{{ $pendingCount }}</div>
    </a>
    <div class="border border-line rounded-[16px] px-6 py-5 bg-white">
        <div class="font-bn text-[13px] text-slate-500 mb-1">প্রোফাইলের অবস্থা</div>
        @if ($doctor->doctor_approved_at)
            <div class="font-bn text-[14.5px] font-semibold text-teal-700">আপনি অনুমোদন দিয়েছেন</div>
        @else
            <div class="font-bn text-[14.5px] font-semibold text-[#C98A1E]">আপনার অনুমোদন বাকি</div>
            <a href="{{ route('doctor.profile.edit') }}" class="font-bn text-[13px] text-slate-500 underline">প্রোফাইলে যান</a>
        @endif
        <div class="font-bn text-[12.5px] text-slate-500 mt-2">
            BMDC যাচাই: {{ $doctor->bmdc_verified_at ? 'সম্পন্ন' : 'বাকি' }}
        </div>
    </div>
</div>
@endsection
