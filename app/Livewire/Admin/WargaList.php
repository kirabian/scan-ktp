<?php

namespace App\Livewire\Admin;

use App\Models\Warga;
use Livewire\Component;
use Livewire\WithPagination;

class WargaList extends Component
{
    use WithPagination;

    public $search = '';
    
    public $selectedWarga = null;
    public $isModalOpen = false;

    public function render()
    {
        $wargas = Warga::where('nik', 'like', '%' . $this->search . '%')
            ->orWhere('nama', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.warga-list', [
            'wargas' => $wargas,
        ])->layout('layouts.app', ['header' => '<h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Warga</h2>']);
    }

    public function viewDetails($id)
    {
        $this->selectedWarga = Warga::findOrFail($id);
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->selectedWarga = null;
    }
}
