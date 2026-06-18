<div class="max-w-2xl mx-auto py-2 sm:px-6 lg:px-8">
    <style>
        /* Override default ugly html5-qrcode styles */
        #qr-reader {
            border: none !important;
            border-radius: 12px;
            overflow: hidden;
        }
        #qr-reader__scan_region {
            background-color: #f1f5f9;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #qr-reader__dashboard_section_csr {
            padding: 16px !important;
            display: flex;
            flex-direction: column;
            gap: 10px;
            display: none !important; /* Hide camera selection entirely since we auto-start */
        }
        #qr-reader__dashboard_section_swaplink {
            display: none !important;
        }
        #qr-reader button {
            background-color: #4f46e5 !important;
            color: white !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
        }
        #qr-reader button:hover {
            background-color: #4338ca !important;
        }
        #qr-reader__status_span {
            font-size: 14px !important;
            color: #64748b !important;
        }
        /* Make video fill and hide ugly borders */
        #qr-reader video {
            object-fit: cover !important;
            border-radius: 12px !important;
        }
    </style>
    {{-- Event Selector (tampil jika ada >1 event aktif) --}}
    @if($showEventSelector && count($activeEvents) > 0)
    <div class="mb-4 bg-yellow-50 border border-yellow-300 rounded-2xl p-4 shadow-sm">
        <p class="text-sm font-bold text-yellow-800 mb-2">⚠️ Ada {{ count($activeEvents) }} event yang sedang berlangsung. Pilih event untuk scan:</p>
        <div class="space-y-2">
            @foreach($activeEvents as $ev)
            <button wire:click="selectEvent({{ $ev['id'] }})" class="w-full text-left bg-white hover:bg-yellow-100 border border-yellow-200 rounded-xl px-4 py-3 transition-colors">
                <span class="font-bold text-slate-800">{{ $ev['judul'] }}</span>
                <span class="block text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($ev['tanggal_mulai'])->format('d/m/Y') }} {{ substr($ev['jam_mulai'],0,5) }} - {{ \Carbon\Carbon::parse($ev['tanggal_selesai'])->format('d/m/Y') }} {{ substr($ev['jam_selesai'],0,5) }}</span>
            </button>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white shadow-sm border border-slate-200 overflow-hidden sm:rounded-2xl">
        <div class="px-4 py-3 sm:px-6 bg-blue-700 text-white text-center">
            <h3 class="text-lg leading-6 font-bold">Sistem Scan QR Code</h3>
            @if($currentEvent)
            <div class="mt-1 bg-blue-800 rounded-lg px-3 py-1 inline-block">
                <p class="text-xs font-bold text-white">Event: {{ $currentEvent['judul'] }}</p>
            </div>
            @endif
        </div>

        <div class="p-4 sm:p-6">
            @if (session()->has('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errorMessage && !$warga)
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <p class="font-bold mb-1">Peringatan:</p>
                    <p class="text-sm">{{ $errorMessage }}</p>
                </div>
            @endif

            <div class="{{ $warga ? 'hidden' : '' }}">
                <div class="text-center" id="scan-container">
                    <p class="mb-3 text-sm text-slate-600 font-medium">Arahkan kamera ke Barcode warga.</p>
                    
                    <div wire:ignore>
                        <div id="qr-reader" style="width: 100%; border-radius: 12px; overflow: hidden; border: 2px solid #e2e8f0; background: #000;"></div>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-center">
                        <div class="border-t border-slate-200 flex-grow"></div>
                        <span class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Atau</span>
                        <div class="border-t border-slate-200 flex-grow"></div>
                    </div>

                    <form wire:submit.prevent="searchManual" class="mt-4" wire:key="manual-search-form">
                        <label for="manual_nik" class="block text-sm font-bold text-gray-700 text-left mb-2">Input NIK Manual</label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" wire:key="manual-nik-input" wire:model.defer="manualNik" id="manual_nik" placeholder="Ketik 16 digit NIK..." class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2" required>
                            <button type="submit" wire:key="manual-nik-btn" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm hover:bg-blue-700 transition-colors flex items-center justify-center">
                                Cari Data
                            </button>
                        </div>
                        @error('manualNik') <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                    </form>
                </div>
            </div>

            @if ($warga)
                <div class="space-y-4">
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1 bg-slate-100 rounded-lg overflow-hidden h-24 flex items-center justify-center">
                            @if($warga->foto_wajah_path)
                                <img src="{{ route('secure.image', ['folder' => 'wajah', 'filename' => basename($warga->foto_wajah_path)]) }}" class="max-h-full object-cover">
                            @else
                                <span class="text-xs text-slate-400">No Wajah</span>
                            @endif
                        </div>
                        <div class="flex-1 bg-slate-100 rounded-lg overflow-hidden h-24 flex items-center justify-center">
                            @if($warga->foto_ktp_path)
                                <img src="{{ route('secure.image', ['folder' => 'ktp', 'filename' => basename($warga->foto_ktp_path)]) }}" class="max-h-full object-cover">
                            @else
                                <span class="text-xs text-slate-400">No KTP</span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Lengkap</p>
                        <p class="text-lg font-bold text-slate-800">
                            {{ $warga->nama }} 
                            <span class="text-sm font-bold text-indigo-600 ml-1 block sm:inline mt-1 sm:mt-0">
                                ({{ $warga->umur }} Thn - {{ $kategoriUsia }})
                            </span>
                        </p>
                        
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1 mt-3">Area KTP</p>
                        <p class="text-sm font-medium text-slate-700 leading-relaxed">
                            <span class="block">Desa: <strong>{{ $warga->kel_desa_ktp ?? '-' }}</strong></span>
                            <span class="block">Kecamatan: <strong>{{ $warga->kecamatan_ktp ?? '-' }}</strong></span>
                        </p>

                        @if($statusPengambilan)
                            <div class="mt-3 bg-yellow-100 p-2 rounded text-sm text-yellow-800 font-medium">
                                {{ $statusPengambilan }}
                            </div>
                        @else
                            <div class="mt-3 bg-green-100 p-2 rounded text-sm text-green-800 font-medium">
                                Belum pernah menerima sedekah.
                            </div>
                        @endif
                    </div>

                    @if(!$showConfirmation)
                        <div class="flex gap-3 pt-2">
                            <button wire:click="resetScan" class="flex-1 bg-slate-200 text-slate-800 py-3 rounded-xl font-bold hover:bg-slate-300 transition-colors">
                                Cancel
                            </button>
                            <button wire:click="handleMasuk" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-sm hover:bg-blue-700 transition-colors">
                                Masuk
                            </button>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 p-4 rounded-xl mt-4">
                            <p class="font-bold text-red-700 mb-2">Konfirmasi Pengambilan Ganda</p>
                            <p class="text-sm text-red-600 mb-4">{{ $warningMessage }}</p>
                            
                            <div class="flex gap-3">
                                <button wire:click="resetScan" class="flex-1 bg-white border border-slate-300 text-slate-700 py-2.5 rounded-xl font-bold hover:bg-slate-50">
                                    Tidak
                                </button>
                                
                                <label class="flex-1 cursor-pointer bg-red-600 text-white py-2.5 rounded-xl font-bold text-center hover:bg-red-700 flex items-center justify-center">
                                    Iya, Lanjut
                                    <input type="file" id="emergency-ktp-input" accept="image/*" capture="user" class="hidden" />
                                </label>
                            </div>
                            <div id="emergency-loading" class="hidden mt-3 text-center text-sm text-red-600 font-bold">
                                Mengunggah foto bukti...
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrCode = null;

        function onScanSuccess(decodedText, decodedResult) {
            // Pause scanner immediately
            if (html5QrCode && html5QrCode.getState() === 2) { // 2 = SCANNING
                html5QrCode.pause(true);
            }
            
            // Dispatch to Livewire
            Livewire.dispatch('qrScanned', { nik: decodedText });
        }

        function setupQrScanner() {
            if (document.getElementById('qr-reader')) {
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("qr-reader");
                }
                
                if (html5QrCode.getState() === 1) { // 1 = NOT_STARTED
                    html5QrCode.start(
                        { facingMode: "environment" }, // Paksa pakai kamera belakang
                        { fps: 10, qrbox: {width: 250, height: 250} },
                        onScanSuccess
                    ).catch(err => console.error(err));
                } else if (html5QrCode.getState() === 3) { // 3 = PAUSED
                    html5QrCode.resume();
                }
            }
        }

        function compressImage(file) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    const maxWidth = 1200;
                    let width = img.width;
                    let height = img.height;
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                    resolve(canvas.toDataURL('image/jpeg', 0.8));
                };
                img.src = URL.createObjectURL(file);
            });
        }

        function setupEmergencyCamera() {
            const emInput = document.getElementById('emergency-ktp-input');
            if (emInput) {
                emInput.onchange = async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    document.getElementById('emergency-loading').classList.remove('hidden');
                    
                    try {
                        const dataUrl = await compressImage(file);
                        @this.call('catatPengambilanGanda', dataUrl);
                    } catch (err) {
                        console.error(err);
                        document.getElementById('emergency-loading').classList.add('hidden');
                    }
                };
            }
        }

        document.addEventListener('livewire:initialized', () => {
            setupQrScanner();
            setupEmergencyCamera();
            
            Livewire.hook('morph.updated', () => {
                // Restart if DOM changed heavily
                setupQrScanner();
                setupEmergencyCamera();
            });

            Livewire.on('resetCamera', () => { 
                if (html5QrCode) {
                    try {
                        html5QrCode.resume();
                    } catch (e) {
                        console.log("Kamera mungkin belum di-pause atau sudah aktif", e);
                    }
                }
                setupEmergencyCamera();
            });
        });
    </script>
</div>
