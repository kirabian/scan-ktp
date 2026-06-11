<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class EventManage extends Component
{
    use WithPagination;

    public $search = '';

    public $eventId;
    public $judul = '';
    public $deskripsi = '';
    public $tanggal_mulai = '';
    public $jam_mulai = '08:00';
    public $tanggal_selesai = '';
    public $jam_selesai = '17:00';
    public $is_active = true;

    public $isModalOpen = false;

    protected $rules = [
        'judul' => 'required|string|max:255',
        'deskripsi' => 'nullable|string',
        'tanggal_mulai' => 'required|date',
        'jam_mulai' => 'required',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'jam_selesai' => 'required',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $events = Event::where('judul', 'like', '%' . $this->search . '%')
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(10);

        return view('livewire.admin.event-manage', [
            'events' => $events,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->eventId = null;
        $this->judul = '';
        $this->deskripsi = '';
        $this->tanggal_mulai = '';
        $this->jam_mulai = '08:00';
        $this->tanggal_selesai = '';
        $this->jam_selesai = '17:00';
        $this->is_active = true;
    }

    public function store()
    {
        $this->validate();

        Event::updateOrCreate(
            ['id' => $this->eventId],
            [
                'judul' => $this->judul,
                'deskripsi' => $this->deskripsi,
                'tanggal_mulai' => $this->tanggal_mulai,
                'jam_mulai' => $this->jam_mulai,
                'tanggal_selesai' => $this->tanggal_selesai,
                'jam_selesai' => $this->jam_selesai,
                'is_active' => $this->is_active,
                'created_by' => $this->eventId ? Event::find($this->eventId)->created_by : Auth::id(),
            ]
        );

        session()->flash('message', $this->eventId ? 'Event berhasil diupdate.' : 'Event berhasil dibuat.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $this->eventId = $event->id;
        $this->judul = $event->judul;
        $this->deskripsi = $event->deskripsi ?? '';
        $this->tanggal_mulai = $event->tanggal_mulai->format('Y-m-d');
        $this->jam_mulai = substr($event->jam_mulai, 0, 5);
        $this->tanggal_selesai = $event->tanggal_selesai->format('Y-m-d');
        $this->jam_selesai = substr($event->jam_selesai, 0, 5);
        $this->is_active = $event->is_active;
        $this->openModal();
    }

    public function toggleActive($id)
    {
        $event = Event::findOrFail($id);
        $event->is_active = !$event->is_active;
        $event->save();
        session()->flash('message', 'Status event berhasil diubah.');
    }

    public function delete($id)
    {
        Event::find($id)->delete();
        session()->flash('message', 'Event berhasil dihapus.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
