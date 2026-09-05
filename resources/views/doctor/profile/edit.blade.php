@extends('layouts.doctor')

@section('title', 'প্রোফাইল — ডাক্তার পোর্টাল')

@section('content')
<div class="font-serif text-[24px] font-medium mb-2"><span class="font-bn">প্রোফাইল</span></div>
<p class="font-bn text-[13.5px] text-slate-500 mb-6">
    এখান থেকে শুধু ফি ও সময়সূচি বদলাতে পারবেন। আপনার সেবা, চিকিৎসা দর্শন ও রোগীর সংখ্যা CCB লেখে —
    কোনো পরিবর্তন চাইলে CCB-কে ফোনে জানান।
</p>

@if (! $doctor->doctor_approved_at)
    <div class="flex items-center justify-between gap-4 bg-[#FDF4E3] border border-[#F0DFBC] rounded-[13px] px-5 py-4 mb-6">
        <div class="font-bn text-[13.5px] text-[#8A6416]">আপনার প্রোফাইল এখনো অনুমোদন করেননি — অনুমোদন না দেওয়া পর্যন্ত এটি প্রকাশ করা হবে না।</div>
        <form method="POST" action="{{ route('doctor.profile.approve') }}">
            @csrf
            <button type="submit" class="font-bn text-[13px] px-4 py-2 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700 whitespace-nowrap">প্রোফাইল অনুমোদন করুন</button>
        </form>
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
        @foreach ($errors->all() as $message)
            <p class="font-bn text-[13px] text-red-700">{{ $message }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('doctor.profile.update') }}">
    @csrf
    @method('PUT')

    <div class="bg-white border border-line rounded-[16px] px-7 py-6 mb-5">
        <div class="font-serif text-[18px] font-medium mb-4"><span class="font-bn">সেবার ফি</span></div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">দ্বিতীয় মতামতের ফি (৳)</label>
                <input type="number" min="0" name="second_opinion_fee" value="{{ old('second_opinion_fee', $doctor->second_opinion_fee) }}"
                    class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
            </div>
            <div>
                <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">WhatsApp পরামর্শের ফি (৳)</label>
                <input type="number" min="0" name="whatsapp_fee" value="{{ old('whatsapp_fee', $doctor->whatsapp_fee) }}"
                    class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
            </div>
            <div>
                <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">WhatsApp উত্তরের সময়</label>
                <input type="text" name="whatsapp_response_hours" value="{{ old('whatsapp_response_hours', $doctor->whatsapp_response_hours) }}" placeholder="যেমন: ২৪ ঘণ্টার মধ্যে"
                    class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
            </div>
        </div>
    </div>

    <div class="bg-white border border-line rounded-[16px] px-7 py-6 mb-5">
        <div class="font-serif text-[18px] font-medium mb-4"><span class="font-bn">চেম্বারের ফি ও সময়সূচি</span></div>
        @forelse ($doctor->chambers as $chamber)
            <div class="border border-line rounded-[13px] px-5 py-4 mb-3">
                <div class="font-bn text-[13.5px] font-semibold mb-3">{{ $chamber->name_bn }} <span class="text-slate-400 font-normal">— {{ $chamber->address_bn }}</span></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ভিজিট ফি (৳)</label>
                        <input type="number" min="0" name="chambers[{{ $chamber->id }}][fee]" value="{{ old('chambers.'.$chamber->id.'.fee', $chamber->fee) }}"
                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                    </div>
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">যেসব দিন ও সময়</label>
                        <input type="text" name="chambers[{{ $chamber->id }}][days_bn]" value="{{ old('chambers.'.$chamber->id.'.days_bn', $chamber->days_bn) }}"
                            class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">শুরুর সময়</label>
                        <input type="time" name="chambers[{{ $chamber->id }}][time_from]" value="{{ old('chambers.'.$chamber->id.'.time_from', $chamber->time_from) }}"
                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                    </div>
                    <div>
                        <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">শেষের সময়</label>
                        <input type="time" name="chambers[{{ $chamber->id }}][time_to]" value="{{ old('chambers.'.$chamber->id.'.time_to', $chamber->time_to) }}"
                            class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
                    </div>
                </div>
            </div>
        @empty
            <p class="font-bn text-sm text-slate-500">কোনো চেম্বার যোগ করা নেই।</p>
        @endforelse
    </div>

    <button type="submit" class="font-bn text-[15px] px-7 py-3 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">সংরক্ষণ করুন</button>
</form>
@endsection
