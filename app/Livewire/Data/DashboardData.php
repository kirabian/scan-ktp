<?php

namespace App\Livewire\Data;

use App\Models\Warga;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DashboardData extends Component
{
    public $topGenders = [];
    public $topAges = [];
    public $topKecamatan = [];
    public $topDesa = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // 1. Top Genders
        $this->topGenders = Warga::selectRaw('jenis_kelamin as name, count(*) as count')
            ->whereNotNull('jenis_kelamin')
            ->where('jenis_kelamin', '!=', '')
            ->groupBy('jenis_kelamin')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        // 2. Top Kecamatan
        $this->topKecamatan = Warga::selectRaw('kecamatan_ktp as name, count(*) as count')
            ->whereNotNull('kecamatan_ktp')
            ->where('kecamatan_ktp', '!=', '')
            ->groupBy('kecamatan_ktp')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        // 3. Top Desa
        $this->topDesa = Warga::selectRaw('kel_desa_ktp as name, count(*) as count')
            ->whereNotNull('kel_desa_ktp')
            ->where('kel_desa_ktp', '!=', '')
            ->groupBy('kel_desa_ktp')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        // 4. Top Ages
        // Since age is computed in PHP, we need to fetch all and group them
        $allWarga = Warga::select('tempat_tgl_lahir')->get();
        $ageCounts = [];
        foreach ($allWarga as $w) {
            $age = $w->umur;
            if ($age !== '-') {
                if (!isset($ageCounts[$age])) {
                    $ageCounts[$age] = 0;
                }
                $ageCounts[$age]++;
            }
        }
        
        // Sort by count descending
        arsort($ageCounts);
        
        // Take top 5
        $this->topAges = collect(array_slice($ageCounts, 0, 5, true))->map(function ($count, $age) {
            return ['name' => $age, 'count' => $count];
        })->values()->toArray();
    }

    public function render()
    {
        return view('livewire.data.dashboard-data');
    }
}
