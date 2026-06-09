<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Warga;
use App\Models\HistoriSedekah;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $today = Carbon::today();

        $totalWarga = Warga::count();
        $totalSedekahHariIni = HistoriSedekah::whereDate('waktu_ambil', $today)->count();
        
        // Kasus ganda (di mana foto_penerima_path tidak null)
        $totalKasusGandaHariIni = HistoriSedekah::whereDate('waktu_ambil', $today)
                                                ->whereNotNull('foto_penerima_path')
                                                ->count();

        $logHistori = HistoriSedekah::with(['warga', 'petugasSecurity'])
                                    ->orderBy('waktu_ambil', 'desc')
                                    ->take(50)
                                    ->get();

        return view('livewire.admin.dashboard', [
            'totalWarga' => $totalWarga,
            'totalSedekahHariIni' => $totalSedekahHariIni,
            'totalKasusGandaHariIni' => $totalKasusGandaHariIni,
            'logHistori' => $logHistori,
        ]);
    }
}
