@extends('layouts.doctor')

@section('title', $request->request_code . ' — ডাক্তার পোর্টাল')

@section('content')
<a href="{{ route('doctor.second-opinions.index') }}" class="font-bn text-[13px] text-slate-500 hover:text-ink">← তালিকায় ফিরুন</a>

<div class="bg-white border border-line rounded-[16px] px-7 py-6 mt-4 mb-6">
    <div class="font-serif text-[22px] font-medium mb-3">
        <span class="font-bn">{{ $request->patient_name }}</span>
        <span class="font-bn text-[13px] text-slate-400 font-sans font-normal">· {{ $request->request_code }}</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-bn text-[13.5px] mb-5">
        <div><span class="text-slate-500">বয়স</span><br>{{ $request->age }}</div>
        <div><span class="text-slate-500">ক্যান্সারের ধরন</span><br>{{ $request->cancerType?->name_bn }}</div>
        <div><span class="text-slate-500">চিকিৎসার অবস্থা</span><br>{{ $request->current_status->labelBn() }}</div>
    </div>

    @if ($request->treatments_done_bn)
        <div class="mb-4">
            <div class="font-bn text-[12.5px] font-semibold text-slate-500 mb-1">এ পর্যন্ত যা চিকিৎসা হয়েছে</div>
            <p class="font-bn text-[14px] leading-[1.7]">{{ $request->treatments_done_bn }}</p>
        </div>
    @endif

    <div class="mb-4">
        <div class="font-bn text-[12.5px] font-semibold text-slate-500 mb-1">প্রশ্ন</div>
        <p class="font-bn text-[14px] leading-[1.7]">{{ $request->question_bn }}</p>
    </div>

    <div>
        <div class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2">রিপোর্ট</div>
        <div class="flex flex-wrap gap-2">
            @foreach ($request->files as $file)
                <a href="{{ $fileUrls[$file->id] }}" target="_blank" rel="noopener" class="font-bn inline-flex items-center gap-1.5 text-[13px] px-3 py-2 rounded-[9px] border border-line hover:border-slate-300">
                    <i class="ti ti-file text-sm"></i> {{ $file->original_name }}
                </a>
            @endforeach
        </div>
        <p class="font-bn text-[11.5px] text-[#8E979D] mt-2">লিংক ১৫ মিনিটের জন্য বৈধ — মেয়াদ শেষ হলে পাতাটি রিফ্রেশ করুন।</p>
    </div>
</div>

@if ($request->response)
    <div class="bg-teal-50 border border-[#BFE5DC] rounded-[16px] px-7 py-6">
        <div class="font-bn text-[13.5px] font-semibold text-teal-700 mb-2">আপনার উত্তর জমা হয়েছে</div>
        <p class="font-bn text-[14px] leading-[1.7] whitespace-pre-line">{{ $request->response->response_bn }}</p>
    </div>
@else
    <div class="bg-white border border-line rounded-[16px] px-7 py-6">
        <div class="font-serif text-[18px] font-medium mb-4"><span class="font-bn">উত্তর দিন</span></div>

        @if ($errors->any())
            <div class="mb-4 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
                @foreach ($errors->all() as $message)
                    <p class="font-bn text-[13px] text-red-700">{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('doctor.second-opinions.respond', $request) }}">
            @csrf
            <textarea name="response_bn" rows="6" required placeholder="আপনার লিখিত মতামত..."
                class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 mb-4">{{ old('response_bn') }}</textarea>

            <label class="flex items-center gap-2 mb-3 cursor-pointer">
                <input type="checkbox" name="call_made" value="1" @checked(old('call_made'))>
                <span class="font-bn text-[13.5px] text-slate-600">রোগীকে ফোনে কথা বলেছি</span>
            </label>

            <textarea name="call_note" rows="2" placeholder="ফোন কল সংক্রান্ত নোট (ঐচ্ছিক)"
                class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700 mb-5">{{ old('call_note') }}</textarea>

            <button type="submit" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">উত্তর জমা দিন</button>
        </form>
    </div>
@endif
@endsection
