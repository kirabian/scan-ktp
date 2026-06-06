<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard Admin</h1>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
                <h3 class="text-gray-500 text-sm font-medium">Total Warga Terdaftar</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalWarga }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
                <h3 class="text-gray-500 text-sm font-medium">Total Sedekah Tersalurkan Hari Ini</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalSedekahHariIni }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 border-t-4 border-red-500">
                <h3 class="text-gray-500 text-sm font-medium">Total Kasus Ambil Ganda Hari Ini</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalKasusGandaHariIni }}</p>
            </div>
        </div>

        <!-- Real-time History Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden" wire:poll.10s>
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Log Histori Real-time (10 detik auto-refresh)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK & Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Security</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($logHistori as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->waktu_ambil->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $log->warga->nama }}</div>
                                <div class="text-sm text-gray-500">{{ $log->warga->nik }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->petugasSecurity->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($log->foto_penerima_path)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ⚠️ Pengambilan Ganda
                                    </span>
                                    <a href="/secure/{{ $log->foto_penerima_path }}" target="_blank" class="text-blue-600 hover:text-blue-900 ml-2">Lihat Foto Bukti</a>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Pengambilan Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Belum ada log histori.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
