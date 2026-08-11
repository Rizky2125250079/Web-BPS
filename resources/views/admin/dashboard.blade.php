<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Portal BPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between">

    <div>
        @include('partials.navbar', ['backRoute' => route('home')])

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-4 py-10 w-full space-y-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Admin</h1>
                <p class="text-sm text-gray-500 mt-1">Masuk sebagai <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span></p>
            </div>

            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form Tambah / Ubah Pengumuman -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    {{ isset($editing) ? 'Ubah Pengumuman' : 'Tambah Pengumuman' }}
                </h2>

                <form method="POST"
                      action="{{ isset($editing) ? route('admin.announcements.update', $editing) : route('admin.announcements.store') }}"
                      class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    @isset($editing) @method('PUT') @endisset

                    <input type="text" name="title" value="{{ old('title', $editing->title ?? '') }}" required maxlength="255"
                           placeholder="Judul pengumuman"
                           class="flex-1 bg-white border border-gray-300 px-4 py-2.5 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                            {{ isset($editing) ? 'Simpan' : 'Tambah' }}
                        </button>
                        @isset($editing)
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition">
                                Batal
                            </a>
                        @endisset
                    </div>
                </form>

                @error('title')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Daftar Pengumuman -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">Judul</th>
                            <th class="px-6 py-3">Admin</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($announcements as $announcement)
                            <tr class="{{ isset($editing) && $editing->is($announcement) ? 'bg-blue-50' : '' }}">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $announcement->title }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $announcement->user->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $announcement->created_at->locale('id')->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                           class="px-3 py-1.5 bg-gray-100 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}"
                                              onsubmit="return confirm('Hapus pengumuman ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-gray-100 hover:bg-red-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada pengumuman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs mt-12">
        <p>&copy; {{ date('Y') }} Portal Publikasi BPS. Powered by BPS Web API.</p>
    </footer>

</body>
</html>
