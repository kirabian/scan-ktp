<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">List Data Warga</h1>
        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex justify-end items-center mb-4">
            <input type="text" wire:model.live="search" placeholder="Cari NIK atau Nama..." class="border-slate-300 rounded-xl shadow-sm focus:ring focus:ring-blue-600 focus:border-blue-600 w-64">
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L/P</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat (KTP)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. HP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($wargas as $warga)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $warga->nik }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $warga->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $warga->jenis_kelamin }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $warga->alamat_ktp }}, RT/RW {{ $warga->rt_rw_ktp }}, {{ $warga->kel_desa_ktp }}, {{ $warga->kecamatan_ktp }}">
                                    {{ $warga->alamat_ktp }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $warga->no_wa_hp }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="viewDetails({{ $warga->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Lihat Detail & Foto</button>
                                    @if(Auth::user()->role === 'admin')
                                        <button wire:click="deleteWarga({{ $warga->id }})" wire:confirm="Yakin ingin menghapus data warga ini beserta fotonya? Tindakan ini tidak bisa dibatalkan." class="text-red-600 hover:text-red-900">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Data Warga Kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50">
                {{ $wargas->links() }}
            </div>
        </div>

        @if($isModalOpen && $selectedWarga)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-5 border-b pb-2">
                            <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                                Detail Data Warga
                            </h3>
                            <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Data Info -->
                            <div>
                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Informasi Pribadi</h4>
                                <table class="w-full text-sm">
                                    <tbody>
                                        <tr><td class="py-1 font-medium text-gray-600 w-1/3">NIK</td><td class="py-1 font-bold">{{ $selectedWarga->nik }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">Nama</td><td class="py-1">{{ $selectedWarga->nama }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">TTL</td><td class="py-1">{{ $selectedWarga->tempat_tgl_lahir }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">Jenis Kelamin</td><td class="py-1">{{ $selectedWarga->jenis_kelamin }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">Pekerjaan</td><td class="py-1">{{ $selectedWarga->pekerjaan }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">No WhatsApp/HP</td><td class="py-1">{{ $selectedWarga->no_wa_hp }}</td></tr>
                                    </tbody>
                                </table>

                                <h4 class="font-semibold text-gray-700 border-b pb-1 mt-5 mb-3">Alamat KTP</h4>
                                <p class="text-sm">
                                    {{ $selectedWarga->alamat_ktp }}<br>
                                    RT/RW: {{ $selectedWarga->rt_rw_ktp }}<br>
                                    Kel/Desa: {{ $selectedWarga->kel_desa_ktp }}<br>
                                    Kecamatan: {{ $selectedWarga->kecamatan_ktp }}
                                </p>

                                <h4 class="font-semibold text-gray-700 border-b pb-1 mt-5 mb-3">Alamat Domisili</h4>
                                @if($selectedWarga->is_domisili_sesuai_ktp)
                                    <p class="text-sm italic text-gray-500">Sesuai KTP</p>
                                @else
                                    <p class="text-sm">
                                        {{ $selectedWarga->alamat_detail_domisili }}<br>
                                        Kel/Desa: {{ $selectedWarga->kel_desa_domisili }}, Kec: {{ $selectedWarga->kecamatan_domisili }}<br>
                                        Kab/Kota: {{ $selectedWarga->kota_kab_domisili }}, Prov: {{ $selectedWarga->provinsi_domisili }}<br>
                                        Kode Pos: {{ $selectedWarga->kode_pos_domisili }}
                                    </p>
                                @endif
                            </div>

                            <!-- Photos -->
                            <div>
                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Foto KTP</h4>
                                <div class="bg-gray-100 rounded border border-gray-200 h-48 flex items-center justify-center mb-4 overflow-hidden">
                                    @if($selectedWarga->foto_ktp_path)
                                        <img src="{{ route('secure.image', ['folder' => 'ktp', 'filename' => basename($selectedWarga->foto_ktp_path)]) }}" alt="Foto KTP" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-gray-400">Tidak ada foto KTP</span>
                                    @endif
                                </div>

                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Foto Wajah</h4>
                                <div class="bg-gray-100 rounded border border-gray-200 h-48 flex items-center justify-center overflow-hidden">
                                    @if($selectedWarga->foto_wajah_path)
                                        <img src="{{ route('secure.image', ['folder' => 'wajah', 'filename' => basename($selectedWarga->foto_wajah_path)]) }}" alt="Foto Wajah" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-gray-400">Tidak ada foto wajah</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
