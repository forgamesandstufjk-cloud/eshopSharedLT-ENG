@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Puslapiavimas" class="flex flex-col items-center gap-3">
        <div class="text-sm text-black">
            Rodoma nuo {{ $paginator->firstItem() }} iki {{ $paginator->lastItem() }} iš {{ $paginator->total() }}
        </div>

        <div class="flex items-center gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span
                    class="px-4 py-2 rounded border text-sm text-gray-500 cursor-not-allowed"
                    style="background-color: rgb(215, 183, 142); border-color: #836354;"
                >
                    ‹
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="px-4 py-2 rounded border text-sm text-black hover:text-white transition"
                    style="background-color: rgb(215, 183, 142); border-color: #836354;"
                    onmouseover="this.style.backgroundColor='rgb(131, 99, 84)'"
                    onmouseout="this.style.backgroundColor='rgb(215, 183, 142)'"
                >
                    ‹
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span
                        class="px-4 py-2 rounded border text-sm text-black"
                        style="background-color: rgb(215, 183, 142); border-color: #836354;"
                    >
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                aria-current="page"
                                class="px-4 py-2 rounded border text-sm text-white font-semibold"
                                style="background-color: rgb(131, 99, 84); border-color: #836354;"
                            >
                                {{ $page }}
                            </span>
                        @else
                            <a
                                href="{{ $url }}"
                                class="px-4 py-2 rounded border text-sm text-black hover:text-white transition"
                                style="background-color: rgb(215, 183, 142); border-color: #836354;"
                                onmouseover="this.style.backgroundColor='rgb(131, 99, 84)'"
                                onmouseout="this.style.backgroundColor='rgb(215, 183, 142)'"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="px-4 py-2 rounded border text-sm text-black hover:text-white transition"
                    style="background-color: rgb(215, 183, 142); border-color: #836354;"
                    onmouseover="this.style.backgroundColor='rgb(131, 99, 84)'"
                    onmouseout="this.style.backgroundColor='rgb(215, 183, 142)'"
                >
                    ›
                </a>
            @else
                <span
                    class="px-4 py-2 rounded border text-sm text-gray-500 cursor-not-allowed"
                    style="background-color: rgb(215, 183, 142); border-color: #836354;"
                >
                    ›
                </span>
            @endif
        </div>
    </nav>
@endif