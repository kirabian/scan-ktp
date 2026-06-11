<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Manajemen Event</h1>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
            <button wire:click="create()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-colors">
                + Tambah Event Baru
            </button>
            <input type="text" wire:model.live="search" placeholder="Cari event..." class="border-slate-300 rounded-xl shadow-sm focus:ring focus:ring-blue-600 focus:border-blue-600 w-full sm:w-72">
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pembuat</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse ($events as $event)
                            <tr wire:key="event-row-{{ $event->id }}">
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold text-slate-800">{{ $event->judul }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5 line-clamp-1">{{ $event->deskripsi ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <div>{{ $event->tanggal_mulai->format('d/m/Y') }} {{ substr($event->jam_mulai,0,5) }}</div>
                                    <div class="text-xs text-slate-400">s/d {{ $event->tanggal_selesai->format('d/m/Y') }} {{ substr($event->jam_selesai,0,5) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $isActive = $event->is_active && $event->isCurrentlyActive();
                                        $isUpcoming = $event->is_active && !$event->isCurrentlyActive() && now()->lt(\Carbon\Carbon::parse($event->tanggal_mulai->format('Y-m-d').' '.$event->jam_mulai));
                                    @endphp
                                    @if($isActive)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Berlangsung</span>
                                    @elseif($isUpcoming)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800">Akan Datang</span>
                                    @elseif($event->is_active)
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-slate-100 text-slate-600">Selesai</span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-700">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $event->createdBy->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                    <button type="button" wire:click="edit({{ $event->id }})" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition-colors">Edit</button>
                                    <button type="button" wire:click="toggleActive({{ $event->id }})" class="text-yellow-700 hover:text-yellow-900 font-bold text-xs bg-yellow-50 hover:bg-yellow-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                        {{ $event->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    <button type="button" wire:click="delete({{ $event->id }})" wire:confirm="Yakin ingin menghapus event ini? Semua data sedekah terkait akan kehilangan referensi event." class="text-red-600 hover:text-red-800 font-bold text-xs bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition-colors">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">Belum ada event. Klik tombol "Tambah Event Baru" untuk membuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

        @if($isModalOpen)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                    <form wire:submit.prevent="store">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <h3 class="text-xl leading-6 font-bold text-slate-900 mb-5" id="modal-title">
                                {{ $eventId ? 'Edit Event' : 'Tambah Event Baru' }}
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="judul" class="block text-sm font-bold text-slate-700 mb-1">Judul Event</label>
                                    <input type="text" id="judul" wire:model="judul" placeholder="Contoh: Sedekah Ramadhan 2026" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5">
                                    @error('judul') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="deskripsi" class="block text-sm font-bold text-slate-700 mb-1">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
                                    <textarea id="deskripsi" wire:model="deskripsi" rows="2" placeholder="Deskripsi singkat event..." class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5"></textarea>
                                    @error('deskripsi') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                                        <input type="date" wire:model="tanggal_mulai" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5">
                                        @error('tanggal_mulai') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1">Jam Mulai</label>
                                        <input type="time" wire:model="jam_mulai" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5">
                                        @error('jam_mulai') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                                        <input type="date" wire:model="tanggal_selesai" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5">
                                        @error('tanggal_selesai') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1">Jam Selesai</label>
                                        <input type="time" wire:model="jam_selesai" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5">
                                        @error('jam_selesai') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <label for="is_active" class="text-sm font-bold text-slate-700">Aktifkan Event</label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-200">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                {{ $eventId ? 'Update Event' : 'Buat Event' }}
                            </button>
                            <button type="button" wire:click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
