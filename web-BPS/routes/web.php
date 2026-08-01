<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicationController;
use Illuminate\Support\Facades\Route;

// Route Utama (Welcome -> Index Publikasi BPS)
Route::get('/', [PublicationController::class, 'welcome'])->name('home');
Route::get('/publikasi/{id}', [PublicationController::class, 'show'])->name('publications.show');
Route::get('/press-release/{id}', [PublicationController::class, 'showPressRelease'])->name('pressreleases.show');

// Autentikasi (khusus admin, tidak ada halaman registrasi publik)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Area Admin: hanya user yang login DAN memiliki role admin (Gate 'admin')
// yang boleh membuat, mengubah, atau menghapus data.
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::post('/dashboard/announcements', [AdminController::class, 'storeAnnouncement'])->name('admin.announcements.store');
    Route::get('/dashboard/announcements/{announcement}/edit', [AdminController::class, 'editAnnouncement'])->name('admin.announcements.edit');
    Route::put('/dashboard/announcements/{announcement}', [AdminController::class, 'updateAnnouncement'])->name('admin.announcements.update');
    Route::delete('/dashboard/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])->name('admin.announcements.destroy');
});
