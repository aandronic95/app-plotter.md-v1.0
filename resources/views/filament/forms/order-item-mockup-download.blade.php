@php
    $url = $get('mockup_url');
    $filename = $get('mockup_filename') ?? '';
@endphp

@if($url && $filename)
    <a href="{{ $url }}" download="{{ e($filename) }}" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-primary-600 underline ring-1 ring-inset ring-primary-600/20 hover:bg-primary-50 dark:text-primary-400 dark:ring-primary-400/30 dark:hover:bg-primary-400/10">
        {{ e($filename) }} ↓ Descarcă
    </a>
@elseif($filename)
    <p class="text-sm text-gray-600 dark:text-gray-400">{{ e($filename) }}</p>
@endif
