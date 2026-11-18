@props(['paginator'])

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->hasPages())
    @php $paginator->appends(request()->query()); @endphp
    <nav {{ $attributes->class(['d-flex justify-content-center mt-4']) }} aria-label="Report list pagination">
        <ul class="pagination mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->previousPageUrl() ?: '#' }}" @if($paginator->onFirstPage()) aria-disabled="true" tabindex="-1" @endif>Previous</a>
            </li>
            <li class="page-item disabled">
                <span class="page-link">Page {{ $paginator->currentPage() }}@if(method_exists($paginator, 'lastPage')) of {{ $paginator->lastPage() }}@endif</span>
            </li>
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $paginator->nextPageUrl() ?: '#' }}" @unless($paginator->hasMorePages()) aria-disabled="true" tabindex="-1" @endunless>Next</a>
            </li>
        </ul>
    </nav>
@endif
