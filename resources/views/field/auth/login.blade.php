@extends('layouts.field')

@section('title', 'মাঠকর্মী লগইন — CancerCare Bangladesh')

@section('content')
<div class="bg-white border border-line rounded-[20px] px-8 py-9 mt-10">
    <div class="font-serif text-[22px] font-medium mb-1">
        <span class="font-bn">মাঠকর্মী পোর্টাল</span>
    </div>
    <p class="font-bn text-[13.5px] text-slate-500 mb-6">রোগীর রেটিং জমা দিতে লগইন করুন</p>

    @if ($errors->any())
        <div class="mb-5 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
            @foreach ($errors->all() as $message)
                <p class="font-bn text-[13px] text-red-700">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('field.login.store') }}">
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
</div>
@endsection
