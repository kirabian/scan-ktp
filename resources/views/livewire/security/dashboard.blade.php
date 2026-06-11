<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8 bg-slate-50 min-h-screen">
    {{-- Header --}}
    <div class="mb-6 px-4 sm:px-0">
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Security</h1>
        <p class="text-sm text-slate-500">Monitoring penerimaan sedekah per event.</p>
    </div>

    {{-- Tabs --}}
    @if($events->count() > 0)
    <div class="flex flex-wrap gap-3 mb-8 px-4 sm:px-0">
        @foreach($events as $ev)
            <button wire:click="selectEvent({{ $ev->id }})"
                class="px-5 py-2 rounded-full text-sm font-semibold transition-colors {{ $selectedEventId == $ev->id ? 'bg-blue-700 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $ev->judul }}
            </button>
        @endforeach
    </div>
    @endif

    {{-- Selected Event Stats --}}
    @if($selectedStats)
    <div class="px-4 sm:px-0 mb-8">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">{{ $selectedStats['event']->judul }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
            {{-- Total Scan --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Scan</p>
                <p class="text-3xl font-bold text-blue-700">{{ $selectedStats['total_masuk'] }}</p>
                <p class="text-xs text-slate-400 mt-2">kali scan tercatat</p>
            </div>
            
            {{-- Warga Unik --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Warga Unik</p>
                <p class="text-3xl font-bold text-blue-700">{{ $selectedStats['total_unik'] }}</p>
                <p class="text-xs text-slate-400 mt-2">warga berbeda</p>
            </div>
            
            {{-- Ganda --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ganda</p>
                <p class="text-3xl font-bold text-red-600">{{ $selectedStats['total_ganda'] }}</p>
                <p class="text-xs text-slate-400 mt-2">scan berlebih</p>
            </div>
            
            {{-- Partisipasi --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Partisipasi</p>
                <p class="text-3xl font-bold text-blue-700">{{ $selectedStats['persentase'] }}%</p>
                <p class="text-xs text-slate-400 mt-2">dari {{ $totalWarga }} warga</p>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Progress Partisipasi</span>
                <span class="text-sm font-bold text-blue-700">{{ $selectedStats['total_unik'] }} / {{ $totalWarga }} warga</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
                <div class="bg-blue-700 h-2 rounded-full transition-all duration-1000" style="width: {{ min($selectedStats['persentase'], 100) }}%"></div>
            </div>
            <div class="flex justify-between mt-2">
                <span class="text-xs font-bold text-blue-700">0%</span>
                <span class="text-xs text-slate-500">100%</span>
            </div>
        </div>

        {{-- Recent Log --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8" wire:poll.10s>
            <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Log Scan Terbaru</h3>
                    <p class="text-xs text-slate-500 mt-1">Daftar aktivitas pemindaian kartu peserta secara real-time.</p>
                </div>
                <div class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-blue-100 w-fit">
                    Filter: 30 Terakhir
                </div>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Waktu</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Warga</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Petugas</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-50">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-600">
                                {{ $log->waktu_ambil->format('H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800">{{ $log->warga->nama ?? '-' }}</div>
                                <div class="text-xs font-medium text-slate-500 mt-0.5">{{ $log->warga->nik ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-600">
                                {{ $log->petugasSecurity->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->foto_penerima_path)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                        Ganda
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                </div>
                                <p class="text-base font-bold text-slate-800">Belum ada scan masuk untuk event ini.</p>
                                <p class="text-sm text-slate-500 mt-1">Aktivitas pemindaian akan muncul di sini secara otomatis.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center mb-8 mx-4 sm:mx-0">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-slate-200">
                <span class="text-slate-400 font-bold text-2xl">?</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada event aktif</h3>
            <p class="text-sm text-slate-500 mb-2">Minta admin untuk membuat event terlebih dahulu untuk memulai pemantauan real-time.</p>
        </div>
    @endif

    {{-- Ringkasan Semua Event --}}
    @if($eventStats->count() > 1)
    <div class="px-4 sm:px-0">
        <div class="flex justify-between items-end mb-4">
            <h2 class="text-lg font-bold text-slate-800">Ringkasan Semua Event</h2>
            <a href="#" class="text-xs font-bold text-blue-700 uppercase hover:underline">Lihat Detail Semua</a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
            @foreach($eventStats as $stat)
            <button wire:click="selectEvent({{ $stat['event']->id }})"
                class="block w-full text-left bg-white rounded-2xl border {{ $selectedEventId == $stat['event']->id ? 'border-blue-600 ring-1 ring-blue-600 shadow-sm' : 'border-slate-200 shadow-sm hover:border-slate-300' }} p-6 transition-all">
                <div class="flex justify-between items-center mb-6">
                    <p class="font-bold text-blue-700 text-lg">{{ $stat['event']->judul }}</p>
                    @if($stat['event']->isCurrentlyActive())
                        <span class="bg-blue-700 text-white text-[10px] font-bold px-3 py-1 rounded-md uppercase tracking-wider">Aktif</span>
                    @else
                        <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-3 py-1 rounded-md uppercase tracking-wider">Selesai</span>
                    @endif
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:gap-5">
                    <div class="bg-slate-50/80 p-5 lg:p-6 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2">Masuk</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $stat['total_masuk'] }}</p>
                    </div>
                    <div class="bg-slate-50/80 p-5 lg:p-6 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2">Unik</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $stat['total_unik'] }}</p>
                    </div>
                    <div class="bg-slate-50/80 p-5 lg:p-6 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2">Ganda</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $stat['total_ganda'] }}</p>
                    </div>
                    <div class="bg-slate-50/80 p-5 lg:p-6 rounded-xl border border-slate-100 flex flex-col justify-between">
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2">Partisipasi</p>
                            <p class="text-2xl font-bold text-slate-800 mb-3">{{ $stat['persentase'] }}%</p>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1.5 mt-auto">
                            <div class="bg-slate-400 h-1.5 rounded-full" style="width: {{ min($stat['persentase'], 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </button>
            @endforeach
        </div>
    </div>
    @endif
</div>
