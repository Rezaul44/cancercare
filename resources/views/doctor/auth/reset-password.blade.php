@extends('layouts.doctor')

@section('title', 'নতুন পাসওয়ার্ড সেট করুন — CancerCare Bangladesh')

@section('content')
<div class="max-w-[420px] mx-auto bg-white border border-line rounded-[20px] px-8 py-9 mt-10">
    <div class="font-serif text-[24px] font-medium mb-1">
        <span class="font-bn">নতুন পাসওয়ার্ড সেট করুন</span>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
            @foreach ($errors->all() as $message)
                <p class="font-bn text-[13px] text-red-700">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('doctor.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-4">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ইমেইল</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>
        <div class="mb-4">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">নতুন পাসওয়ার্ড</label>
            <input type="password" name="password" required minlength="8"
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>
        <div class="mb-5">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">পাসওয়ার্ড আবার লিখুন</label>
            <input type="password" name="password_confirmation" required minlength="8"
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>
        <button type="submit" class="font-bn w-full text-[15px] px-7 py-3 rounded-[9px] font-semibold bg-teal-700 text-white hover:bg-teal-800 border border-teal-600 shadow-sm transition">পাসওয়ার্ড সেট করুন</button>
    </form>
</div>
@endsection
