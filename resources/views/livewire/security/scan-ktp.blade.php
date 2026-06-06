<div class="max-w-md mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-blue-600 text-white text-center">
            <h3 class="text-lg leading-6 font-medium">Scan KTP Sembako</h3>
            <p class="mt-1 text-sm text-blue-100">Petugas Security Gateway</p>
        </div>

        <div class="p-6">
            @if (session()->has('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (!$warga && !$errorMessage)
                <div class="text-center" id="scan-container">
                    <p class="mb-4 text-gray-600">Ambil foto KTP warga untuk mengecek status sedekah.</p>
                    
                    <label class="cursor-pointer inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm w-full">
                        Ambil Foto KTP
                        <input type="file" id="ktp-input" accept="image/*" capture="environment" class="hidden" />
                    </label>
                    
                    <div id="loading-indicator" class="hidden mt-4">
                        <div class="inline-flex items-center text-blue-600 font-medium">Memproses OCR KTP...</div>
                    </div>
                </div>
            @endif

            @if ($errorMessage && !$warga)
                 @endif

            @if ($warga)
                 @endif
        </div>
    </div>

    <script>
        // ... include fungsi compressImage() yang sama ...

        function setupKtpScanner() {
            const ktpInput = document.getElementById('ktp-input');
            if (ktpInput) {
                ktpInput.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    document.getElementById('loading-indicator').classList.remove('hidden');

                    try {
                        const compressedDataUrl = await compressImage(file);
                        const resImage = await fetch(compressedDataUrl);
                        const blob = await resImage.blob();

                        const formData = new FormData();
                        formData.append('foto_ktp', blob, 'ktp_compressed.jpg');

                        const response = await fetch("{{ route('ocr.ktp') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const result = await response.json();
                        
                        if (result.success && result.nik) {
                            // KOREKSI DI SINI: Tembakkan langsung ke event listener Livewire ScanKtp Component
                            Livewire.dispatch('nikScanned', { nik: result.nik });
                        } else {
                            alert("Gagal membaca NIK otomatis. Silakan posisikan KTP lebih dekat tanpa silau lampu.");
                            document.getElementById('loading-indicator').classList.add('hidden');
                            ktpInput.value = '';
                        }
                    } catch (err) {
                        console.error(err);
                        document.getElementById('loading-indicator').classList.add('hidden');
                    }
                });
            }
        }

        document.addEventListener('livewire:initialized', () => {
            setupKtpScanner();
            Livewire.on('resetCamera', () => { setTimeout(setupKtpScanner, 100); });
        });
    </script>
</div>