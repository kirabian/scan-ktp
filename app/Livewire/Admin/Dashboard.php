<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Warga;
use App\Models\HistoriSedekah;
use App\Models\Event;
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

        // Event stats
        $activeEvents = Event::currentlyActive()->get();
        $totalEvents = Event::count();
        $sedekahPerEvent = HistoriSedekah::selectRaw('event_id, COUNT(*) as total')
            ->whereDate('waktu_ambil', $today)
            ->whereNotNull('event_id')
            ->groupBy('event_id')
            ->get()
            ->map(function ($item) {
                $item->event = Event::find($item->event_id);
                return $item;
            });

        $logHistori = HistoriSedekah::with(['warga', 'petugasSecurity', 'event'])
                                    ->orderBy('waktu_ambil', 'desc')
                                    ->take(50)
                                    ->get();

        return view('livewire.admin.dashboard', [
            'totalWarga' => $totalWarga,
            'totalSedekahHariIni' => $totalSedekahHariIni,
            'totalKasusGandaHariIni' => $totalKasusGandaHariIni,
            'activeEvents' => $activeEvents,
            'totalEvents' => $totalEvents,
            'sedekahPerEvent' => $sedekahPerEvent,
            'logHistori' => $logHistori,
        ]);
    }
}
