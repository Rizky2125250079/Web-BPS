<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $detail['title'] ?? 'Detail Publikasi' }} - Portal BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header / Navbar -->

       <header class="bg-blue-900 text-white shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="font-bold text-lg tracking-wide hover:text-blue-200 transition">Portal Publikasi BPS</a>
                </div>
                <div class="flex items-center space-x-3">

                    <a href="{{ route('home') }}" class="text-sm hover:bg-blue-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
                <div class="flex items-center space-x-3">
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

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 py-10 flex-1 w-full">
        @if(!empty($detail))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Cover Image & Quick Info -->
                    <div class="flex flex-col items-center">
                        <img src="{{ !empty($detail['cover']) ? $detail['cover'] : 'https://placehold.co/300x400?text=Cover+Tidak+Tersedia' }}"
                             alt="{{ $detail['title'] ?? 'Cover Publikasi' }}"
                             class="w-full max-w-[250px] h-auto rounded-xl shadow-md border border-gray-200 object-cover aspect-[3/4]">

                        <div class="w-full mt-6 space-y-3 text-xs text-gray-600 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div>
                                <span class="font-semibold block text-gray-800">Tanggal Rilis:</span>
                                {{ !empty($detail['rl_date']) ? \Carbon\Carbon::parse($detail['rl_date'])->locale('id')->translatedFormat('d F Y') : '-' }}
                            </div>
                            <div>
                                <span class="font-semibold block text-gray-800">No. Katalog:</span>
                                {{ $detail['kat_no'] ?? '-' }}
                            </div>
                            <div>
                                <span class="font-semibold block text-gray-800">No. Publikasi:</span>
                                {{ $detail['pub_no'] ?? '-' }}
                            </div>
                            <div>
                                <span class="font-semibold block text-gray-800">ISSN / ISBN:</span>
                                {{ $detail['issn'] ?? '-' }}
                            </div>
                            <div>
                                <span class="font-semibold block text-gray-800">Ukuran File:</span>
                                {{ $detail['size'] ?? '-' }}
                            </div>
                        </div>

                        <!-- Download Button -->
                        @if(!empty($detail['pdf']))
                            <a href="{{ $detail['pdf'] }}" target="_blank" rel="noopener noreferrer"
                               class="w-full mt-4 text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-xl shadow transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Unduh File PDF
                            </a>
                        @else
                            <div class="w-full mt-4 text-center bg-gray-100 text-gray-400 font-medium py-3 px-4 rounded-xl text-xs border border-gray-200">
                                PDF Tidak Tersedia
                            </div>
                        @endif
                    </div>

                    <!-- Publication Detail Text -->
                    <div class="md:col-span-2 space-y-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold mb-2">
                                Katalog Publikasi BPS
                            </span>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-snug">
                                {{ $detail['title'] ?? 'Judul Tidak Tersedia' }}
                            </h1>
                        </div>

                        <hr class="border-gray-100">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Abstrak / Ringkasan</h3>
                            <div class="text-gray-700 leading-relaxed text-sm bg-gray-50 p-5 rounded-xl border border-gray-100">
                                @if(!empty($detail['abstract']))
                                    {!! nl2br(html_entity_decode($detail['abstract'])) !!}
                                @else
                                    <span class="italic text-gray-400">Tidak ada abstrak untuk publikasi ini.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            <!-- State jika data gagal dipanggil -->
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 shadow-sm max-w-xl mx-auto">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2 class="text-xl font-bold text-gray-800">Detail Publikasi Tidak Ditemukan</h2>
                <p class="text-gray-500 mt-2 text-sm">Data gagal diambil dari BPS API atau ID publikasi tidak valid.</p>
                <a href="{{ route('home') }}" class="inline-block mt-6 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition">
                    Kembali ke Beranda
                </a>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs">
        <p>&copy; {{ date('Y') }} Portal Publikasi BPS. Powered by BPS Web API.</p>
    </footer>

</body>
</html>
