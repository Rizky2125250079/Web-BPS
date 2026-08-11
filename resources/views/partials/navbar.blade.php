{{--
    Navbar bersama, dipakai di setiap halaman (beranda, detail publikasi,
    detail press release, login, dan dashboard admin) supaya tombol
    Login/Logout selalu ada di setiap navbar.

    Parameter opsional:
    - $backRoute : URL tujuan tombol "kembali". Jika diisi, tombol kembali
                   akan tampil di grup kanan, tepat di sebelah Login/Logout.
    - $backLabel : Label tombol kembali (default "Kembali ke Beranda").
--}}
<header class="bg-blue-900 text-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <a href="{{ route('home') }}" class="font-bold text-lg tracking-wide hover:text-blue-200 transition">
            Portal Publikasi BPS
        </a>

        {{-- Grup kanan: tombol kembali (jika ada) + login/logout, selalu rata kanan --}}
        <div class="flex items-center gap-3">
            @isset($backRoute)
                <a href="{{ $backRoute }}" class="text-sm hover:bg-blue-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ $backLabel ?? 'Kembali ke Beranda' }}
                </a>
            @endisset

            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-700 text-sm font-semibold transition">
                    Dashboard Admin
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-blue-400 rounded-lg hover:bg-blue-800 text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 border border-blue-400 rounded-lg hover:bg-blue-800 text-sm font-semibold transition">
                    Login Admin
                </a>
            @endauth

            <a href="{{ route('dataset.index') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 {{ request()->routeIs('dataset.*') ? 'bg-blue-600' : 'hover:bg-blue-700 border border-blue-400' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V9m4 8V5m4 12v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Dataset
            </a>
        </div>
    </div>
</header>
