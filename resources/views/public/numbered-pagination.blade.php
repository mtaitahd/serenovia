@if($paginator->hasPages())
    <nav class="catalogue-pagination" aria-label="Safari result pages">
        @if(!$paginator->onFirstPage())<a href="{{ $paginator->previousPageUrl() }}">← Previous</a>@endif
        @foreach($elements as $element)
            @if(is_string($element))<span>{{ $element }}</span>@endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page === $paginator->currentPage())
                        <span aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">Next →</a>@endif
    </nav>
@endif
