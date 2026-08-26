{{--
    docs/prototypes/intake_flow.html-এর .disclaimer হুবহু অনুসরণ করে। গাইড ও intake উভয় জায়গায়
    বাধ্যতামূলক (CLAUDE.md নীতি ৬)। ডিফল্ট টেক্সট জেনেরিক; পাতাভেদে আলাদা কথা লাগলে স্লট দিয়ে
    ওভাররাইড করা যাবে: <x-medical-disclaimer>কাস্টম বার্তা</x-medical-disclaimer>
--}}
<div class="flex gap-[13px] bg-gold-soft border border-gold-line rounded-2xl px-[22px] py-[18px] mb-[26px]">
    <i class="ti ti-alert-triangle text-gold text-xl shrink-0 mt-0.5"></i>
    <div>
        <div class="font-bn text-[14.5px] font-semibold text-[#7A5410] mb-[5px]">এটি চিকিৎসা পরামর্শ নয়</div>
        <div class="font-bn text-[13.5px] text-[#8A6218] leading-[1.65]">
            @if ($slot->isEmpty())
                CancerCare Bangladesh কোনো রোগ নির্ণয় করে না, পরীক্ষা লেখে না, চিকিৎসাও দেয় না। এই তথ্য শুধু আপনাকে প্রস্তুত করার জন্য — চূড়ান্ত সিদ্ধান্ত সবসময় একজন নিবন্ধিত চিকিৎসকই নেবেন। <b>নিজে থেকে কোনো পরীক্ষা বা চিকিৎসা শুরু করবেন না।</b>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</div>
