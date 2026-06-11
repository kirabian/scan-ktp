<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8 bg-slate-50/50 min-h-screen">
    <div class="px-4 sm:px-0">
        <div class="border-b border-slate-200 pb-5 mb-8">
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Security</h1>
            <p class="mt-2 text-sm text-slate-500">Monitoring penerimaan sedekah secara real-time.</p>
        </div>

        {{-- Event Tabs --}}
        @if($events->count() > 0)
        <div class="mb-8">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Pilih Event</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($events as $ev)
                    <button wire:click="selectEvent({{ $ev->id }})"
                        class="relative px-6 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 border {{ $selectedEventId == $ev->id ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-slate-600 border-slate-300 hover:border-slate-400 hover:bg-slate-50' }}">
                        {{ $ev->judul }}
                        @if($ev->isCurrentlyActive())
                            <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Selected Event Stats --}}
        @if($selectedStats)
        <div class="mb-10">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-800">{{ $selectedStats['event']->judul }}</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                    {{-- Total Masuk --}}
                    <div class="p-6 bg-white hover:bg-blue-50/30 transition-colors">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Scan</p>
                        <div class="flex items-baseline">
                            <p class="text-4xl font-extrabold text-blue-600">{{ $selectedStats['total_masuk'] }}</p>
                            <p class="ml-2 text-sm font-medium text-slate-500">kali</p>
                        </div>
                    </div>

                    {{-- Warga Unik --}}
                    <div class="p-6 bg-white hover:bg-emerald-50/30 transition-colors">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Warga Unik</p>
                        <div class="flex items-baseline">
                            <p class="text-4xl font-extrabold text-emerald-600">{{ $selectedStats['total_unik'] }}</p>
                            <p class="ml-2 text-sm font-medium text-slate-500">warga</p>
                        </div>
                    </div>

                    {{-- Pengambilan Ganda --}}
                    <div class="p-6 bg-white hover:bg-red-50/30 transition-colors">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ganda</p>
                        <div class="flex items-baseline">
                            <p class="text-4xl font-extrabold text-red-600">{{ $selectedStats['total_ganda'] }}</p>
                            <p class="ml-2 text-sm font-medium text-slate-500">kasus</p>
                        </div>
                    </div>

                    {{-- Persentase --}}
                    <div class="p-6 bg-white hover:bg-purple-50/30 transition-colors">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Partisipasi</p>
                        <div class="flex items-baseline">
                            <p class="text-4xl font-extrabold text-purple-600">{{ $selectedStats['persentase'] }}%</p>
                            <p class="ml-2 text-sm font-medium text-slate-500">dari {{ $totalWarga }}</p>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Progress Target Warga</span>
                        <span class="text-sm font-bold text-slate-700">{{ $selectedStats['total_unik'] }} / {{ $totalWarga }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-1000" style="width: {{ min($selectedStats['persentase'], 100) }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Recent Log --}}
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" wire:poll.10s>
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                    <h3 class="text-base font-bold text-slate-800">Log Scan Terbaru</h3>
                    <span class="text-xs font-bold bg-white px-2 py-1 border border-slate-200 rounded-md text-slate-500">30 Terakhir</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Warga</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Petugas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse($recentLogs as $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ $log->waktu_ambil->format('H:i:s') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $log->warga->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->warga->nik ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-700">
                                        {{ $log->petugasSecurity->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->foto_penerima_path)
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-red-100 text-red-700">
                                            Ganda
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-700">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <p class="text-sm text-slate-500">Belum ada scan masuk untuk event ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center mx-auto mb-4 border border-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Event Aktif</h3>
                <p class="text-sm text-slate-500">Silakan hubungi administrator untuk membuat event baru.</p>
            </div>
        @endif

        {{-- Ringkasan Semua Event --}}
        @if($eventStats->count() > 1)
        <div class="mt-10">
            <h2 class="text-base font-bold text-slate-800 mb-4">Ringkasan Semua Event</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($eventStats as $stat)
                <button wire:click="selectEvent({{ $stat['event']->id }})"
                    class="block w-full text-left bg-white rounded-xl shadow-sm border {{ $selectedEventId == $stat['event']->id ? 'border-blue-500 ring-1 ring-blue-500' : 'border-slate-200' }} overflow-hidden hover:border-slate-300 transition-colors">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                        <p class="font-bold text-slate-800 text-sm truncate">{{ $stat['event']->judul }}</p>
                    </div>
                    <div class="p-5 grid grid-cols-2 gap-y-4 gap-x-2">
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Masuk</p>
                            <p class="text-lg font-bold text-slate-800">{{ $stat['total_masuk'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Unik</p>
                            <p class="text-lg font-bold text-slate-800">{{ $stat['total_unik'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Ganda</p>
                            <p class="text-lg font-bold text-red-600">{{ $stat['total_ganda'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Partisipasi</p>
                            <p class="text-lg font-bold text-slate-800">{{ $stat['persentase'] }}%</p>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
