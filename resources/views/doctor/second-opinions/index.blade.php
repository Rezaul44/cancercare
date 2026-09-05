@extends('layouts.doctor')

@section('title', 'দ্বিতীয় মতামতের অনুরোধ — ডাক্তার পোর্টাল')

@section('content')
<div class="font-serif text-[24px] font-medium mb-6"><span class="font-bn">দ্বিতীয় মতামতের অনুরোধ</span></div>

<div class="space-y-3">
    @forelse ($requests as $request)
        <a href="{{ route('doctor.second-opinions.show', $request) }}" class="flex items-center justify-between gap-4 border border-line rounded-[14px] px-5 py-4 bg-white hover:border-slate-300">
            <div>
                <div class="font-bn text-[14.5px] font-semibold text-ink">{{ $request->patient_name }} <span class="text-slate-400">· {{ $request->request_code }}</span></div>
                <div class="font-bn text-[12.5px] text-slate-500 mt-0.5">{{ $request->cancerType?->name_bn }} · {{ $request->district?->name_bn }}</div>
            </div>
            <span class="font-bn text-[12px] font-semibold px-3 py-1.5 rounded-full
                @class([
                    'bg-teal-100 text-teal-700' => $request->status->value === 'answered',
                    'bg-[#FDF4E3] text-[#8A6416]' => in_array($request->status->value, ['submitted', 'accepted']),
                ])">{{ $request->status->labelBn() }}</span>
        </a>
    @empty
        <p class="font-bn text-sm text-slate-500">এখনো কোনো অনুরোধ আসেনি।</p>
    @endforelse
</div>

<div class="mt-6">{{ $requests->links() }}</div>
@endsection
