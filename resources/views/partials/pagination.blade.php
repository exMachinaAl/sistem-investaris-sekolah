@if($paginator->hasPages())
<div class="pagination"><span>Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</span><div class="pager">
@if($paginator->onFirstPage())<span class="off">‹</span>@else<a href="{{ $paginator->previousPageUrl() }}" aria-label="Sebelumnya">‹</a>@endif
@foreach($elements as $element)
@if(is_string($element))<span class="off">…</span>@else
@foreach($element as $page=>$url)
@if($page==$paginator->currentPage())<span class="current">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
@endforeach @endif
@endforeach
@if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" aria-label="Berikutnya">›</a>@else<span class="off">›</span>@endif
</div></div>
@endif
