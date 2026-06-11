<?php

namespace App\Livewire\Security;

use Livewire\Component;
use App\Models\Warga;
use App\Models\HistoriSedekah;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public $selectedEventId = null;

    public function mount()
    {
        // Default ke event aktif pertama
        $activeEvent = Event::currentlyActive()->first();
        if ($activeEvent) {
            $this->selectedEventId = $activeEvent->id;
        }
    }

    public function selectEvent($eventId)
    {
        $this->selectedEventId = $eventId;
    }

    public function render()
    {
        $totalWarga = Warga::count();
        $events = Event::where('is_active', true)
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $eventStats = collect();

        foreach ($events as $event) {
            $baseQuery = HistoriSedekah::where('event_id', $event->id);

            $totalMasuk = (clone $baseQuery)->count();
            $totalUnik = (clone $baseQuery)->count(DB::raw('DISTINCT warga_id'));
            $totalGanda = $totalMasuk - $totalUnik;
            $persentase = $totalWarga > 0 ? round(($totalUnik / $totalWarga) * 100, 1) : 0;

            $eventStats->push([
                'event' => $event,
                'total_masuk' => $totalMasuk,
                'total_unik' => $totalUnik,
                'total_ganda' => $totalGanda,
                'persentase' => $persentase,
            ]);
        }

        // Data untuk event yang dipilih
        $selectedStats = null;
        $recentLogs = collect();
        $demografiDesa = collect();
        $demografiUsia = collect([
            'Anak-anak (0-11)' => 0,
            'Remaja (12-25)' => 0,
            'Dewasa (26-45)' => 0,
            'Lansia (46+)' => 0,
            'Tidak Diketahui' => 0
        ]);

        if ($this->selectedEventId) {
            $selectedEvent = Event::find($this->selectedEventId);
            if ($selectedEvent) {
                $q = HistoriSedekah::where('event_id', $this->selectedEventId);
                $totalMasuk = (clone $q)->count();
                $totalUnik = (clone $q)->count(DB::raw('DISTINCT warga_id'));
                $totalGanda = $totalMasuk - $totalUnik;
                $persentase = $totalWarga > 0 ? round(($totalUnik / $totalWarga) * 100, 1) : 0;

                $selectedStats = [
                    'event' => $selectedEvent,
                    'total_masuk' => $totalMasuk,
                    'total_unik' => $totalUnik,
                    'total_ganda' => $totalGanda,
                    'persentase' => $persentase,
                ];

                $recentLogs = HistoriSedekah::with(['warga', 'petugasSecurity'])
                    ->where('event_id', $this->selectedEventId)
                    ->orderBy('waktu_ambil', 'desc')
                    ->take(30)
                    ->get();
                    
                // Hitung Demografi untuk event yang dipilih (berdasarkan warga_id unik)
                $wargaIds = (clone $q)->select('warga_id')->distinct()->pluck('warga_id');
                if ($wargaIds->isNotEmpty()) {
                    $wargaHadir = Warga::whereIn('id', $wargaIds)->get();
                    
                    // Kelompokkan berdasarkan Desa
                    $demografiDesa = $wargaHadir->groupBy('kel_desa_ktp')->map->count()->sortDesc();
                    
                    // Kelompokkan berdasarkan Usia
                    foreach ($wargaHadir as $w) {
                        $umur = $w->umur;
                        if ($umur === '-' || !is_numeric($umur)) {
                            $demografiUsia['Tidak Diketahui']++;
                        } else {
                            if ($umur <= 11) $demografiUsia['Anak-anak (0-11)']++;
                            elseif ($umur <= 25) $demografiUsia['Remaja (12-25)']++;
                            elseif ($umur <= 45) $demografiUsia['Dewasa (26-45)']++;
                            else $demografiUsia['Lansia (46+)']++;
                        }
                    }
                }
            }
        }

        return view('livewire.security.dashboard', [
            'events' => $events,
            'eventStats' => $eventStats,
            'selectedStats' => $selectedStats,
            'recentLogs' => $recentLogs,
            'totalWarga' => $totalWarga,
            'demografiDesa' => $demografiDesa,
            'demografiUsia' => $demografiUsia,
        ]);
    }
}
