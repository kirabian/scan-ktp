<?php

namespace App\Livewire\Admin;

use App\Models\Warga;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DataDownload extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $totalWarga = Warga::count();
        $sudahDownload = Warga::where('qr_download_count', '>', 0)->count();
        $doubleDownload = Warga::where('qr_download_count', '>', 1)->count();

        $query = Warga::where('qr_download_count', '>', 0);

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nik', 'like', $searchTerm)
                  ->orWhere('nama', 'like', $searchTerm);
            });
        }

        $wargas = $query->orderBy('last_qr_download_at', 'desc')->paginate(10);

        return view('livewire.admin.data-download', [
            'totalWarga' => $totalWarga,
            'sudahDownload' => $sudahDownload,
            'doubleDownload' => $doubleDownload,
            'wargas' => $wargas,
        ]);
    }
}
