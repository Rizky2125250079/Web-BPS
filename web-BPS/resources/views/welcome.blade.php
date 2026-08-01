<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Informasi Publikasi BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <div>
        <!-- Header / Navbar -->
        <header class="bg-blue-900 text-white shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="font-bold text-lg tracking-wide hover:text-blue-200 transition">Portal Publikasi BPS</a>
                </div>
                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-700 text-sm font-semibold transition">Dashboard Admin</a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 border border-blue-400 rounded-lg hover:bg-blue-800 text-sm font-semibold transition">Login Admin</a>
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero & Search Section -->
        <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16 px-4">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                <h1 class="text-3xl md:text-5xl font-extrabold">Layanan Publikasi Data Statistik BPS</h1>
                <p class="text-blue-100 text-base md:text-lg">Temukan dokumen publikasi resmi, analisis indikator ekonomi, dan sosial terbaru.</p>

                <form action="{{ route('home') }}" method="GET" class="flex flex-col md:flex-row gap-2 max-w-2xl mx-auto pt-4">
                    <input type="text" name="search" value="{{ $keyword ?? request('search') }}" placeholder="Cari judul publikasi..."
                           class="bg-white w-full px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 font-semibold rounded-lg transition">Cari</button>
                </form>
            </div>
        </section>

        <!-- Statistik Jumlah Publikasi per Tahun (disembunyikan saat sedang menampilkan hasil pencarian) -->
        @if(empty($keyword))
        <section id="statistik-tahunan" class="max-w-7xl mx-auto px-4 pt-12">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-blue-900 pb-2 inline-block">Statistik Jumlah Publikasi per Tahun</h2>

            @php
                $totalSemuaTahun = array_sum($yearlyCounts);
                $tahunTerbanyak  = !empty($yearlyCounts) ? array_search(max($yearlyCounts), $yearlyCounts) : '-';
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-500 font-semibold uppercase">Total Publikasi</p>
                    <p class="text-3xl font-extrabold text-blue-900 mt-1">{{ number_format($totalSemuaTahun, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $chartStartYear }} &ndash; {{ $chartEndYear }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-500 font-semibold uppercase">Tahun Terbanyak</p>
                    <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $tahunTerbanyak }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($yearlyCounts[$tahunTerbanyak] ?? 0, 0, ',', '.') }} publikasi</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-500 font-semibold uppercase">Rata-rata per Tahun</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">
                        {{ count($yearlyCounts) ? number_format($totalSemuaTahun / count($yearlyCounts), 1, ',', '.') : 0 }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ count($yearlyCounts) }} tahun ditampilkan</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                @if($totalSemuaTahun > 0)
                    <canvas id="publikasiPerTahunChart" height="90"
                        data-labels="{{ json_encode(array_map('strval', array_keys($yearlyCounts))) }}"
                        data-values="{{ json_encode(array_values($yearlyCounts)) }}"></canvas>
                @else
                    <div class="text-center py-16 text-gray-500">
                        Tidak ada data publikasi pada rentang tahun ini, atau API BPS sedang tidak dapat diakses.
                    </div>
                @endif
            </div>
        </section>
        @endif

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-12">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-blue-900 pb-2 inline-block">
                @if(!empty($keyword))
                    Hasil Pencarian: "{{ $keyword }}"
                @else
                    Publikasi Terkini
                @endif
            </h2>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($apiPublications as $item)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition border border-gray-100 flex flex-col justify-between overflow-hidden">
                        <img src="{{ !empty($item['cover']) ? $item['cover'] : 'https://placehold.co/300x400?text=No+Cover' }}"
                             alt="{{ $item['title'] ?? 'Publikasi' }}"
                             class="w-full h-56 object-cover">

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-semibold px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                    {{ !empty($item['rl_date']) ? \Carbon\Carbon::parse($item['rl_date'])->locale('id')->translatedFormat('d M Y') : '-' }}
                                </span>
                                <h3 class="font-bold text-gray-900 mt-2 text-sm line-clamp-2 hover:text-blue-600 transition" title="{{ $item['title'] ?? '' }}">
                                    {{ $item['title'] ?? 'Judul Tidak Tersedia' }}
                                </h3>
                            </div>
                            <a href="{{ route('publications.show', $item['pub_id'] ?? '#') }}" class="mt-4 block text-center py-2 px-4 bg-gray-100 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded-xl border border-gray-100">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Tidak ada publikasi ditemukan.
                    </div>
                @endforelse
            </div>

            <!-- PAGINATION API BPS -->
            @if(isset($totalPages) && count($apiPublications) > 0)
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 bg-white px-4 py-4 rounded-xl shadow-sm gap-4">

                    <!-- Info Halaman -->
                    <div class="text-sm text-gray-600">
                        Halaman <strong class="text-gray-900">{{ $currentPage }}</strong> dari <strong class="text-gray-900">{{ $totalPages }}</strong>
                    </div>

                    <!-- Tombol Navigasi -->
                    <div class="flex items-center gap-2 text-sm font-medium">
                        {{-- Tombol Sebelumnya --}}
                        <a href="{{ route('home', array_merge(request()->query(), ['page' => max(1, $currentPage - 1)])) }}"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-1 {{ $currentPage <= 1 ? 'opacity-40 pointer-events-none' : '' }}">
                            &larr; Sebelumnya
                        </a>

                        {{-- Indikator Ringkas --}}
                        <span class="px-3 py-2 text-blue-900 bg-blue-50 rounded-lg border border-blue-100 font-bold">
                            {{ $currentPage }} / {{ $totalPages }}
                        </span>

                        {{-- Tombol Selanjutnya --}}
                        <a href="{{ route('home', array_merge(request()->query(), ['page' => min($totalPages, $currentPage + 1)])) }}"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-1 {{ $currentPage >= $totalPages ? 'opacity-40 pointer-events-none' : '' }}">
                            Selanjutnya &rarr;
                        </a>
                    </div>

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
