@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 rounded-lg border border-line px-3 py-2 text-sm text-muted opacity-50" aria-disabled="true">
                <x-icon name="chevron-left" class="size-4" />
                Anterior
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 rounded-lg border border-line bg-surface-2 px-3 py-2 text-sm transition hover:border-muted">
                <x-icon name="chevron-left" class="size-4" />
                Anterior
            </a>
        @endif

        <span class="text-sm text-muted">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 rounded-lg border border-line bg-surface-2 px-3 py-2 text-sm transition hover:border-muted">
                Próxima
                <x-icon name="chevron-right" class="size-4" />
            </a>
        @else
            <span class="inline-flex items-center gap-1 rounded-lg border border-line px-3 py-2 text-sm text-muted opacity-50" aria-disabled="true">
                Próxima
                <x-icon name="chevron-right" class="size-4" />
            </span>
        @endif
    </nav>
@endif
