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
        @include('partials.navbar')

        <!-- Hero & Search Section (mencari Publikasi & Press Release sekaligus) -->
        <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16 px-4">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                <h1 class="text-3xl md:text-5xl font-extrabold">Layanan Publikasi Data Statistik BPS</h1>
                <p class="text-blue-100 text-base md:text-lg">Temukan dokumen publikasi resmi, berita resmi statistik, dan indikator terbaru dalam satu pencarian.</p>

                <form action="{{ route('home') }}" method="GET" class="flex flex-col md:flex-row gap-2 max-w-2xl mx-auto pt-4">
                    <input type="text" name="search" value="{{ $keyword ?? request('search') }}" placeholder="Cari judul publikasi atau press release..."
                           class="bg-white w-full px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">

                    {{-- Pertahankan filter tahun yang sedang aktif di kedua bagian supaya tidak ikut ter-reset saat pencarian baru --}}
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="year_brs" value="{{ $yearBrs }}">

                    <button type="submit" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 font-semibold rounded-lg transition">Cari</button>
                </form>
            </div>
        </section>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-12 space-y-16">

            <!-- ==================== SECTION: PENGUMUMAN ==================== -->
            @if($announcements->isNotEmpty())
                <section>
                    <div class="flex items-center justify-between gap-3 mb-6 border-b-2 border-amber-500 pb-2">
                        <h2 class="text-2xl font-bold text-gray-900">Pengumuman</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach($announcements as $announcement)
                            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition border border-amber-100 p-5 flex flex-col gap-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-1 bg-amber-100 text-amber-800 rounded-full w-fit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    Pengumuman
                                </span>
                                <h3 class="font-bold text-gray-900 text-sm leading-snug">
                                    {{ $announcement->title }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-auto">
                                    Oleh {{ $announcement->user->name ?? 'Admin' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- ==================== SECTION: PUBLIKASI ==================== -->
            <section>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 border-b-2 border-blue-900 pb-2">
                    <h2 class="text-2xl font-bold text-gray-900">
                        @if(!empty($keyword) && !empty($year))
                            Hasil Pencarian Publikasi: "{{ $keyword }}" &middot; Tahun {{ $year }}
                        @elseif(!empty($keyword))
                            Hasil Pencarian Publikasi: "{{ $keyword }}"
                        @elseif(!empty($year))
                            Publikasi Tahun {{ $year }}
                        @else
                            Publikasi Terkini
                        @endif
                    </h2>

                    <!-- Filter Tahun khusus Publikasi -->
                    <form action="{{ route('home') }}" method="GET" class="flex items-center gap-2 text-sm">
                        <label for="year" class="font-medium text-gray-600 whitespace-nowrap">Tahun:</label>
                        <select id="year" name="year" onchange="this.form.submit()"
                                class="bg-white border border-gray-300 px-3 py-2 rounded-lg text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Tahun</option>
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>

                        {{-- Pertahankan search & filter tahun Press Release yang sedang aktif --}}
                        <input type="hidden" name="search" value="{{ $keyword }}">
                        <input type="hidden" name="year_brs" value="{{ $yearBrs }}">
                    </form>
                </div>

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

                <!-- PAGINATION PUBLIKASI -->
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
            </section>

            <!-- ==================== SECTION: PRESS RELEASE ==================== -->
            <section>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 border-b-2 border-emerald-600 pb-2">
                    <h2 class="text-2xl font-bold text-gray-900">
                        @if(!empty($keyword) && !empty($yearBrs))
                            Hasil Pencarian Press Release: "{{ $keyword }}" &middot; Tahun {{ $yearBrs }}
                        @elseif(!empty($keyword))
                            Hasil Pencarian Press Release: "{{ $keyword }}"
                        @elseif(!empty($yearBrs))
                            Press Release Tahun {{ $yearBrs }}
                        @else
                            Berita Resmi Statistik (Press Release) Terkini
                        @endif
                    </h2>

                    <!-- Filter Tahun khusus Press Release -->
                    <form action="{{ route('home') }}" method="GET" class="flex items-center gap-2 text-sm">
                        <label for="year_brs" class="font-medium text-gray-600 whitespace-nowrap">Tahun:</label>
                        <select id="year_brs" name="year_brs" onchange="this.form.submit()"
                                class="bg-white border border-gray-300 px-3 py-2 rounded-lg text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Tahun</option>
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ (string) $yearBrs === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>

                        {{-- Pertahankan search & filter tahun Publikasi yang sedang aktif --}}
                        <input type="hidden" name="search" value="{{ $keyword }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                    </form>
                </div>

                <!-- Grid Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @forelse($apiPressReleases as $item)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition border border-gray-100 flex flex-col justify-between overflow-hidden">
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-xs font-semibold px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full">
                                        {{ !empty($item['rl_date']) ? \Carbon\Carbon::parse($item['rl_date'])->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </span>
                                    <h3 class="font-bold text-gray-900 mt-2 text-sm line-clamp-3 hover:text-emerald-700 transition" title="{{ $item['title'] ?? '' }}">
                                        {{ $item['title'] ?? 'Judul Tidak Tersedia' }}
                                    </h3>
                                </div>
                                <a href="{{ route('pressreleases.show', $item['brs_id'] ?? '#') }}" class="mt-4 block text-center py-2 px-4 bg-gray-100 hover:bg-emerald-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded-xl border border-gray-100">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Tidak ada press release ditemukan.
                        </div>
                    @endforelse
                </div>

                <!-- PAGINATION PRESS RELEASE -->
                @if(isset($totalPagesBrs) && count($apiPressReleases) > 0)
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 bg-white px-4 py-4 rounded-xl shadow-sm gap-4">

                        <!-- Info Halaman -->
                        <div class="text-sm text-gray-600">
                            Halaman <strong class="text-gray-900">{{ $currentPageBrs }}</strong> dari <strong class="text-gray-900">{{ $totalPagesBrs }}</strong>
                        </div>

                        <!-- Tombol Navigasi -->
                        <div class="flex items-center gap-2 text-sm font-medium">
                            {{-- Tombol Sebelumnya --}}
                            <a href="{{ route('home', array_merge(request()->query(), ['page_brs' => max(1, $currentPageBrs - 1)])) }}"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-1 {{ $currentPageBrs <= 1 ? 'opacity-40 pointer-events-none' : '' }}">
                                &larr; Sebelumnya
                            </a>

                            {{-- Indikator Ringkas --}}
                            <span class="px-3 py-2 text-emerald-800 bg-emerald-50 rounded-lg border border-emerald-100 font-bold">
                                {{ $currentPageBrs }} / {{ $totalPagesBrs }}
                            </span>

                            {{-- Tombol Selanjutnya --}}
                            <a href="{{ route('home', array_merge(request()->query(), ['page_brs' => min($totalPagesBrs, $currentPageBrs + 1)])) }}"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-1 {{ $currentPageBrs >= $totalPagesBrs ? 'opacity-40 pointer-events-none' : '' }}">
                                Selanjutnya &rarr;
                            </a>
                        </div>

                    </div>
                @endif
            </section>

        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs mt-12">
        <p>&copy; {{ date('Y') }} Portal Publikasi BPS. Powered by BPS Web API.</p>
    </footer>


</body>
</html>
