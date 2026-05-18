@if ($paginator->hasPages())
    <nav class="eservices-pagination" role="navigation" aria-label="{{ __('ui.pagination.navigation') }}">
        <ul class="eservices-pagination__list">
            {{-- Previous --}}
            <li>
                @if ($paginator->onFirstPage())
                    <span class="eservices-pagination__item eservices-pagination__item--disabled" aria-disabled="true" aria-label="{{ __('ui.pagination.previous') }}">&lsaquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="eservices-pagination__item" rel="prev" aria-label="{{ __('ui.pagination.previous') }}">&lsaquo;</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="eservices-pagination__item eservices-pagination__item--dots" aria-disabled="true">{{ localized_digits($element) }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="eservices-pagination__item eservices-pagination__item--active" aria-current="page">{{ localized_digits((string) $page) }}</span>
                            @else
                                <a href="{{ $url }}" class="eservices-pagination__item" aria-label="{{ __('ui.pagination.goto_page', ['page' => localized_digits((string) $page)]) }}">{{ localized_digits((string) $page) }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="eservices-pagination__item" rel="next" aria-label="{{ __('ui.pagination.next') }}">&rsaquo;</a>
                @else
                    <span class="eservices-pagination__item eservices-pagination__item--disabled" aria-disabled="true" aria-label="{{ __('ui.pagination.next') }}">&rsaquo;</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
