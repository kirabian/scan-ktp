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
                <h2 class="text-xl font-bold text-slate-800">{{ $selectedStats['event']->judul }}</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Masuk --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-blue-500 transition-colors">Total Scan Masuk</p>
                    <p class="text-4xl font-extrabold text-slate-800 mt-2">{{ $selectedStats['total_masuk'] }}</p>
                    <p class="text-sm font-medium text-slate-500 mt-2">kali scan tercatat</p>
                </div>

                {{-- Warga Unik --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-emerald-500 transition-colors">Warga Unik Masuk</p>
                    <p class="text-4xl font-extrabold text-slate-800 mt-2">{{ $selectedStats['total_unik'] }}</p>
                    <p class="text-sm font-medium text-slate-500 mt-2">warga berbeda</p>
                </div>

                {{-- Pengambilan Ganda --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-red-500 transition-colors">Pengambilan Ganda</p>
                    <p class="text-4xl font-extrabold text-slate-800 mt-2">{{ $selectedStats['total_ganda'] }}</p>
                    <p class="text-sm font-medium text-slate-500 mt-2">scan berlebih</p>
                </div>

                {{-- Persentase --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md hover:border-slate-200 transition-all duration-300 group">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-purple-500 transition-colors">Partisipasi Warga</p>
                    <p class="text-4xl font-extrabold text-slate-800 mt-2">{{ $selectedStats['persentase'] }}%</p>
                    <p class="text-sm font-medium text-slate-500 mt-2">dari {{ $totalWarga }} warga</p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
                <div class="flex justify-between items-end mb-3">
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
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight">Log Scan Terbaru</h3>
                    <span class="text-xs font-semibold px-3 py-1 bg-white border border-slate-200 rounded-full text-slate-500 shadow-sm">30 Terakhir</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Waktu</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Warga</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Petugas</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-50">
                            @forelse($recentLogs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-slate-600 px-2 py-1 bg-slate-100 rounded-md">
                                        {{ $log->waktu_ambil->format('H:i:s') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ $log->warga->nama ?? '-' }}</div>
                                    <div class="text-xs font-medium text-slate-500 mt-0.5">{{ $log->warga->nik ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-600">
                                        {{ $log->petugasSecurity->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->foto_penerima_path)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            Ganda
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Normal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <p class="text-sm font-medium text-slate-400">Belum ada scan masuk untuk event ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-slate-300 font-bold text-2xl">?</span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Belum ada event aktif</h3>
                <p class="text-slate-500 font-medium">Minta admin untuk membuat event terlebih dahulu.</p>
            </div>
        @endif

        {{-- Ringkasan Semua Event --}}
        @if($eventStats->count() > 1)
        <div class="mt-12">
            <h2 class="text-xl font-bold text-slate-800 mb-6 tracking-tight">Ringkasan Semua Event</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($eventStats as $stat)
                <button wire:click="selectEvent({{ $stat['event']->id }})"
                    class="group bg-white rounded-2xl shadow-sm border {{ $selectedEventId == $stat['event']->id ? 'border-blue-500 ring-1 ring-blue-500' : 'border-slate-100' }} p-6 text-left hover:shadow-md hover:border-slate-300 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <p class="font-bold text-slate-900 text-base group-hover:text-blue-600 transition-colors">{{ $stat['event']->judul }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-blue-50/50 transition-colors">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Masuk</p>
                            <p class="text-xl font-extrabold text-slate-800">{{ $stat['total_masuk'] }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-emerald-50/50 transition-colors">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Unik</p>
                            <p class="text-xl font-extrabold text-slate-800">{{ $stat['total_unik'] }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-red-50/50 transition-colors">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Ganda</p>
                            <p class="text-xl font-extrabold text-slate-800">{{ $stat['total_ganda'] }}</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl group-hover:bg-purple-50/50 transition-colors">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Partisipasi</p>
                            <p class="text-xl font-extrabold text-slate-800">{{ $stat['persentase'] }}%</p>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
