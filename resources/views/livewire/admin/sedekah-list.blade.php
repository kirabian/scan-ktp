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
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status / Bukti Ganda</th>
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
                                    @if($h->foto_penerima_path)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-2">
                                            Pengambilan Ganda
                                        </span><br>
                                        <a href="{{ route('secure.image', ['folder' => 'darurat', 'filename' => basename($h->foto_penerima_path)]) }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-bold underline text-xs">
                                            Lihat Foto Bukti Live
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Normal
                                        </span>
                                    @endif
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
</div>
