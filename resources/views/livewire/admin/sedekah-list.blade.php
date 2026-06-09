<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Data Riwayat Sedekah</h1>

        <div class="flex justify-end items-center mb-4">
            <input type="text" wire:model.live="search" placeholder="Cari NIK atau Nama Warga..." class="border-slate-300 rounded-xl shadow-sm focus:ring focus:ring-blue-600 focus:border-blue-600 w-72">
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu Ambil</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Warga</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Petugas Security</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse ($histori as $h)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">
                                    {{ $h->waktu_ambil->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $h->warga->nik }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">
                                    {{ $h->warga->nama }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $h->petugasSecurity->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button wire:click="showDetail({{ $h->id }})" class="bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-200 transition-colors">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                    Belum ada data riwayat sedekah yang sesuai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($histori->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    {{ $histori->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Detail Sedekah -->
    @if($showModal && $selectedHistori)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-5 border-b pb-3">
                        <div>
                            <h3 class="text-xl leading-6 font-bold text-slate-900" id="modal-title">
                                Detail Riwayat Sedekah
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">ID Transaksi: #{{ $selectedHistori->id }}</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-500 focus:outline-none bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Informasi Teks -->
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Waktu & Petugas</h4>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                                        <div class="text-slate-500 font-medium">Tanggal</div>
                                        <div class="col-span-2 font-bold text-slate-800">{{ $selectedHistori->waktu_ambil->format('d F Y') }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                                        <div class="text-slate-500 font-medium">Jam</div>
                                        <div class="col-span-2 font-bold text-slate-800">{{ $selectedHistori->waktu_ambil->format('H:i') }} WIB</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-sm">
                                        <div class="text-slate-500 font-medium">Petugas</div>
                                        <div class="col-span-2 font-bold text-slate-800">{{ $selectedHistori->petugasSecurity->name ?? 'Tidak Diketahui' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Warga</h4>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                                        <div class="text-slate-500 font-medium">Nama</div>
                                        <div class="col-span-2 font-bold text-slate-800">{{ $selectedHistori->warga->nama }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                                        <div class="text-slate-500 font-medium">NIK</div>
                                        <div class="col-span-2 font-bold text-slate-800">{{ $selectedHistori->warga->nik }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-sm">
                                        <div class="text-slate-500 font-medium">No. HP</div>
                                        <div class="col-span-2 font-bold text-slate-800">{{ $selectedHistori->warga->no_wa_hp }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status Pengambilan</h4>
                                @if($selectedHistori->foto_penerima_path)
                                    <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                                        <p class="text-sm font-bold text-red-700">Peringatan: PENGAMBILAN GANDA</p>
                                        <p class="text-xs text-red-600 mt-1">Warga ini mengambil sedekah lebih dari 1 kali di hari yang sama. Foto bukti pengambilan darurat disertakan di sebelah kanan.</p>
                                    </div>
                                @else
                                    <div class="bg-green-50 p-4 rounded-xl border border-green-200">
                                        <p class="text-sm font-bold text-green-700">Pengambilan Normal</p>
                                        <p class="text-xs text-green-600 mt-1">Ini adalah pengambilan pertama warga pada hari tersebut.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Galeri Foto -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Galeri Bukti & Wajah</h4>
                            <div class="space-y-4">
                                @if($selectedHistori->foto_penerima_path)
                                    <div class="bg-white rounded-xl border border-red-300 overflow-hidden shadow-sm">
                                        <div class="bg-red-50 px-3 py-2 border-b border-red-200 flex justify-between items-center">
                                            <span class="text-xs font-bold text-red-700">FOTO BUKTI PENGAMBILAN GANDA SAAT ITU</span>
                                        </div>
                                        <div class="h-40 bg-slate-100 flex items-center justify-center p-2">
                                            <img src="{{ route('secure.image', ['folder' => 'darurat', 'filename' => basename($selectedHistori->foto_penerima_path)]) }}" class="max-h-full max-w-full rounded shadow-sm object-contain">
                                        </div>
                                    </div>
                                @endif

                                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                                    <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex justify-between items-center">
                                        <span class="text-xs font-bold text-slate-700">FOTO WAJAH DARI DATABASE WARGA</span>
                                    </div>
                                    <div class="h-40 bg-slate-100 flex items-center justify-center p-2">
                                        @if($selectedHistori->warga->foto_wajah_path)
                                            <img src="{{ route('secure.image', ['folder' => 'wajah', 'filename' => basename($selectedHistori->warga->wajah_path ?? $selectedHistori->warga->foto_wajah_path)]) }}" class="max-h-full max-w-full rounded shadow-sm object-contain">
                                        @else
                                            <span class="text-xs text-slate-400">Tidak ada foto wajah</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
