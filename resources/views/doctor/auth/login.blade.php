@extends('layouts.doctor')

@section('title', 'ডাক্তার লগইন — CancerCare Bangladesh')

@section('content')
<div class="max-w-[420px] mx-auto bg-white border border-line rounded-[20px] px-8 py-9 mt-10">
    <div class="font-serif text-[24px] font-medium mb-1">
        <span class="font-bn">ডাক্তার পোর্টাল</span>
    </div>
    <p class="font-bn text-[13.5px] text-slate-500 mb-6">দ্বিতীয় মতামত ও প্রোফাইল ব্যবস্থাপনার জন্য লগইন করুন</p>

    @if ($errors->any())
        <div class="mb-5 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
            @foreach ($errors->all() as $message)
                <p class="font-bn text-[13px] text-red-700">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('doctor.login.store') }}">
        @csrf
        <div class="mb-4">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ইমেইল</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>
        <div class="mb-5">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পাসওয়ার্ড</label>
            <input type="password" name="password" required
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>
        <button type="submit" class="font-bn w-full text-[15px] px-7 py-3 rounded-[9px] font-semibold bg-teal-700 text-white hover:bg-teal-800 border border-teal-600 shadow-sm transition">লগইন করুন</button>
    </form>

    <a href="{{ route('doctor.password.request') }}" class="font-bn block text-center text-[13px] text-slate-500 hover:text-ink mt-5">পাসওয়ার্ড ভুলে গেছেন?</a>
</div>
@endsection
