@php
    $section = $site['stakeholder_section'];
@endphp
<p class="public-kicker">{{ $kicker ?? 'Cooperation landscape' }}</p>
<h2>{{ $section['title'] }}</h2>
<p class="lede">{{ $intro ?? $section['intro'] }}</p>
<p class="lede">{{ $section['distinction'] }}</p>
<div class="public-grid public-grid-3">
    @foreach ($site['partner_categories'] as $index => $category)
        <article class="public-card">
            <span class="public-num" aria-hidden="true">{{ sprintf('%02d', $index + 1) }}</span>
            <h3>{{ $category['title'] }}</h3>
            <p>{{ $category['text'] }}</p>
            <p class="public-meta">{{ $section['empty_note'] }}</p>
        </article>
    @endforeach
</div>
