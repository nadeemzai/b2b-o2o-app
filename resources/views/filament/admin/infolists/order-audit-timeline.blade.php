@php
    use App\Models\Order;

    $history = $record?->statusHistory ?? collect();

    $colorMap = [
        'pending'          => ['dot' => 'bg-yellow-400', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'payment_verified' => ['dot' => 'bg-blue-400',   'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'transferred'      => ['dot' => 'bg-indigo-500', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200', 'icon' => 'M7 16l-4-4m0 0l4-4m-4 4h18'],
        'fulfilling'       => ['dot' => 'bg-sky-500',    'badge' => 'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200',         'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
        'delivered'        => ['dot' => 'bg-green-500',  'badge' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',  'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        'cancelled'        => ['dot' => 'bg-red-500',    'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',         'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];

    $labelMap = [
        'pending'          => 'Pending',
        'payment_verified' => 'Payment Verified',
        'transferred'      => 'Transferred to Huashu',
        'fulfilling'       => 'Fulfilling',
        'delivered'        => 'Delivered',
        'cancelled'        => 'Cancelled',
    ];

    $getColor  = fn (?string $s) => $colorMap[$s] ?? ['dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
    $getLabel  = fn (?string $s) => $labelMap[$s] ?? ucwords(str_replace('_', ' ', (string)$s));
@endphp

<div class="px-4 py-4">
    @if ($history->isEmpty())
        <p class="text-sm text-gray-400 dark:text-gray-500 italic">No audit entries recorded yet.</p>
    @else
        <ol class="relative border-s border-gray-200 dark:border-gray-700 ms-3 space-y-0">
            @foreach ($history as $entry)
                @php
                    $toColor = $getColor($entry->to_status);
                    $toLabel = $getLabel($entry->to_status);
                    $fromLabel = $entry->from_status ? $getLabel($entry->from_status) : null;
                    $actor   = $entry->changedBy?->name ?? 'System';
                    $ts      = $entry->created_at ? \Carbon\Carbon::parse($entry->created_at)->format('d M Y, H:i') : '—';
                @endphp
                <li class="mb-6 ms-6">
                    {{-- Dot --}}
                    <span class="absolute flex items-center justify-center w-7 h-7 rounded-full -start-3.5 ring-4 ring-white dark:ring-gray-800 {{ $toColor['dot'] }}">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $toColor['icon'] }}"/>
                        </svg>
                    </span>

                    {{-- Content card --}}
                    <div class="p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-lg shadow-sm">
                        {{-- Top row: badge + timestamp --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2">
                                @if ($fromLabel)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $fromLabel }}</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                @endif
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $toColor['badge'] }}">
                                    {{ $toLabel }}
                                </span>
                            </div>
                            <time class="text-xs text-gray-400 dark:text-gray-500 tabular-nums shrink-0">{{ $ts }}</time>
                        </div>

                        {{-- Actor --}}
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $actor }}</span>
                        </p>

                        {{-- Note --}}
                        @if ($entry->note)
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 italic leading-relaxed">
                                &ldquo;{{ $entry->note }}&rdquo;
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
