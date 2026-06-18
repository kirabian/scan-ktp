<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Data Download QR Code</h1>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-1">Total Warga</div>
                <div class="text-3xl font-bold text-slate-800">{{ $totalWarga }}</div>
                <div class="text-sm text-slate-500 mt-2">Seluruh data warga yang terdaftar</div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-1">Sudah Download</div>
                <div class="flex items-baseline">
                    <div class="text-3xl font-bold text-blue-600">{{ $sudahDownload }}</div>
                    <div class="text-slate-500 ml-2 font-medium">/ {{ $totalWarga }} Warga</div>
                </div>
                <div class="text-sm text-slate-500 mt-2">Warga yang telah mengunduh QR Code</div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-1">Double Download</div>
                <div class="text-3xl font-bold text-amber-500">{{ $doubleDownload }}</div>
                <div class="text-sm text-slate-500 mt-2">Warga yang mengunduh lebih dari 1 kali</div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Riwayat Download Terakhir</h2>
            <input type="text" wire:model.live="search" placeholder="Cari NIK atau Nama..." class="border-slate-300 rounded-xl shadow-sm focus:ring focus:ring-blue-600 focus:border-blue-600 w-64">
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="overflow-x-auto w-full" style="-webkit-overflow-scrolling: touch;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Terakhir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Download</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($wargas as $warga)
                            <tr wire:key="warga-row-{{ $warga->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $warga->last_qr_download_at ? $warga->last_qr_download_at->translatedFormat('d M Y H:i:s') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $warga->nik }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $warga->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $warga->qr_download_count > 1 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $warga->qr_download_count }}x
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada riwayat unduhan QR Code.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50">
                {{ $wargas->links() }}
            </div>
        </div>
    </div>
</div>
