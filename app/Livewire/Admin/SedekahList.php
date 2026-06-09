<?php

namespace App\Livewire\Admin;

use App\Models\HistoriSedekah;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SedekahList extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $selectedHistori = null;

    public function showDetail($id)
    {
        $this->selectedHistori = HistoriSedekah::with(['warga', 'petugasSecurity'])->find($id);
        if ($this->selectedHistori) {
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedHistori = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $histori = HistoriSedekah::with(['warga', 'petugasSecurity'])
            ->whereHas('warga', function($query) {
                $query->where('nik', 'like', '%' . $this->search . '%')
                      ->orWhere('nama', 'like', '%' . $this->search . '%');
            })
            ->orderBy('waktu_ambil', 'desc')
            ->paginate(15);

        return view('livewire.admin.sedekah-list', [
            'histori' => $histori
        ]);
    }
}
