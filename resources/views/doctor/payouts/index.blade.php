@extends('layouts.doctor')

@section('title', 'পে-আউট — ডাক্তার পোর্টাল')

@section('content')
<div class="font-serif text-[24px] font-medium mb-6"><span class="font-bn">পে-আউটের হিসাব</span></div>

<div class="bg-white border border-line rounded-[16px] overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-mist">
            <tr class="font-bn text-[12.5px] text-slate-500">
                <th class="px-5 py-3">সময়কাল</th>
                <th class="px-5 py-3">অনুরোধ সংখ্যা</th>
                <th class="px-5 py-3">মোট আয়</th>
                <th class="px-5 py-3">গেটওয়ে চার্জ</th>
                <th class="px-5 py-3">নীট পরিমাণ</th>
                <th class="px-5 py-3">অবস্থা</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payouts as $payout)
                <tr class="font-bn text-[13.5px] border-t border-line">
                    <td class="px-5 py-3.5">{{ $payout->period_start->format('d M') }} – {{ $payout->period_end->format('d M, Y') }}</td>
                    <td class="px-5 py-3.5">{{ $payout->request_count }}</td>
                    <td class="px-5 py-3.5">৳{{ number_format($payout->gross_amount) }}</td>
                    <td class="px-5 py-3.5 text-slate-500">−৳{{ number_format($payout->gateway_fee) }}</td>
                    <td class="px-5 py-3.5 font-semibold">৳{{ number_format($payout->net_amount) }}</td>
                    <td class="px-5 py-3.5">{{ $payout->status->labelBn() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="font-bn px-5 py-6 text-center text-slate-500">এখনো কোনো পে-আউট তৈরি হয়নি।</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $payouts->links() }}</div>
@endsection
