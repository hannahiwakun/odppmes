<section class="public-page-hero">
    <div class="public-wrap">
        <p class="public-eyebrow" style="color: var(--odp-green);">{{ $kicker ?? 'Development Partners Management Office' }}</p>
        <h1>{{ $title }}</h1>
        @isset($lede)
            <p class="lede">{{ $lede }}</p>
        @endisset
    </div>
</section>
