{{--
    resources/views/components/stats-bar.blade.php

    Usage:
        <x-stats-bar />
        <x-stats-bar :stats="$stats" />
--}}
@php
    $stats = $stats ?? [
        ['value' => '5+', 'label' => 'PARTNER HOSPITALS'],
        ['value' => '10+', 'label' => 'VERIFIED GUIDES'],
        ['value' => '4.8/5', 'label' => 'AVERAGE RATING'],
        ['value' => '100%', 'label' => 'NON-MEDICAL SUPPORT'],
    ];
@endphp

<section class="cura-stats" aria-label="AapkaSarthi statistics">
    <div class="cura-stats-grid">
        @foreach ($stats as $stat)
            <article class="cura-stat">
                <p class="cura-stat-value">{{ $stat['value'] }}</p>
                <p class="cura-stat-label">{{ $stat['label'] }}</p>
            </article>
        @endforeach
    </div>
</section>
