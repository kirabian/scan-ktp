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
        $query = HistoriSedekah::with(['warga', 'petugasSecurity']);

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->whereHas('warga', function ($q) use ($searchTerm) {
                $q->where('nik', 'like', $searchTerm)
                  ->orWhere('nama', 'like', $searchTerm);
            });
        }

        $histori = $query->orderBy('waktu_ambil', 'desc')->paginate(15);

        return view('livewire.admin.sedekah-list', [
            'histori' => $histori
        ]);
    }
}
