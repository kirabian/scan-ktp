<div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Registrasi Warga Baru</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Input data warga untuk penerimaan sedekah.</p>
            </div>
        </div>

        <div class="p-6 border-t border-gray-200">
            @if($successMessage)
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $successMessage }}</span>
                </div>
            @endif

            <div wire:ignore class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h4 class="text-md font-medium text-blue-800 mb-2">Scan KTP Awal via OCR</h4>
                <p class="text-sm text-blue-600 mb-4">Foto KTP untuk mengisi seluruh form secara otomatis.</p>
                <input type="file" id="ocr-ktp" accept="image/*" capture="environment" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100 mb-2
                "/>
                <div id="ocr-status" class="text-sm font-bold text-blue-600 hidden">Memproses KTP... Mohon tunggu.</div>
                <div id="ocr-preview-container" class="mt-4 hidden">
                    <p class="text-xs text-gray-500 mb-1">Preview KTP:</p>
                    <img id="ocr-preview-image" src="" alt="Preview KTP" class="max-h-48 rounded-lg shadow-sm border border-gray-200">
                </div>
            </div>

            <form wire:submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label for="nik" class="block text-sm font-medium text-gray-700">NIK (16 Digit)</label>
                        <input type="text" wire:model="nik" id="nik" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                        @error('nik') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap Sesuai KTP</label>
                        <input type="text" wire:model="nama" id="nama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label for="tempat_tgl_lahir" class="block text-sm font-medium text-gray-700">Tempat, Tgl Lahir</label>
                        <input type="text" wire:model="tempat_tgl_lahir" id="tempat_tgl_lahir" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select wire:model="jenis_kelamin" id="jenis_kelamin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Pilih</option>
                            <option value="LAKI-LAKI">LAKI-LAKI</option>
                            <option value="PEREMPUAN">PEREMPUAN</option>
                        </select>
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-4">
                        <h4 class="text-md font-medium text-gray-900 border-b pb-2 mb-4">Data Alamat KTP</h4>
                    </div>
                    
                    <div class="col-span-1 md:col-span-2">
                        <label for="alamat_ktp" class="block text-sm font-medium text-gray-700">Alamat KTP Lengkap</label>
                        <textarea wire:model="alamat_ktp" id="alamat_ktp" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required></textarea>
                    </div>

                    <div>
                        <label for="rt_rw_ktp" class="block text-sm font-medium text-gray-700">RT/RW KTP</label>
                        <input type="text" wire:model="rt_rw_ktp" id="rt_rw_ktp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="kel_desa_ktp" class="block text-sm font-medium text-gray-700">Kelurahan/Desa KTP</label>
                        <input type="text" wire:model="kel_desa_ktp" id="kel_desa_ktp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="kecamatan_ktp" class="block text-sm font-medium text-gray-700">Kecamatan KTP</label>
                        <input type="text" wire:model="kecamatan_ktp" id="kecamatan_ktp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-4">
                        <h4 class="text-md font-medium text-gray-900 border-b pb-2 mb-4">Data Alamat Domisili</h4>
                        <div class="flex items-center mb-4">
                            <input wire:model.live="is_domisili_sesuai_ktp" id="is_domisili_sesuai_ktp" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_domisili_sesuai_ktp" class="ml-2 block text-sm text-gray-900">Alamat Domisili Sesuai KTP</label>
                        </div>
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-4">
                        <h4 class="text-md font-medium text-gray-900 border-b pb-2 mb-4">Kontak & Pekerjaan</h4>
                    </div>

                    <div>
                        <label for="no_wa_hp" class="block text-sm font-medium text-gray-700">No. WA / HP</label>
                        <input type="text" wire:model="no_wa_hp" id="no_wa_hp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="pekerjaan" class="block text-sm font-medium text-gray-700">Pekerjaan</label>
                        <input type="text" wire:model="pekerjaan" id="pekerjaan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-4">
                        <h4 class="text-md font-medium text-gray-900 border-b pb-2 mb-4">Unggah Foto Dokumen</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto KTP (Wajib Berwarna)</label>
                        <input type="file" wire:model="foto_ktp" accept="image/*" capture="environment" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Orang/Wajah</label>
                        <input type="file" wire:model="foto_wajah" accept="image/*" capture="user" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-200 pt-5">
                    <button type="submit" class="w-full inline-flex justify-center py-3 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Simpan Data Warga
                    </button>
                </div>
            </form>
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

        function isImageColored(file) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const size = 100;
                    canvas.width = size;
                    canvas.height = size;
                    ctx.drawImage(img, 0, 0, size, size);
                    
                    const imageData = ctx.getImageData(0, 0, size, size);
                    const data = imageData.data;
                    
                    let colorVarianceScore = 0;
                    let validPixels = 0;
                    
                    for (let i = 0; i < data.length; i += 4) {
                        const r = data[i];
                        const g = data[i+1];
                        const b = data[i+2];
                        
                        // Abaikan background putih terang atau hitam pekat agar perhitungan lebih akurat pada objek utama
                        if ((r > 240 && g > 240 && b > 240) || (r < 15 && g < 15 && b < 15)) continue;
                        
                        const max = Math.max(r, g, b);
                        const min = Math.min(r, g, b);
                        colorVarianceScore += (max - min);
                        validPixels++;
                    }
                    
                    // KTP Berwarna (biru/merah/ada garuda kuning) rata-rata variansi di atas 15
                    // Fotokopi hitam putih rata-rata variansi RGB < 5
                    const avgVariance = validPixels === 0 ? 0 : (colorVarianceScore / validPixels);
                    resolve(avgVariance > 10);
                };
                img.src = URL.createObjectURL(file);
            });
        }

        document.addEventListener('livewire:initialized', () => {
            const ktpInput = document.getElementById('ocr-ktp');
            if (ktpInput) {
                ktpInput.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Tampilkan preview foto KTP
                    const previewContainer = document.getElementById('ocr-preview-container');
                    const previewImage = document.getElementById('ocr-preview-image');
                    if (previewContainer && previewImage) {
                        previewImage.src = URL.createObjectURL(file);
                        previewContainer.classList.remove('hidden');
                    }

                    // Validasi KTP Berwarna (bukan fotokopi hitam putih)
                    const isColored = await isImageColored(file);
                    if (!isColored) {
                        alert("MOHON MAAF: KTP yang Anda foto terdeteksi sebagai fotokopi (Hitam-Putih).\n\nSistem wajib menggunakan KTP ASLI dan Berwarna. Silakan ulangi foto menggunakan KTP Asli.");
                        e.target.value = ''; // Reset file input ocr
                        if (previewContainer) previewContainer.classList.add('hidden');
                        return; // Hentikan proses OCR
                    }

                    // Otomatis pindahkan file ke input "Foto KTP" yang ada di bawah
                    const fotoKtpInput = document.querySelector('[wire\\:model="foto_ktp"]');
                    if (fotoKtpInput) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fotoKtpInput.files = dataTransfer.files;
                        fotoKtpInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const statusEl = document.getElementById('ocr-status');
                    statusEl.classList.remove('hidden');
                    statusEl.innerText = "Mengompresi gambar...";
                    statusEl.className = "text-sm font-bold text-blue-600";

                    try {
                        const compressedDataUrl = await compressImage(file);
                        const resImage = await fetch(compressedDataUrl);
                        const blob = await resImage.blob();

                        statusEl.innerText = "Mengunggah ke OCR...";
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

                        if (result.success) {
                            const fields = {
                                'nik': result.nik,
                                'nama': result.nama,
                                'tempat_tgl_lahir': result.tempat_tgl_lahir,
                                'jenis_kelamin': result.jenis_kelamin,
                                'alamat_ktp': result.alamat_ktp,
                                'rt_rw_ktp': result.rt_rw_ktp,
                                'kel_desa_ktp': result.kel_desa_ktp,
                                'kecamatan_ktp': result.kecamatan_ktp
                            };

                            // Pengisian yang terjamin aman untuk Livewire
                            for (const [key, value] of Object.entries(fields)) {
                                if (value) {
                                    let el = document.getElementById(key);
                                    if (!el) {
                                        el = document.querySelector(`[wire\\:model="${key}"]`);
                                    }
                                    if (el) {
                                        el.value = value;
                                        // Wajib pakai bubbles:true agar Livewire menangkap ketikannya
                                        el.dispatchEvent(new Event('input', { bubbles: true }));
                                        el.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }

                            statusEl.innerText = "✅ Semua data KTP berhasil terisi otomatis!";
                            statusEl.className = "text-sm font-bold text-green-600";
                        } else {
                            statusEl.innerText = "⚠️ Gagal membaca data KTP. Silakan ketik secara manual.";
                            statusEl.className = "text-sm font-bold text-orange-600";
                        }
                    } catch (err) {
                        console.error(err);
                        statusEl.innerText = "Terjadi kesalahan OCR: " + err.message;
                        statusEl.className = "text-sm font-bold text-red-600";
                    }
                });
            }
        });
    </script>
</div>