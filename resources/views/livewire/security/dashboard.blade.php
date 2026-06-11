<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">Dashboard Security</h1>
        <p class="text-slate-500 mb-6">Monitoring penerimaan sedekah per event.</p>

        {{-- Event Tabs --}}
        @if($events->count() > 0)
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($events as $ev)
                <button wire:click="selectEvent({{ $ev->id }})"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ $selectedEventId == $ev->id ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                    {{ $ev->judul }}
                    @if($ev->isCurrentlyActive())
                        <span class="ml-1 inline-block w-2 h-2 rounded-full bg-green-400"></span>
                    @endif
                </button>
            @endforeach
        </div>
        @endif

        {{-- Selected Event Stats --}}
        @if($selectedStats)
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-700 mb-3">{{ $selectedStats['event']->judul }}</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                {{-- Total Masuk --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Scan Masuk</p>
                    <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ $selectedStats['total_masuk'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">kali scan tercatat</p>
                </div>

                {{-- Warga Unik --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Warga Unik Masuk</p>
                    <p class="text-3xl font-extrabold text-green-600 mt-1">{{ $selectedStats['total_unik'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">warga berbeda</p>
                </div>

                {{-- Pengambilan Ganda --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pengambilan Ganda</p>
                    <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $selectedStats['total_ganda'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">scan berlebih</p>
                </div>

                {{-- Persentase --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Partipasi Warga</p>
                    <p class="text-3xl font-extrabold text-purple-600 mt-1">{{ $selectedStats['persentase'] }}%</p>
                    <p class="text-xs text-slate-500 mt-1">dari {{ $totalWarga }} warga</p>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-bold text-slate-700">Progress Partipasi</span>
                    <span class="text-sm font-extrabold text-blue-600">{{ $selectedStats['total_unik'] }} / {{ $totalWarga }} warga</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-500" style="width: {{ min($selectedStats['persentase'], 100) }}%"></div>
                </div>
                <div class="flex justify-between mt-1.5">
                    <span class="text-xs text-slate-400">0%</span>
                    <span class="text-xs font-bold text-slate-500">{{ $selectedStats['persentase'] }}%</span>
                    <span class="text-xs text-slate-400">100%</span>
                </div>
            </div>

            {{-- Recent Log --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" wire:poll.10s>
                <div class="px-5 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-700">Log Scan Terbaru (30 terakhir)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase">Waktu</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase">NIK & Nama</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase">Petugas</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse($recentLogs as $log)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700 font-medium">
                                    {{ $log->waktu_ambil->format('H:i:s') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-800">{{ $log->warga->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->warga->nik ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">
                                    {{ $log->petugasSecurity->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if($log->foto_penerima_path)
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold">Ganda</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">
                                    Belum ada scan masuk untuk event ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <p class="text-slate-400 text-lg">Belum ada event aktif. Minta admin untuk membuat event terlebih dahulu.</p>
            </div>
        @endif

        {{-- Ringkasan Semua Event --}}
        @if($eventStats->count() > 1)
        <div class="mt-8">
            <h2 class="text-lg font-bold text-slate-700 mb-3">Ringkasan Semua Event</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($eventStats as $stat)
                <button wire:click="selectEvent({{ $stat['event']->id }})"
                    class="bg-white rounded-xl shadow-sm border {{ $selectedEventId == $stat['event']->id ? 'border-blue-400 ring-2 ring-blue-100' : 'border-slate-200' }} p-5 text-left hover:shadow-md transition-shadow">
                    <p class="font-bold text-slate-800 text-sm mb-3">{{ $stat['event']->judul }}</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-slate-400 font-bold">Masuk</p>
                            <p class="text-xl font-extrabold text-blue-600">{{ $stat['total_masuk'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold">Warga Unik</p>
                            <p class="text-xl font-extrabold text-green-600">{{ $stat['total_unik'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold">Ganda</p>
                            <p class="text-xl font-extrabold text-red-600">{{ $stat['total_ganda'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-bold">Partipasi</p>
                            <p class="text-xl font-extrabold text-purple-600">{{ $stat['persentase'] }}%</p>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
