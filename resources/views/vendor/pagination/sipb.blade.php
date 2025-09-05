@if ($paginator->hasPages())
<nav class="sipb-pagination-wrap" role="navigation" aria-label="Pagination">
  <ul class="sipb-pagination">
    {{-- First --}}
    @if ($paginator->onFirstPage())
      <li class="item disabled" aria-disabled="true" aria-label="@lang('pagination.first')">
        <span class="link" aria-hidden="true"><i class="bi bi-chevron-double-left"></i></span>
      </li>
    @else
      <li class="item">
        <a class="link" href="{{ $paginator->url(1) }}" rel="first" aria-label="@lang('pagination.first')">
          <i class="bi bi-chevron-double-left"></i>
        </a>
      </li>
    @endif

    {{-- Prev --}}
    @if ($paginator->onFirstPage())
      <li class="item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
        <span class="link" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
      </li>
    @else
      <li class="item">
        <a class="link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>
    @endif

    {{-- Numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <li class="item disabled" aria-disabled="true"><span class="link">&hellip;</span></li>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <li class="item active" aria-current="page"><span class="link">{{ $page }}</span></li>
          @else
            <li class="item"><a class="link" href="{{ $url }}">{{ $page }}</a></li>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <li class="item">
        <a class="link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>
    @else
      <li class="item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
        <span class="link" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
      </li>
    @endif

    {{-- Last --}}
    @if ($paginator->hasMorePages())
      <li class="item">
        <a class="link" href="{{ $paginator->url($paginator->lastPage()) }}" rel="last" aria-label="@lang('pagination.last')">
          <i class="bi bi-chevron-double-right"></i>
        </a>
      </li>
    @else
      <li class="item disabled" aria-disabled="true" aria-label="@lang('pagination.last')">
        <span class="link" aria-hidden="true"><i class="bi bi-chevron-double-right"></i></span>
      </li>
    @endif
  </ul>
</nav>
@endif
