<x-filament-widgets::widget>
    <x-filament::section heading="সাপ্তাহিক প্রতিবেদন — গত ৭ দিনের কল বিষয়ভিত্তিক">
        @if ($rows->isEmpty())
            <p class="text-sm text-gray-500">গত ৭ দিনে কোনো কল লগ হয়নি।</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($rows as $row)
                    <div class="flex items-center justify-between py-2 text-sm">
                        <span>{{ $row['label'] }}</span>
                        <span class="font-semibold">{{ $row['total'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
