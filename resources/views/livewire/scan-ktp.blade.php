<div>
    {{-- Tesseract.js CDN --}}
    @assets
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    @endassets

    <div class="min-h-screen bg-slate-50 pb-8">
        {{-- Header --}}
        <div class="bg-blue-700 text-white px-4 py-5 shadow-sm">
            <div class="max-w-lg mx-auto">
                <h1 class="text-xl font-bold text-center">Sistem Scan KTP</h1>
                <p class="text-blue-100 text-xs text-center mt-1">Arahkan kamera ke KTP warga</p>
            </div>
        </div>

        <div class="max-w-lg mx-auto px-4 mt-5 space-y-5">

            {{-- Result Message --}}
            @if($message)
            <div id="resultMessage" class="rounded-xl p-4 shadow-md border-l-4 animate-fade-in
                @if($messageType === 'success') bg-green-50 border-green-500
                @elseif($messageType === 'warning') bg-yellow-50 border-yellow-500
                @else bg-red-50 border-red-500
                @endif">
                <div class="flex items-start gap-3">
                    @if($messageType === 'success')
                        <span class="text-3xl text-green-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                    @elseif($messageType === 'warning')
                        <span class="text-3xl text-yellow-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </span>
                    @else
                        <span class="text-3xl text-red-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </span>
                    @endif
                    <div>
                        <p class="font-semibold text-sm
                            @if($messageType === 'success') text-green-800
                            @elseif($messageType === 'warning') text-yellow-800
                            @else text-red-800
                            @endif">
                            {{ $message }}
                        </p>
                        @if($wargaNama && $messageType === 'success')
                        <p class="text-green-600 text-xs mt-1">Nama: <strong>{{ $wargaNama }}</strong></p>
                        @endif
                    </div>
                </div>
                @if($messageType === 'success' || $messageType === 'error')
                <button wire:click="resetScan" class="mt-3 w-full bg-blue-600 text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-blue-700 transition-colors">
                    Scan KTP Berikutnya
                </button>
                @endif
            </div>
            @endif

            {{-- Camera Section --}}
            @if(!$message || $messageType === 'warning')
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gray-800 px-4 py-3 flex items-center justify-between">
                    <span class="text-white text-sm font-medium">Ambil Foto KTP</span>
                    <span id="ocrStatus" class="text-xs text-gray-400">Siap</span>
                </div>

                {{-- Camera Preview Area --}}
                <div class="relative bg-gray-900 aspect-[8.56/5.398] flex items-center justify-center overflow-hidden">
                    {{-- Image Preview --}}
                    <img id="ktpPreview" src="" alt="" class="hidden w-full h-full object-contain" />

                    {{-- Placeholder --}}
                    <div id="cameraPlaceholder" class="text-center text-gray-400 p-6">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-sm">Tap tombol di bawah untuk foto KTP</p>
                    </div>

                    {{-- KTP Bounding Box Overlay --}}
                    <div id="ktpOverlay" class="hidden absolute inset-0 pointer-events-none">
                        <div class="absolute inset-3 border-2 border-dashed border-yellow-400 rounded-lg"></div>
                        <div class="absolute top-1 left-1/2 -translate-x-1/2 bg-yellow-400 text-gray-900 text-[10px] font-bold px-2 py-0.5 rounded-b">POSISI KTP</div>
                    </div>
                </div>

                {{-- Capture Button --}}
                <div class="p-4 bg-gray-50">
                    <label for="ktpFileInput" class="block w-full bg-blue-600 text-white rounded-xl py-3 text-center text-sm font-semibold cursor-pointer hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-sm">
                        Ambil Foto KTP dari Kamera
                    </label>
                    <input
                        type="file"
                        id="ktpFileInput"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        onchange="handleKtpCapture(this)"
                    />
                </div>

                {{-- OCR Progress --}}
                <div id="ocrProgressContainer" class="hidden px-4 pb-4">
                    <div class="bg-gray-100 rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="animate-spin w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                            <span id="ocrProgressText" class="text-sm text-gray-700 font-medium">Memproses OCR...</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="ocrProgressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5" id="ocrProgressPercent">0%</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Review Form (shown after OCR) --}}
            <div id="reviewForm" class="hidden">
                <form wire:submit="verifikasiDanKonfirmasi">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-green-600 px-4 py-3">
                            <span class="text-white text-sm font-medium">Review Data KTP</span>
                        </div>

                        <div class="p-4 space-y-3">
                            {{-- NIK --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">NIK <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    wire:model="nik"
                                    id="nikInput"
                                    maxlength="16"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    placeholder="16 digit NIK"
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-lg font-mono font-bold tracking-wider text-center focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                />
                                @error('nik')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nama --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama</label>
                                <input type="text" wire:model="nama" id="namaInput"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                            </div>

                            {{-- Tempat & Tanggal Lahir --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tempat Lahir</label>
                                    <input type="text" wire:model="tempat_lahir" id="tempatLahirInput"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Lahir</label>
                                    <input type="text" wire:model="tanggal_lahir" id="tanggalLahirInput" placeholder="DD-MM-YYYY"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                                </div>
                            </div>

                            {{-- Jenis Kelamin --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Kelamin</label>
                                <select wire:model="jenis_kelamin" id="jenisKelaminInput"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
                                    <option value="">-- Pilih --</option>
                                    <option value="LAKI-LAKI">LAKI-LAKI</option>
                                    <option value="PEREMPUAN">PEREMPUAN</option>
                                </select>
                            </div>

                            {{-- Alamat --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat</label>
                                <textarea wire:model="alamat" id="alamatInput" rows="2"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200"></textarea>
                            </div>

                            {{-- RT / RW --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">RT</label>
                                    <input type="text" wire:model="rt" id="rtInput" maxlength="5"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">RW</label>
                                    <input type="text" wire:model="rw" id="rwInput" maxlength="5"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                                </div>
                            </div>

                            {{-- Kelurahan & Kecamatan --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kel/Desa</label>
                                    <input type="text" wire:model="kelurahan_desa" id="kelurahanDesaInput"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kecamatan</label>
                                    <input type="text" wire:model="kecamatan" id="kecamatanInput"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-200" />
                                </div>
                            </div>
                        </div>

                        {{-- Foto KTP Upload (hidden, auto-filled) --}}
                        <input type="file" wire:model="foto_ktp" id="fotoKtpLivewire" class="hidden" />
                        @error('foto_ktp')
                        <p class="text-red-500 text-xs px-4 pb-2">{{ $message }}</p>
                        @enderror

                        {{-- Submit Button --}}
                        <div class="p-4 bg-gray-50">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="w-full bg-green-600 text-white rounded-xl py-3.5 text-sm font-bold shadow hover:bg-green-700 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="verifikasiDanKonfirmasi">
                                    Verifikasi & Konfirmasi Ambil
                                </span>
                                <span wire:loading wire:target="verifikasiDanKonfirmasi" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>

                            <button
                                type="button"
                                onclick="resetAll()"
                                wire:click="resetScan"
                                class="w-full mt-2 bg-gray-200 text-gray-700 rounded-xl py-2.5 text-sm font-medium hover:bg-gray-300 transition-colors"
                            >
                                Ulang Scan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Manual NIK Entry (always accessible) --}}
            @if(!$message || $messageType === 'warning')
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <button onclick="toggleManualEntry()" class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors border-b border-gray-100">
                    <span class="text-sm font-medium text-gray-700">Input NIK Manual (tanpa OCR)</span>
                    <svg id="manualChevron" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="manualEntrySection" class="hidden p-4">
                    <p class="text-xs text-gray-500 mb-3">Masukkan NIK secara manual jika kamera/OCR tidak berfungsi</p>
                    <input
                        type="text"
                        id="manualNikInput"
                        maxlength="16"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="Ketik 16 digit NIK di sini"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-lg font-mono font-bold tracking-wider text-center focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                    />
                    <button onclick="useManualNik()" class="w-full mt-3 bg-blue-600 text-white rounded-xl py-3 text-sm font-semibold hover:bg-blue-700 transition-colors">
                        Gunakan NIK Ini
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>

    @script
    <script>
        // ============================================
        // Tesseract.js OCR Integration
        // ============================================

        let ocrWorker = null;

        /**
         * Handle KTP image capture from camera
         */
        async function handleKtpCapture(input) {
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            const preview = document.getElementById('ktpPreview');
            const placeholder = document.getElementById('cameraPlaceholder');
            const overlay = document.getElementById('ktpOverlay');

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                overlay.classList.add('hidden');
            };
            reader.readAsDataURL(file);

            // Assign the file to Livewire's foto_ktp input
            const livewireInput = document.getElementById('fotoKtpLivewire');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            livewireInput.files = dataTransfer.files;
            livewireInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Run OCR
            await processOCR(file);
        }

        /**
         * Preprocess image using Canvas for better OCR accuracy
         */
        function preprocessImage(file) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Scale to max 2000px for OCR speed vs quality balance
                    let w = img.width;
                    let h = img.height;
                    const maxDim = 2000;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) { h = Math.round(h * maxDim / w); w = maxDim; }
                        else { w = Math.round(w * maxDim / h); h = maxDim; }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    ctx.drawImage(img, 0, 0, w, h);

                    // Convert to grayscale + increase contrast
                    const imageData = ctx.getImageData(0, 0, w, h);
                    const data = imageData.data;
                    for (let i = 0; i < data.length; i += 4) {
                        // Grayscale
                        const gray = 0.299 * data[i] + 0.587 * data[i+1] + 0.114 * data[i+2];
                        // Contrast enhancement (factor 1.5)
                        const contrast = ((gray / 255 - 0.5) * 1.5 + 0.5) * 255;
                        const val = Math.max(0, Math.min(255, contrast));
                        data[i] = data[i+1] = data[i+2] = val;
                    }
                    ctx.putImageData(imageData, 0, 0);

                    canvas.toBlob(resolve, 'image/jpeg', 0.95);
                };
                img.src = URL.createObjectURL(file);
            });
        }

        /**
         * Process OCR using Tesseract.js
         */
        async function processOCR(file) {
            const progressContainer = document.getElementById('ocrProgressContainer');
            const progressBar = document.getElementById('ocrProgressBar');
            const progressText = document.getElementById('ocrProgressText');
            const progressPercent = document.getElementById('ocrProgressPercent');
            const statusEl = document.getElementById('ocrStatus');

            // Show progress
            progressContainer.classList.remove('hidden');
            statusEl.textContent = 'Memproses...';
            statusEl.classList.remove('text-gray-400');
            statusEl.classList.add('text-yellow-400');

            try {
                // Preprocess image
                progressText.textContent = 'Menyiapkan gambar...';
                const processedBlob = await preprocessImage(file);

                // Create Tesseract worker
                progressText.textContent = 'Memuat engine OCR...';
                progressBar.style.width = '10%';
                progressPercent.textContent = '10%';

                const worker = await Tesseract.createWorker('ind', 1, {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const pct = Math.round(10 + m.progress * 80);
                            progressBar.style.width = pct + '%';
                            progressPercent.textContent = pct + '%';
                            progressText.textContent = 'Membaca teks KTP...';
                        }
                    }
                });

                // Recognize text
                const { data: { text } } = await worker.recognize(processedBlob);
                await worker.terminate();

                progressBar.style.width = '100%';
                progressPercent.textContent = '100%';
                progressText.textContent = 'Selesai! Mengurai data...';

                // Parse KTP fields
                const parsed = parseKtpText(text);
                fillFormFields(parsed);

                // Show review form
                document.getElementById('reviewForm').classList.remove('hidden');
                progressContainer.classList.add('hidden');
                statusEl.textContent = 'OCR Selesai ✓';
                statusEl.classList.remove('text-yellow-400');
                statusEl.classList.add('text-green-400');

            } catch (error) {
                console.error('OCR Error:', error);
                progressText.textContent = 'Gagal membaca KTP. Silakan input manual.';
                progressBar.style.width = '100%';
                progressBar.classList.remove('bg-blue-600');
                progressBar.classList.add('bg-red-500');
                statusEl.textContent = 'Gagal ✗';
                statusEl.classList.remove('text-yellow-400');
                statusEl.classList.add('text-red-400');

                // Still show form for manual input
                document.getElementById('reviewForm').classList.remove('hidden');
                setTimeout(() => { progressContainer.classList.add('hidden'); }, 2000);
            }
        }

        /**
         * Parse extracted OCR text into KTP fields
         */
        function parseKtpText(text) {
            const result = {
                nik: '',
                nama: '',
                tempatLahir: '',
                tanggalLahir: '',
                jenisKelamin: '',
                alamat: '',
                rt: '',
                rw: '',
                kelurahanDesa: '',
                kecamatan: ''
            };

            // Normalize text
            const lines = text.replace(/\r/g, '').split('\n').map(l => l.trim()).filter(l => l);
            const fullText = text.toUpperCase();

            // 1. NIK: find 16-digit number
            const nikMatches = text.match(/\b(\d{16})\b/g);
            if (nikMatches) {
                // Pick the first valid 16-digit number
                result.nik = nikMatches[0];
            } else {
                // Try to find a sequence close to 16 digits (OCR errors)
                const nearMatch = text.match(/\b(\d{14,18})\b/);
                if (nearMatch) result.nik = nearMatch[1].substring(0, 16);
            }

            // 2. Parse each line looking for labels
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].toUpperCase();
                const lineOriginal = lines[i];

                // Nama
                if (/^NAMA\s*[:\.]?\s*/i.test(line)) {
                    result.nama = lineOriginal.replace(/^NAMA\s*[:\.]?\s*/i, '').trim();
                }

                // Tempat/Tgl Lahir
                if (/TEMPAT.*LAHIR|TGL.*LAHIR|TTL/i.test(line)) {
                    const val = lineOriginal.replace(/^.*(?:TEMPAT\/?TGL\s*LAHIR|TEMPAT.*LAHIR|TTL)\s*[:\.]?\s*/i, '').trim();
                    const parts = val.split(/[,]/);
                    if (parts.length >= 2) {
                        result.tempatLahir = parts[0].trim();
                        result.tanggalLahir = parts.slice(1).join(',').trim();
                    } else {
                        result.tempatLahir = val;
                    }
                }

                // Jenis Kelamin
                if (/JENIS\s*KELAMIN|JNS\s*KELAMIN/i.test(line)) {
                    if (/LAKI/i.test(line)) result.jenisKelamin = 'LAKI-LAKI';
                    else if (/PEREMPUAN|WANITA/i.test(line)) result.jenisKelamin = 'PEREMPUAN';
                }

                // Alamat
                if (/^ALAMAT\s*[:\.]?\s*/i.test(line)) {
                    result.alamat = lineOriginal.replace(/^ALAMAT\s*[:\.]?\s*/i, '').trim();
                    // Sometimes address spans multiple lines before RT/RW
                    for (let j = i + 1; j < lines.length && j <= i + 2; j++) {
                        if (/^RT|^RW|^KEL|^KEC|^AGAMA|^STATUS|^PEKERJAAN/i.test(lines[j].toUpperCase())) break;
                        result.alamat += ' ' + lines[j].trim();
                    }
                }

                // RT/RW
                const rtRwMatch = line.match(/RT\s*[/.\s:]*\s*(\d{1,3})\s*[/.\s]*\s*RW\s*[/.\s:]*\s*(\d{1,3})/i);
                if (rtRwMatch) {
                    result.rt = rtRwMatch[1].padStart(3, '0');
                    result.rw = rtRwMatch[2].padStart(3, '0');
                }

                // Kel/Desa
                if (/^KEL\s*[\/.]?\s*DESA|^KELURAHAN|^DESA/i.test(line)) {
                    result.kelurahanDesa = lineOriginal.replace(/^(?:KEL\s*[\/.]?\s*DESA|KELURAHAN|DESA)\s*[:\.]?\s*/i, '').trim();
                }

                // Kecamatan
                if (/^KECAMATAN|^KEC\s*[:\.]?/i.test(line)) {
                    result.kecamatan = lineOriginal.replace(/^(?:KECAMATAN|KEC)\s*[:\.]?\s*/i, '').trim();
                }
            }

            // Fallback: try to detect gender from full text if not found
            if (!result.jenisKelamin) {
                if (/LAKI[\s-]*LAKI/i.test(fullText)) result.jenisKelamin = 'LAKI-LAKI';
                else if (/PEREMPUAN/i.test(fullText)) result.jenisKelamin = 'PEREMPUAN';
            }

            return result;
        }

        /**
         * Fill form fields with parsed OCR data
         */
        function fillFormFields(parsed) {
            setLivewireField('nik', parsed.nik);
            setLivewireField('nama', parsed.nama);
            setLivewireField('tempat_lahir', parsed.tempatLahir);
            setLivewireField('tanggal_lahir', parsed.tanggalLahir);
            setLivewireField('jenis_kelamin', parsed.jenisKelamin);
            setLivewireField('alamat', parsed.alamat);
            setLivewireField('rt', parsed.rt);
            setLivewireField('rw', parsed.rw);
            setLivewireField('kelurahan_desa', parsed.kelurahanDesa);
            setLivewireField('kecamatan', parsed.kecamatan);
        }

        /**
         * Set Livewire component field value
         */
        function setLivewireField(field, value) {
            $wire.set(field, value || '');
        }

        /**
         * Toggle manual NIK entry section
         */
        function toggleManualEntry() {
            const section = document.getElementById('manualEntrySection');
            const chevron = document.getElementById('manualChevron');
            section.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }

        /**
         * Use manually entered NIK
         */
        function useManualNik() {
            const nik = document.getElementById('manualNikInput').value.trim();
            if (nik.length !== 16 || !/^\d{16}$/.test(nik)) {
                alert('NIK harus tepat 16 digit angka!');
                return;
            }
            setLivewireField('nik', nik);
            document.getElementById('reviewForm').classList.remove('hidden');
            // Scroll to form
            document.getElementById('reviewForm').scrollIntoView({ behavior: 'smooth' });
        }

        /**
         * Reset all UI state
         */
        function resetAll() {
            const preview = document.getElementById('ktpPreview');
            const placeholder = document.getElementById('cameraPlaceholder');
            const reviewForm = document.getElementById('reviewForm');
            const progressContainer = document.getElementById('ocrProgressContainer');
            const statusEl = document.getElementById('ocrStatus');

            if (preview) { preview.classList.add('hidden'); preview.src = ''; }
            if (placeholder) placeholder.classList.remove('hidden');
            if (reviewForm) reviewForm.classList.add('hidden');
            if (progressContainer) progressContainer.classList.add('hidden');
            if (statusEl) { statusEl.textContent = 'Siap'; statusEl.className = 'text-xs text-gray-400'; }

            const fileInput = document.getElementById('ktpFileInput');
            if (fileInput) fileInput.value = '';
        }

        // Listen for Livewire component reset
        $wire.on('resetScan', () => resetAll());
    </script>
    @endscript

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</div>
