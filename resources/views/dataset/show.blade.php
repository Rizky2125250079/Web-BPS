<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $detail['title'] ?? 'Detail Dataset' }} - Portal BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <div>
        @include('partials.navbar', ['backRoute' => route('dataset.index'), 'backLabel' => 'Kembali ke Dataset'])

        <main class="max-w-7xl mx-auto px-4 py-10 flex-1 w-full space-y-6">

            @if(!empty($detail))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">

                        <!-- Download & QR -->
                        <div class="flex flex-col md:col-span-1">
                            <span class="inline-block w-fit px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold mb-4">
                                Tabel Statis BPS
                            </span>

                            <!-- Download Button -->
                            @if(!empty($detail['excel']))
                                <a href="{{ $detail['excel'] }}" target="_blank" rel="noopener noreferrer"
                                   class="w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-xl shadow transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Excel
                                </a>
                                <!-- QR Code: scan dari HP untuk download langsung -->
                                <div class="w-full mt-4 flex flex-col items-center gap-3 bg-gray-50 border border-gray-100 rounded-xl p-6">
                                    <img
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=8&data={{ urlencode($detail['excel']) }}"
                                        alt="QR Code Download Excel"
                                        width="240" height="240"
                                        class="w-[240px] h-[240px] bg-white border border-gray-200 rounded-lg p-2">
                                    <p class="text-sm text-gray-500 text-center">
                                        Scan pakai kamera HP untuk<br>langsung download file Excel
                                    </p>
                                </div>
                            @else
                                <div class="w-full text-center bg-gray-100 text-gray-400 font-medium py-3 px-4 rounded-xl text-xs border border-gray-200">
                                    File Excel Tidak Tersedia
                                </div>
                            @endif

                            <a href="{{ route('dataset.index') }}"
                               class="w-full mt-3 text-center bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-xl border border-gray-200 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Kembali ke Dataset
                            </a>
                        </div>

                        <!-- Title & Metadata -->
                        <div class="md:col-span-2 flex flex-col">
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-snug mb-6">
                                {{ $detail['title'] ?? 'Judul Tidak Tersedia' }}
                            </h1>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                                    <span class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Tanggal Update
                                    </span>
                                    <span class="text-sm font-medium text-gray-800">{{ $detail['updt_date'] ?? '-' }}</span>
                                </div>

                                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                                    <span class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Ukuran File
                                    </span>
                                    <span class="text-sm font-medium text-gray-800">{{ $detail['size'] ?? '-' }}</span>
                                </div>

                                @if(!empty($detail['subj']))
                                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 sm:col-span-2">
                                        <span class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5.586 5.586a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10V5a2 2 0 012-2z"/>
                                            </svg>
                                            Subjek
                                        </span>
                                        <span class="text-sm font-medium text-gray-800">{{ $detail['subj'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Data Table -->
                @if(!empty($detail['table']))
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                            </svg>
                            <h2 class="font-semibold text-gray-800 text-sm">Isi Tabel Data</h2>
                        </div>
                        <div class="p-6 overflow-x-auto dataset-table">
                            {!! strip_tags($detail['table'], '<table><thead><tbody><tfoot><tr><th><td><caption><colgroup><col><br>') !!}
                        </div>
                    </div>
                @endif
            @else
                <!-- State jika data gagal dipanggil -->
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 shadow-sm max-w-xl mx-auto">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h2 class="text-xl font-bold text-gray-800">Detail Dataset Tidak Ditemukan</h2>
                    <p class="text-gray-500 mt-2 text-sm">Data gagal diambil dari BPS API atau ID dataset tidak valid.</p>
                    <a href="{{ route('dataset.index') }}" class="inline-block mt-6 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition">
                        Kembali ke Dataset
                    </a>
                </div>
            @endif

        </main>
    </div>

    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs mt-12">
        <p>&copy; {{ date('Y') }} Portal Publikasi BPS. Powered by BPS Web API.</p>
    </footer>

</body>
</html>