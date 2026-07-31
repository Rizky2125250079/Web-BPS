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
    <header class="bg-blue-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                <div class="bg-blue-600 p-2 rounded-lg font-bold text-xl">BPS</div>
                <span class="font-bold text-lg tracking-wide">Portal Publikasi</span>
            </a>
            <a href="{{ route('home') }}" class="text-sm bg-blue-800  px-4 py-2 rounded-lg transition">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 py-10 flex-1">
        @if($detail)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Cover Image & Quick Info -->
                    <div class="flex flex-col items-center">
                        <img src="{{ $detail['cover'] ?? 'https://via.placeholder.com/300x400' }}"
                             alt="{{ $detail['title'] ?? 'Cover' }}"
                             class="w-full max-w-[250px] rounded-lg shadow-md border object-cover">

                        <div class="w-full mt-6 space-y-3 text-xs text-gray-600 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div>
                                <span class="font-semibold block text-gray-800">Tanggal Rilis:</span>
                                {{ isset($detail['rls_date']) ? \Carbon\Carbon::parse($detail['rls_date'])->format('d F Y') : '-' }}
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
                            <div class="text-gray-700 leading-relaxed text-sm whitespace-pre-line bg-gray-50 p-4 rounded-xl border border-gray-100">
                                {!! nl2br(e($detail['abstract'] ?? 'Tidak ada abstrak untuk publikasi ini.')) !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-xl border p-8">
                <h2 class="text-xl font-bold text-gray-700">Detail Publikasi Tidak Ditemukan</h2>
                <p class="text-gray-500 mt-2">Data gagal diambil dari BPS API atau ID publikasi tidak valid.</p>
                <a href="{{ route('home') }}" class="inline-block mt-4 text-blue-600 hover:underline">Kembali ke beranda</a>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs">
        <p>&copy; {{ date('Y') }} Portal Publikasi BPS. Powered by BPS Web API.</p>
    </footer>

</body>
</html>
