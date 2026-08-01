<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $detail['title'] ?? 'Detail Press Release' }} - Portal BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header / Navbar -->
    @include('partials.navbar', ['backRoute' => route('home')])

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-10 flex-1 w-full">
        @if(!empty($detail))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 md:p-10">

                <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold mb-3">
                    Berita Resmi Statistik (Press Release)
                </span>

                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-snug">
                    {{ $detail['title'] ?? 'Judul Tidak Tersedia' }}
                </h1>

                <!-- Meta info -->
                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 text-xs text-gray-500 border-b border-gray-100 pb-6">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Rilis: {{ !empty($detail['rl_date']) ? \Carbon\Carbon::parse($detail['rl_date'])->locale('id')->translatedFormat('d F Y') : '-' }}
                    </span>
                    @if(!empty($detail['updt_date']))
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Diperbarui: {{ \Carbon\Carbon::parse($detail['updt_date'])->locale('id')->translatedFormat('d F Y') }}
                        </span>
                    @endif
                    @if(!empty($detail['size']))
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v10a2 2 0 01-2 2z"/>
                            </svg>
                            {{ $detail['size'] }}
                        </span>
                    @endif
                </div>

                <!-- Abstrak -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Abstrak / Ringkasan</h3>
                    <div class="text-gray-700 leading-relaxed text-sm bg-gray-50 p-5 rounded-xl border border-gray-100">
                        @if(!empty($detail['abstract']))
                            {!! nl2br(html_entity_decode($detail['abstract'])) !!}
                        @else
                            <span class="italic text-gray-400">Tidak ada abstrak untuk press release ini.</span>
                        @endif
                    </div>
                </div>

                <!-- Download Button -->
                @if(!empty($detail['pdf']))
                    <a href="{{ $detail['pdf'] }}" target="_blank" rel="noopener noreferrer"
                       class="w-full md:w-auto mt-6 inline-flex items-center justify-center gap-2 text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-xl shadow transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Unduh File PDF
                    </a>
                @else
                    <div class="w-full md:w-auto mt-6 inline-block text-center bg-gray-100 text-gray-400 font-medium py-3 px-6 rounded-xl text-xs border border-gray-200">
                        PDF Tidak Tersedia
                    </div>
                @endif
            </div>
        @else
            <!-- State jika data gagal dipanggil -->
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 shadow-sm max-w-xl mx-auto">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2 class="text-xl font-bold text-gray-800">Detail Press Release Tidak Ditemukan</h2>
                <p class="text-gray-500 mt-2 text-sm">Data gagal diambil dari BPS API atau ID press release tidak valid.</p>
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
