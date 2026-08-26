{{--
    docs/prototypes/doctor_directory.html-এর .trust-strip হুবহু অনুসরণ করে। ডাক্তার ও হাসপাতালের
    তালিকায় বাধ্যতামূলক (CLAUDE.md নীতি ১)। ব্যবহার:
    <x-ranking-notice :basis="['বিশেষত্ব', 'দূরত্ব', 'রোগীর মতামত']" />
--}}
@props(['basis' => []])

@php
    $basisText = match (true) {
        count($basis) === 0 => null,
        count($basis) === 1 => $basis[0],
        default => implode(', ', array_slice($basis, 0, -1)).' ও '.end($basis),
    };
@endphp

<div class="flex items-center gap-[11px] bg-white border border-line rounded-[13px] px-[18px] py-[14px] mb-4">
    <i class="ti ti-scale text-slate-500 text-[19px] shrink-0"></i>
    <div class="font-bn text-[13px] text-slate-500 leading-[1.6] flex-1">
        ক্রম নির্ধারিত হয়
        @if ($basisText)
            <b class="text-ink font-medium">{{ $basisText }}</b> দিয়ে।
        @endif
        কোনো ডাক্তার বা হাসপাতাল টাকা দিয়ে উপরে আসতে পারে না, আর CCB কাউকে সুপারিশ করে না।
    </div>
    <span class="font-bn text-[12.5px] text-ink underline cursor-pointer whitespace-nowrap">পদ্ধতি দেখুন</span>
</div>
