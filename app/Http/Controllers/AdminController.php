<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{

    public function dashboard(): View
    {
        $announcements = Announcement::with('user')->latest()->get();

        return view('admin.dashboard', compact('announcements'));
    }

    public function editAnnouncement(Announcement $announcement): View
    {
        $announcements = Announcement::with('user')->latest()->get();

        return view('admin.dashboard', [
            'announcements' => $announcements,
            'editing' => $announcement,
        ]);
    }


    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->announcements()->create($validated);

        return redirect()->route('dashboard')->with('status', 'Pengumuman berhasil ditambahkan.');
    }


    public function updateAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $announcement->update($validated);

        return redirect()->route('dashboard')->with('status', 'Pengumuman berhasil diperbarui.');
    }


    public function destroyAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('dashboard')->with('status', 'Pengumuman berhasil dihapus.');
    }
}
