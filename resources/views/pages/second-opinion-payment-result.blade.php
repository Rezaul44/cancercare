@extends('layouts.app')

@section('title', 'পেমেন্টের ফলাফল — CancerCare Bangladesh')

@section('content')

@php
    $config = [
        'success' => ['icon' => 'ti-circle-check', 'color' => 'teal', 'title' => 'পেমেন্ট সফল হয়েছে', 'body' => 'আপনার দ্বিতীয় মতামতের অনুরোধ জমা হয়েছে। ডাক্তার নির্ধারিত সময়ের মধ্যে উত্তর দেবেন।'],
        'failed' => ['icon' => 'ti-alert-circle', 'color' => 'red', 'title' => 'পেমেন্ট ব্যর্থ হয়েছে', 'body' => 'আপনার টাকা কাটা হয়নি। আবার চেষ্টা করতে পারেন — আপনার অনুরোধ এখনো সংরক্ষিত আছে।'],
        'cancelled' => ['icon' => 'ti-x', 'color' => 'slate', 'title' => 'পেমেন্ট বাতিল করা হয়েছে', 'body' => 'আপনি পেমেন্ট প্রক্রিয়া বাতিল করেছেন। চাইলে আবার চেষ্টা করতে পারেন।'],
    ][$outcome];
@endphp

<div class="bg-white border-b border-line py-16">
    <div class="max-w-[560px] mx-auto px-6 text-center">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-700">
            <i class="ti {{ $config['icon'] }} text-3xl"></i>
        </div>
        <h1 class="font-serif text-[28px] font-medium leading-[1.2] mb-3">
            <span class="font-bn">{{ $config['title'] }}</span>
        </h1>
        <p class="font-bn text-[15px] text-slate-500 leading-[1.7] mb-8">{{ $config['body'] }}</p>

        <div class="flex items-center justify-center gap-3">
            @if ($outcome !== 'success')
                <a href="{{ route('second-opinion.request') }}" class="font-bn inline-block text-sm px-6 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">
                    আবার চেষ্টা করুন
                </a>
            @endif
            <a href="{{ url('/') }}" class="font-bn inline-block text-sm px-6 py-3 rounded-[9px] font-medium border border-line text-slate-700 hover:bg-mist">
                হোমপেজে ফিরে যান
            </a>
        </div>
    </div>
</div>
@endsection
