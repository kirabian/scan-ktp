<div class="max-w-md mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm border border-slate-200 overflow-hidden sm:rounded-2xl">
        <div class="px-4 py-5 sm:px-6 bg-blue-700 text-white text-center">
            <h3 class="text-xl leading-6 font-bold">Sistem Scan KTP</h3>
            <p class="mt-1 text-sm text-blue-100">Petugas Security</p>
        </div>

        <div class="p-6">
            @if (session()->has('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errorMessage && !$warga)
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <p class="font-bold mb-1">Peringatan:</p>
                    <p class="text-sm">{{ $errorMessage }}</p>
                    <p class="text-sm mt-2 font-medium">Jika NIK salah baca, silakan perbaiki di kolom Manual di bawah ini.</p>
                </div>
            @endif

            @if (!$warga)
                <div class="text-center" id="scan-container">
                    <p class="mb-4 text-slate-600">Ambil foto KTP warga untuk mengecek status dan data.</p>
                    
                    <label class="cursor-pointer inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-sm w-full transition-colors">
                        Scan KTP Warga
                        <input type="file" id="ktp-input" accept="image/*" capture="environment" class="hidden" />
                    </label>
                    
                    <div id="loading-indicator" class="hidden mt-4">
                        <div id="ocr-status-text" class="inline-flex items-center text-blue-600 font-medium">Memproses OCR KTP...</div>
                        <div id="ocr-debug-info" class="mt-2 text-xs text-slate-500 font-mono hidden bg-slate-100 p-2.5 rounded-lg max-h-40 overflow-y-auto whitespace-pre-wrap border border-slate-200 text-left"></div>
                    </div>

                    <div class="mt-6 flex items-center justify-center">
                        <div class="border-t border-slate-200 flex-grow"></div>
                        <span class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Atau</span>
                        <div class="border-t border-slate-200 flex-grow"></div>
                    </div>

                    <form wire:submit.prevent="searchManual" class="mt-6">
                        <label for="manual_nik" class="block text-sm font-bold text-gray-700 text-left mb-2">Input NIK Manual</label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" wire:model="manualNik" id="manual_nik" placeholder="Ketik 16 digit NIK..." class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3" required>
                            <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-sm hover:bg-blue-700 transition-colors flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                                Cari Data
                            </button>
                        </div>
                        @error('manualNik') <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                    </form>
                </div>
            @endif

            @if ($warga)
                <div class="space-y-4">
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1 bg-slate-100 rounded-lg overflow-hidden h-32 flex items-center justify-center">
                            @if($warga->foto_wajah_path)
                                <img src="{{ route('secure.image', ['folder' => 'wajah', 'filename' => basename($warga->foto_wajah_path)]) }}" class="max-h-full object-cover">
                            @else
                                <span class="text-xs text-slate-400">No Wajah</span>
                            @endif
                        </div>
                        <div class="flex-1 bg-slate-100 rounded-lg overflow-hidden h-32 flex items-center justify-center">
                            @if($warga->foto_ktp_path)
                                <img src="{{ route('secure.image', ['folder' => 'ktp', 'filename' => basename($warga->foto_ktp_path)]) }}" class="max-h-full object-cover">
                            @else
                                <span class="text-xs text-slate-400">No KTP</span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Lengkap</p>
                        <p class="text-lg font-bold text-slate-800">{{ $warga->nama }}</p>
                        
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1 mt-3">Alamat Domisili</p>
                        <p class="text-sm font-medium text-slate-700">
                            @if($warga->is_domisili_sesuai_ktp)
                                {{ $warga->alamat_ktp }} (Sesuai KTP)
                            @else
                                {{ $warga->alamat_detail_domisili }}, {{ $warga->kel_desa_domisili }}, {{ $warga->kecamatan_domisili }}
                            @endif
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

    <script>
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

        function setupKtpScanner() {
            const ktpInput = document.getElementById('ktp-input');
            if (ktpInput) {
                ktpInput.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const statusText = document.getElementById('ocr-status-text');
                    const debugEl = document.getElementById('ocr-debug-info');
                    if (statusText) {
                        statusText.innerText = "Mengompresi gambar...";
                        statusText.className = "inline-flex items-center text-blue-600 font-medium";
                    }
                    if (debugEl) {
                        debugEl.innerText = "";
                        debugEl.classList.add('hidden');
                    }
                    document.getElementById('loading-indicator').classList.remove('hidden');

                    try {
                        const compressedDataUrl = await compressImage(file);
                        const resImage = await fetch(compressedDataUrl);
                        const blob = await resImage.blob();

                        if (statusText) {
                            statusText.innerText = "Mengunggah & Memproses OCR...";
                        }

                        const formData = new FormData();
                        formData.append('foto_ktp', blob, 'ktp_compressed.jpg');
                        formData.append('is_security', '1');

                        const response = await fetch("{{ route('ocr.ktp') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const result = await response.json();

                        if (debugEl && result.raw_ocr_text) {
                            debugEl.innerText = "Raw OCR Text:\n" + result.raw_ocr_text;
                            debugEl.classList.remove('hidden');
                        }
                        
                        if (result.success && result.nik && !result.nik.startsWith('RAW:')) {
                            Livewire.dispatch('nikScanned', { nik: result.nik });
                        } else {
                            let rawNik = result.nik ? result.nik.replace('RAW:', '').trim() : '';
                            if (rawNik) {
                                @this.set('manualNik', rawNik);
                                alert("NIK dari foto kurang jelas/tidak lengkap (" + rawNik + "). Angka telah dimasukkan ke kolom Input Manual, silakan lengkapi/koreksi menjadi 16 digit lalu klik Cari Data.");
                            } else {
                                alert("Gagal membaca NIK otomatis dari KTP. Silakan potret ulang atau ketik NIK secara manual.");
                            }
                            document.getElementById('loading-indicator').classList.add('hidden');
                            ktpInput.value = '';
                        }
                    } catch (err) {
                        console.error(err);
                        alert("Terjadi kesalahan sistem: " + err.message);
                        document.getElementById('loading-indicator').classList.add('hidden');
                        ktpInput.value = '';
                    }
                });
            }
        }

        function setupEmergencyCamera() {
            const emInput = document.getElementById('emergency-ktp-input');
            if (emInput) {
                emInput.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    document.getElementById('emergency-loading').classList.remove('hidden');
                    
                    try {
                        const dataUrl = await compressImage(file);
                        // Kirim dataUrl ke Livewire
                        @this.call('catatPengambilanGanda', dataUrl);
                    } catch (err) {
                        console.error(err);
                        document.getElementById('emergency-loading').classList.add('hidden');
                    }
                });
            }
        }

        document.addEventListener('livewire:initialized', () => {
            setupKtpScanner();
            setupEmergencyCamera();
            Livewire.hook('morph.updated', () => {
                setupKtpScanner();
                setupEmergencyCamera();
            });
            Livewire.on('resetCamera', () => { 
                const debugEl = document.getElementById('ocr-debug-info');
                if (debugEl) {
                    debugEl.innerText = "";
                    debugEl.classList.add('hidden');
                }
                const statusText = document.getElementById('ocr-status-text');
                if (statusText) {
                    statusText.innerText = "Memproses OCR KTP... Mohon tunggu.";
                    statusText.className = "inline-flex items-center text-blue-600 font-medium";
                }
                const indicator = document.getElementById('loading-indicator');
                if (indicator) {
                    indicator.classList.add('hidden');
                }
                setTimeout(() => {
                    setupKtpScanner(); 
                    setupEmergencyCamera();
                }, 100); 
            });
        });
    </script>
</div>