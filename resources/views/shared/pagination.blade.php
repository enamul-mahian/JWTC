@if($paginator->hasPages())
<nav class="jg-page-nav" aria-label="Pagination"><div class="jg-page-summary">Showing {{ $paginator->firstItem()??0 }}–{{ $paginator->lastItem()??0 }}@if(method_exists($paginator,'total')) of {{ $paginator->total() }} results@endif</div><div class="jg-page-links">
@if($paginator->onFirstPage())<span aria-disabled="true">← Previous</span>@else<a rel="prev" href="{{ $paginator->previousPageUrl() }}">← Previous</a>@endif
@if(method_exists($paginator,'lastPage'))
@foreach(array_values(array_unique(array_merge([1],range(max(1,$paginator->currentPage()-2),min($paginator->lastPage(),$paginator->currentPage()+2)),[$paginator->lastPage()]))) as $page)
@if($page===$paginator->currentPage())<span class="is-current" aria-current="page">{{ $page }}</span>@else<a href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>@endif
@endforeach
@endif
@if($paginator->hasMorePages())<a rel="next" href="{{ $paginator->nextPageUrl() }}">Next →</a>@else<span aria-disabled="true">Next →</span>@endif
</div></nav>@endif
