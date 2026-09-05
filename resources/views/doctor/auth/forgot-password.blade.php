@extends('layouts.doctor')

@section('title', 'পাসওয়ার্ড রিসেট — CancerCare Bangladesh')

@section('content')
<div class="max-w-[420px] mx-auto bg-white border border-line rounded-[20px] px-8 py-9 mt-10">
    <div class="font-serif text-[24px] font-medium mb-1">
        <span class="font-bn">পাসওয়ার্ড রিসেট</span>
    </div>
    <p class="font-bn text-[13.5px] text-slate-500 mb-6">আপনার ইমেইলে পাসওয়ার্ড সেট করার লিংক পাঠানো হবে।</p>

    @if ($errors->any())
        <div class="mb-5 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
            @foreach ($errors->all() as $message)
                <p class="font-bn text-[13px] text-red-700">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('doctor.password.email') }}">
        @csrf
        <div class="mb-5">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ইমেইল</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>
        <button type="submit" class="font-bn w-full text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">লিংক পাঠান</button>
    </form>

    <a href="{{ route('doctor.login') }}" class="font-bn block text-center text-[13px] text-slate-500 hover:text-ink mt-5">লগইনে ফিরে যান</a>
</div>
@endsection
