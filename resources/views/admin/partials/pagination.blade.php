@if ($paginator->hasPages())
    <div class="pager">
        <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        <div>@if ($paginator->onFirstPage())<span class="disabled">← Previous</span>@else<a href="{{ $paginator->previousPageUrl() }}">← Previous</a>@endif
        @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">Next →</a>@else<span class="disabled">Next →</span>@endif</div>
    </div>
@endif
