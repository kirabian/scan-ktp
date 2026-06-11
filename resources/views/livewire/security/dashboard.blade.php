<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="px-4 sm:px-0">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">Dashboard Security</h1>
        <p class="text-slate-500 mb-8 font-medium">Monitoring penerimaan sedekah per event.</p>

        {{-- Event Tabs --}}
        @if($events->count() > 0)
        <div class="flex flex-wrap gap-3 mb-8">
            @foreach($events as $ev)
                <button wire:click="selectEvent({{ $ev->id }})"
                    class="relative px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out {{ $selectedEventId == $ev->id ? 'bg-blue-600 text-white shadow-md shadow-blue-200 ring-2 ring-blue-600 ring-offset-2' : 'bg-white text-slate-600 border border-slate-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 hover:shadow-sm' }}">
                    {{ $ev->judul }}
                    @if($ev->isCurrentlyActive())
                        <span class="absolute -top-1 -right-1 inline-block w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    @endif
                </button>
            @endforeach
        </div>
        @endif

        {{-- Selected Event Stats --}}
        @if($selectedStats)
        <div class="mb-10 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-800 truncate">{{ $selectedStats['event']->judul }}</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                {{-- Total Masuk --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 lg:p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] lg:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">Total Scan</p>
                        <p class="text-3xl lg:text-4xl font-extrabold text-slate-800 mt-2 truncate">{{ $selectedStats['total_masuk'] }}</p>
                    </div>
                    <p class="text-xs lg:text-sm font-medium text-slate-500 mt-3">kali scan tercatat</p>
                </div>

                {{-- Warga Unik --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 lg:p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] lg:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-emerald-500 transition-colors">Warga Unik</p>
                        <p class="text-3xl lg:text-4xl font-extrabold text-slate-800 mt-2 truncate">{{ $selectedStats['total_unik'] }}</p>
                    </div>
                    <p class="text-xs lg:text-sm font-medium text-slate-500 mt-3">warga berbeda</p>
                </div>

                {{-- Pengambilan Ganda --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 lg:p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] lg:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-red-500 transition-colors">Ganda</p>
                        <p class="text-3xl lg:text-4xl font-extrabold text-slate-800 mt-2 truncate">{{ $selectedStats['total_ganda'] }}</p>
                    </div>
                    <p class="text-xs lg:text-sm font-medium text-slate-500 mt-3">scan berlebih</p>
                </div>

                {{-- Persentase --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 lg:p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] lg:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-purple-500 transition-colors">Partisipasi</p>
                        <p class="text-3xl lg:text-4xl font-extrabold text-slate-800 mt-2 truncate">{{ $selectedStats['persentase'] }}%</p>
                    </div>
                    <p class="text-xs lg:text-sm font-medium text-slate-500 mt-3">dari {{ $totalWarga }} warga</p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 lg:p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between sm:items-end mb-3 gap-2">
                    <span class="text-sm font-bold text-slate-600 uppercase tracking-wider">Progress Partisipasi</span>
                    <span class="text-base font-extrabold text-slate-800">{{ $selectedStats['total_unik'] }} <span class="text-sm font-medium text-slate-500">/ {{ $totalWarga }} warga</span></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-blue-600 h-3 rounded-full transition-all duration-1000 ease-out" style="width: {{ min($selectedStats['persentase'], 100) }}%"></div>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-xs font-semibold text-slate-400">0%</span>
                    <span class="text-xs font-bold text-blue-600">{{ $selectedStats['persentase'] }}%</span>
                    <span class="text-xs font-semibold text-slate-400">100%</span>
                </div>
            </div>

            {{-- Recent Log --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" wire:poll.10s>
                <div class="px-5 lg:px-6 py-4 lg:py-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight">Log Scan Terbaru</h3>
                    <span class="text-[10px] lg:text-xs font-semibold px-3 py-1 bg-white border border-slate-200 rounded-full text-slate-500 shadow-sm w-fit">30 Terakhir</span>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="px-4 lg:px-6 py-3 lg:py-4 text-left text-[10px] lg:text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Waktu</th>
                                <th scope="col" class="px-4 lg:px-6 py-3 lg:py-4 text-left text-[10px] lg:text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Warga</th>
                                <th scope="col" class="px-4 lg:px-6 py-3 lg:py-4 text-left text-[10px] lg:text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Petugas</th>
                                <th scope="col" class="px-4 lg:px-6 py-3 lg:py-4 text-left text-[10px] lg:text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-50">
                            @forelse($recentLogs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-150">
                                <td class="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                    <span class="text-xs lg:text-sm font-semibold text-slate-600 px-2 py-1 bg-slate-100 rounded-md">
                                        {{ $log->waktu_ambil->format('H:i:s') }}
                                    </span>
                                </td>
                                <td class="px-4 lg:px-6 py-3 lg:py-4">
                                    <div class="text-xs lg:text-sm font-bold text-slate-900 line-clamp-1">{{ $log->warga->nama ?? '-' }}</div>
                                    <div class="text-[10px] lg:text-xs font-medium text-slate-500 mt-0.5">{{ $log->warga->nik ?? '-' }}</div>
                                </td>
                                <td class="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                    <div class="text-xs lg:text-sm font-medium text-slate-600">
                                        {{ $log->petugasSecurity->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                    @if($log->foto_penerima_path)
                                        <span class="inline-flex items-center px-2 py-1 lg:px-2.5 rounded-full text-[10px] lg:text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            Ganda
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 lg:px-2.5 rounded-full text-[10px] lg:text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Normal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 lg:px-6 py-8 lg:py-12 text-center">
                                    <p class="text-xs lg:text-sm font-medium text-slate-400">Belum ada scan masuk untuk event ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 lg:p-16 text-center">
                <div class="w-14 h-14 lg:w-16 lg:h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-slate-300 font-bold text-xl lg:text-2xl">?</span>
                </div>
                <h3 class="text-base lg:text-lg font-bold text-slate-800 mb-2">Belum ada event aktif</h3>
                <p class="text-xs lg:text-sm text-slate-500 font-medium">Minta admin untuk membuat event terlebih dahulu.</p>
            </div>
        @endif

        {{-- Ringkasan Semua Event --}}
        @if($eventStats->count() > 1)
        <div class="mt-10 lg:mt-12">
            <h2 class="text-lg lg:text-xl font-bold text-slate-800 mb-5 lg:mb-6 tracking-tight">Ringkasan Semua Event</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                @foreach($eventStats as $stat)
                <button wire:click="selectEvent({{ $stat['event']->id }})"
                    class="group w-full bg-white rounded-2xl shadow-sm border {{ $selectedEventId == $stat['event']->id ? 'border-blue-500 ring-1 ring-blue-500' : 'border-slate-100' }} p-5 lg:p-6 text-left hover:shadow-md hover:border-slate-300 transition-all duration-300 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-4 w-full">
                        <p class="font-bold text-slate-900 text-sm lg:text-base group-hover:text-blue-600 transition-colors truncate w-full">{{ $stat['event']->judul }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 lg:gap-4 mt-auto w-full">
                        <div class="p-2.5 lg:p-3 bg-slate-50 rounded-xl group-hover:bg-blue-50/50 transition-colors">
                            <p class="text-[9px] lg:text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1 truncate">Masuk</p>
                            <p class="text-lg lg:text-xl font-extrabold text-slate-800 truncate">{{ $stat['total_masuk'] }}</p>
                        </div>
                        <div class="p-2.5 lg:p-3 bg-slate-50 rounded-xl group-hover:bg-emerald-50/50 transition-colors">
                            <p class="text-[9px] lg:text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1 truncate">Unik</p>
                            <p class="text-lg lg:text-xl font-extrabold text-slate-800 truncate">{{ $stat['total_unik'] }}</p>
                        </div>
                        <div class="p-2.5 lg:p-3 bg-slate-50 rounded-xl group-hover:bg-red-50/50 transition-colors">
                            <p class="text-[9px] lg:text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1 truncate">Ganda</p>
                            <p class="text-lg lg:text-xl font-extrabold text-slate-800 truncate">{{ $stat['total_ganda'] }}</p>
                        </div>
                        <div class="p-2.5 lg:p-3 bg-slate-50 rounded-xl group-hover:bg-purple-50/50 transition-colors">
                            <p class="text-[9px] lg:text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1 truncate">Partisipasi</p>
                            <p class="text-lg lg:text-xl font-extrabold text-slate-800 truncate">{{ $stat['persentase'] }}%</p>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
