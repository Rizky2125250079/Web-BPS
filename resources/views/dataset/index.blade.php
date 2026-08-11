<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dataset Statistik - Portal BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <div>
        @include('partials.navbar', ['backRoute' => route('home')])

        <!-- Hero & Search -->
        <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16 px-4">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                <h1 class="text-3xl md:text-5xl font-extrabold">Dataset Statistik BPS</h1>
                <p class="text-blue-100 text-base md:text-lg">Jelajahi tabel statis (data mentah dalam format tabel) hasil publikasi BPS.</p>

                <form action="{{ route('dataset.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 max-w-2xl mx-auto pt-4">
                <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Cari judul dataset..."
                    class="bg-white w-full px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 font-semibold rounded-lg transition whitespace-nowrap">
                    Cari
                </button>
                @if(!empty($keyword))
                    <a href="{{ route('dataset.index') }}"
                    class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/40 font-semibold rounded-lg transition flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset Filter
                    </a>
                @endif
            </form>
            </div>
        </section>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-12 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-2 border-b-2 border-blue-900 pb-2">
                <h2 class="text-2xl font-bold text-gray-900">
                    @if(!empty($keyword))
                        Hasil Pencarian Dataset: "{{ $keyword }}"
                    @else
                        Dataset Terbaru
                    @endif
                </h2>
                <span class="text-sm text-gray-500">{{ number_format($totalDataset) }} dataset ditemukan</span>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($tables as $item)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition border border-gray-100 p-5 flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                {{ $item['subj'] ?? 'Statistik' }}
                            </span>
                            <span class="text-xs text-gray-400 whitespace-nowrap">
                                {{ $item['updt_date'] ?? '-' }}
                            </span>
                        </div>

                        <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-3 flex-1" title="{{ $item['title'] ?? '' }}">
                            {{ $item['title'] ?? 'Judul Tidak Tersedia' }}
                        </h3>

                        <a href="{{ route('dataset.show', $item['table_id'] ?? '#') }}"
                           class="mt-auto block text-center py-2 px-4 bg-gray-100 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                            Lihat Detail
                        </a>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 text-gray-500 bg-white rounded-xl border border-gray-100">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Tidak ada dataset ditemukan.
                        @if(!empty($keyword))
                            <div class="mt-2 text-sm">
                                Coba kata kunci lain, atau
                                <a href="{{ route('dataset.index') }}" class="text-blue-600 hover:underline font-semibold">lihat semua dataset</a>.
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($totalPages > 1)
                @php
                    $window = 2;
                    $start = max(1, $currentPage - $window);
                    $end = min($totalPages, $currentPage + $window);
                @endphp

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 bg-white px-4 py-4 rounded-xl shadow-sm gap-4">
                    <div class="text-sm text-gray-600">
                        Halaman <strong class="text-gray-900">{{ $currentPage }}</strong> dari <strong class="text-gray-900">{{ $totalPages }}</strong>
                    </div>

                    <nav class="flex items-center gap-1 text-sm font-medium">
                        {{-- Sebelumnya --}}
                        @if($currentPage > 1)
                            <a href="{{ route('dataset.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}"
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                &larr; <span class="hidden sm:inline">Sebelumnya</span>
                            </a>
                        @else
                            <span class="px-3 py-2 border border-gray-200 rounded-lg text-gray-300 cursor-not-allowed">
                                &larr; <span class="hidden sm:inline">Sebelumnya</span>
                            </span>
                        @endif

                        {{-- Halaman 1 + ellipsis kiri --}}
                        @if($start > 1)
                            <a href="{{ route('dataset.index', array_merge(request()->query(), ['page' => 1])) }}"
                               class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">1</a>
                            @if($start > 2)
                                <span class="px-2 text-gray-400">&hellip;</span>
                            @endif
                        @endif

                        {{-- Nomor halaman di sekitar halaman aktif --}}
                        @for($p = $start; $p <= $end; $p++)
                            @if($p == $currentPage)
                                <span class="px-3 py-2 rounded-lg bg-blue-900 text-white font-bold">{{ $p }}</span>
                            @else
                                <a href="{{ route('dataset.index', array_merge(request()->query(), ['page' => $p])) }}"
                                   class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">{{ $p }}</a>
                            @endif
                        @endfor

                        {{-- Ellipsis kanan + halaman terakhir --}}
                        @if($end < $totalPages)
                            @if($end < $totalPages - 1)
                                <span class="px-2 text-gray-400">&hellip;</span>
                            @endif
                            <a href="{{ route('dataset.index', array_merge(request()->query(), ['page' => $totalPages])) }}"
                               class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">{{ $totalPages }}</a>
                        @endif

                        {{-- Selanjutnya --}}
                        @if($currentPage < $totalPages)
                            <a href="{{ route('dataset.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}"
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                <span class="hidden sm:inline">Selanjutnya</span> &rarr;
                            </a>
                        @else
                            <span class="px-3 py-2 border border-gray-200 rounded-lg text-gray-300 cursor-not-allowed">
                                <span class="hidden sm:inline">Selanjutnya</span> &rarr;
                            </span>
                        @endif
                    </nav>
                </div>
            @endif

        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs mt-12">
        <p>&copy; {{ date('Y') }} Portal Publikasi BPS. Powered by BPS Web API.</p>
    </footer>

</body>
</html>