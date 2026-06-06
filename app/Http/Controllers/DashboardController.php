<?php

namespace App\Http\Controllers;

use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with distribution summary.
     */
    public function index()
    {
        $totalWarga = Warga::count();
        $sudahAmbil = Warga::where('status_ambil', true)->count();
        $belumAmbil = $totalWarga - $sudahAmbil;

        $wargaSudahAmbil = Warga::where('status_ambil', true)
            ->with('petugas:id,name')
            ->orderByDesc('waktu_ambil')
            ->paginate(25);

        return view('admin.dashboard', compact(
            'totalWarga',
            'sudahAmbil',
            'belumAmbil',
            'wargaSudahAmbil'
        ));
    }

    /**
     * Serve a secure KTP photo (behind auth).
     */
    public function showFoto(string $filename)
    {
        $path = 'secure_ktp/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Foto KTP tidak ditemukan.');
        }

        return response()->file(Storage::disk('local')->path($path));
    }
}
