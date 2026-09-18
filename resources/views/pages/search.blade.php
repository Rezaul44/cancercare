@extends('layouts.app')

@section('title', ($query !== '' ? $query . ' — অনুসন্ধান ফলাফল' : 'সার্চ') . ' — CancerCare Bangladesh')

@section('content')

@php
    $groupMeta = [
        'doctors' => ['label' => 'ডাক্তার', 'icon' => 'ti-stethoscope', 'bg' => '#E3EEF9', 'fg' => '#1C5E9E'],
        'hospitals' => ['label' => 'হাসপাতাল', 'icon' => 'ti-building-hospital', 'bg' => '#DCF2ED', 'fg' => '#0B6E5C'],
        'guides' => ['label' => 'ক্যান্সার গাইড', 'icon' => 'ti-book-2', 'bg' => '#EFEEFC', 'fg' => '#5B4FB5'],
        'patient_cases' => ['label' => 'রোগীর সহায়তা', 'icon' => 'ti-heart-handshake', 'bg' => '#FFE1EC', 'fg' => '#DE0159'],
    ];
@endphp

<div class="bg-white border-b border-line pt-10 pb-8">
    <div class="max-w-[900px] mx-auto px-6">
        <form method="GET" action="{{ route('search.index') }}" class="relative">
            <i class="ti ti-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 text-[20px] pointer-events-none"></i>
            <input type="text" name="q" value="{{ $query }}" placeholder="যা জানতে চান লিখুন — ডাক্তার, হাসপাতাল, গাইড..."
                class="font-bn w-full py-4 pl-14 pr-5 border-2 border-line rounded-2xl text-[16px] text-ink bg-white focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 transition">
        </form>
    </div>
</div>

<div class="max-w-[900px] mx-auto px-6 py-8">
    @if ($query === '')
        <p class="font-bn text-sm text-slate-500">কী খুঁজছেন লিখুন — ডাক্তার, হাসপাতাল, গাইড বা রোগীর সহায়তা সংক্রান্ত তথ্য।</p>
    @elseif (empty($results['all']))
        <div class="border border-line rounded-[16px] px-7 py-10 text-center">
            <div class="font-serif text-[20px] font-medium mb-2">
                <span class="font-bn">"{{ $query }}"-এর জন্য কোনো ফলাফল পাওয়া যায়নি</span>
            </div>
            <p class="font-bn text-[14px] text-slate-500 mb-5">অন্য শব্দ দিয়ে চেষ্টা করুন, অথবা সরাসরি আমাদের সাথে কথা বলুন।</p>
            <div class="inline-flex items-center gap-2.5 font-bn text-[14px] text-slate-700 bg-mist rounded-full px-5 py-3">
                <i class="ti ti-phone-call text-pink-600 text-[18px]"></i>
                <span>হেল্পলাইন <b class="text-ink font-semibold">০৯৬১১-৭৭৭৮৮৮</b> — আমরা শুনব</span>
            </div>
        </div>
    @else
        <div x-data="{ tab: 'all' }">
            <div class="flex items-center gap-2 flex-wrap border-b border-line mb-6 pb-px">
                <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'border-teal-700 text-teal-800' : 'border-transparent text-slate-500 hover:text-ink'" class="font-bn text-[13.5px] font-semibold px-4 py-3 border-b-2 -mb-px transition">
                    সব ({{ count($results['all']) }})
                </button>
                @foreach ($groupMeta as $key => $meta)
                    <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-teal-700 text-teal-800' : 'border-transparent text-slate-500 hover:text-ink'" class="font-bn text-[13.5px] font-semibold px-4 py-3 border-b-2 -mb-px transition">
                        {{ $meta['label'] }} ({{ count($results[$key]) }})
                    </button>
                @endforeach
            </div>

            <div x-show="tab === 'all'" class="space-y-2">
                @foreach ($results['all'] as $item)
                    @php
                        $meta = $groupMeta[match ($item['type']) {
                            'guide_term', 'guide' => 'guides',
                            'doctor' => 'doctors',
                            'hospital' => 'hospitals',
                            'patient_case' => 'patient_cases',
                        }];
                    @endphp
                    <a href="{{ $item['url'] }}" class="flex items-start gap-4 border border-line rounded-[14px] px-5 py-4 hover:border-slate-300">
                        <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center shrink-0" style="background:{{ $meta['bg'] }}">
                            <i class="ti {{ $meta['icon'] }} text-lg" style="color:{{ $meta['fg'] }}"></i>
                        </div>
                        <div>
                            <div class="font-bn text-[10.5px] font-semibold text-[#8E979D] uppercase tracking-[0.07em] mb-1">{{ $meta['label'] }}</div>
                            <div class="font-bn text-[15px] font-semibold text-ink">{{ $item['title'] }}</div>
                            <div class="font-bn text-[13px] text-slate-500 mt-0.5">{{ $item['excerpt'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>

            @foreach ($groupMeta as $key => $meta)
                <div x-show="tab === '{{ $key }}'" class="space-y-2">
                    @forelse ($results[$key] as $item)
                        <a href="{{ $item['url'] }}" class="flex items-start gap-4 border border-line rounded-[14px] px-5 py-4 hover:border-slate-300">
                            <div class="w-[38px] h-[38px] rounded-[10px] flex items-center justify-center shrink-0" style="background:{{ $meta['bg'] }}">
                                <i class="ti {{ $meta['icon'] }} text-lg" style="color:{{ $meta['fg'] }}"></i>
                            </div>
                            <div>
                                <div class="font-bn text-[15px] font-semibold text-ink">{{ $item['title'] }}</div>
                                <div class="font-bn text-[13px] text-slate-500 mt-0.5">{{ $item['excerpt'] }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="font-bn text-sm text-slate-500 py-6 text-center">এই বিভাগে কোনো ফলাফল নেই।</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
