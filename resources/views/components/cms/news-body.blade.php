@props(['source' => ''])
<div {{ $attributes->merge(['class' => 'cms-news-body']) }}>
    {!! \App\Cms\CmsNewsHtml::toSafeHtmlString((string) $source) !!}
</div>
