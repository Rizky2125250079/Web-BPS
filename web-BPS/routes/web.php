<?php
use App\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PublicationController;
use App\Http\Controllers\AdminPublicationController;

// Route Utama (Welcome -> Index Publikasi BPS)
Route::get('/', [PublicationController::class, 'welcome'])->name('home');
Route::get('/publikasi/{id}', [PublicationController::class, 'show'])->name('publications.show');
Route::get('/press-release/{id}', [PublicationController::class, 'showPressRelease'])->name('pressreleases.show');
