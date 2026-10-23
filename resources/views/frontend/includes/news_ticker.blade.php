@php($newsTicker=app(\App\Services\SiteNotice::class)->current())
@if($newsTicker?->enabled && count($newsTicker->items))
<section class="jg-ticker" data-news-ticker data-speed="{{ max(15,min(160,(int)$newsTicker->speed)) }}" aria-label="Latest updates">
<div class="jg-ticker-inner"><span class="jg-ticker-label">Latest News</span><div class="jg-ticker-window"><div class="jg-ticker-belt"><div class="jg-ticker-items">
@foreach($newsTicker->items as $item)
@if(!empty($item['url']) && \App\Services\SiteNotice::safeUrl($item['url']))<a class="jg-ticker-item" href="{{ $item['url'] }}">{{ $item['title'] }}</a>@else<span class="jg-ticker-item">{{ $item['title'] }}</span>@endif
@endforeach
</div></div></div><button class="jg-ticker-pause" type="button" aria-label="Pause news ticker" aria-pressed="false">Pause</button></div>
</section>
@endif
