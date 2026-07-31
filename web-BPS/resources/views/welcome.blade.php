<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Informasi Publikasi BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <!-- Header / Navbar -->
    <header class="bg-blue-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="font-bold text-lg tracking-wide">Portal Publikasi BPS</span>
            </div>
            <div>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-700 text-sm font-semibold">Dashboard Admin</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 border border-blue-400 rounded-lg hover:bg-blue-800 text-sm font-semibold">Login Admin</a>
                    @endauth
                @endif
            </div>
        </div>
    </header>


    <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16 px-4">
        <div class="max-w-4xl mx-auto text-center space-y-6">
            <h1 class="text-3xl md:text-5xl font-extrabold">Layanan Publikasi Data Statistik BPS</h1>
            <p class="text-blue-100 text-base md:text-lg">Temukan dokumen publikasi resmi, analisis indikator ekonomi, dan sosial terbaru.</p>
            <form action="{{ route('home') }}" method="GET" class="flex flex-col md:flex-row gap-2 max-w-2xl mx-auto pt-4">
                <input type="text" name="search" value="{{ $keyword }}" placeholder="Cari judul publikasi..."
                       class="bg-white w-full px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 font-semibold rounded-lg transition">Cari</button>
            </form>
        </div>
    </section>

    <!-- Content Section -->
    <main class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b-2 border-blue-900 pb-2 inline-block">Publikasi Terkini</h2>

        <!-- Grid Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($apiPublications as $item)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition border border-gray-100 flex flex-col justify-between overflow-hidden">
                    <img src="{{ $item['cover'] ?? 'https://via.placeholder.com/300x400' }}" alt="{{ $item['title'] }}" class="w-full h-56 object-cover">
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-semibold px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                {{ \Carbon\Carbon::parse($item['rls_date'] ?? now())->format('d M Y') }}
                            </span>
                            <h3 class="font-bold text-gray-900 mt-2 text-sm line-clamp-2 hover:text-blue-600">
                                {{ $item['title'] }}
                            </h3>
                        </div>
                        <a href="{{ route('publications.show', $item['pub_id']) }}" class="mt-4 block text-center py-2 px-4 bg-gray-100 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    Tidak ada publikasi ditemukan.
                </div>
            @endforelse
        </div>
    </main>

</body>
</html>
