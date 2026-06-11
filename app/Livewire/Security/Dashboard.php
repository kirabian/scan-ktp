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
            }
        }

        return view('livewire.security.dashboard', [
            'events' => $events,
            'eventStats' => $eventStats,
            'selectedStats' => $selectedStats,
            'recentLogs' => $recentLogs,
            'totalWarga' => $totalWarga,
        ]);
    }
}
