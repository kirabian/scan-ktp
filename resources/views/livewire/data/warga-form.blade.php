<div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white border border-slate-200 shadow-sm sm:rounded-2xl overflow-hidden">
        <div class="px-6 py-5 bg-white border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl leading-6 font-bold text-slate-800">Registrasi Warga Baru</h3>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Input data warga untuk pendataan sistem administrasi.</p>
            </div>
        </div>

        <div class="p-6">
            @if($successMessage)
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $successMessage }}</span>
                </div>
            @endif

            <div wire:ignore class="mb-6 bg-slate-50 p-5 rounded-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-700 mb-1">Scan KTP Otomatis (OCR)</h4>
                <p class="text-xs text-slate-500 mb-4">Unggah atau foto KTP untuk mengisi form ini secara instan.</p>
                <input type="file" id="ocr-ktp" accept="image/*" capture="environment" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-xs file:font-medium
                    file:bg-white file:text-slate-700 file:border file:border-slate-300 file:shadow-sm
                    hover:file:bg-slate-50 mb-2
                "/>
                <div id="ocr-status" class="text-sm font-bold text-blue-600 hidden">Memproses KTP... Mohon tunggu.</div>
                <div id="ocr-debug-info" class="mt-2 text-xs text-slate-500 font-mono hidden bg-slate-100 p-2.5 rounded-lg max-h-40 overflow-y-auto whitespace-pre-wrap border border-slate-200"></div>
                <div id="ocr-preview-container" class="mt-4 hidden">
                    <p class="text-xs text-gray-500 mb-1">Preview KTP:</p>
                    <img id="ocr-preview-image" src="" alt="Preview KTP" class="max-h-48 rounded-lg shadow-sm border border-gray-200">
                </div>
            </div>

            <form id="wargaForm" wire:submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label for="nik" class="block text-sm font-medium text-gray-700">NIK (16 Digit)</label>
                        <input type="text" wire:model.live.debounce.500ms="nik" id="nik" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                        @error('nik') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        
                        @if($existingWarga)
                            <div class="mt-2 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                                <div class="flex items-start">
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700 mb-1">
                                            <span class="font-bold">Peringatan:</span> NIK ini sudah terdaftar atas nama <b>{{ $existingWarga->nama }}</b>. Data tidak bisa diinput ulang.
                                        </p>
                                        <p class="text-xs text-red-600 mb-2">
                                            <b>Alamat Domisili:</b> 
                                            @if($existingWarga->is_domisili_sesuai_ktp)
                                                {{ $existingWarga->alamat_ktp }} (Sesuai KTP)
                                            @else
                                                {{ $existingWarga->alamat_detail_domisili }}, {{ $existingWarga->kel_desa_domisili }}, {{ $existingWarga->kecamatan_domisili }}, {{ $existingWarga->kota_kab_domisili }}, {{ $existingWarga->provinsi_domisili }}
                                            @endif
                                        </p>
                                        <div class="mt-2">
                                            <button type="button" wire:click="viewExistingWarga" class="text-sm font-bold text-red-800 hover:text-red-900 underline">Lihat Detail Data NIK Ini</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap Sesuai KTP</label>
                        <input type="text" wire:model="nama" id="nama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label for="tempat_tgl_lahir" class="block text-sm font-medium text-gray-700">Tempat, Tgl Lahir <span id="umur-kalkulator" class="text-blue-600 font-bold ml-1"></span></label>
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

                    @if(!$is_domisili_sesuai_ktp)
                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <div class="col-span-1 md:col-span-2 mb-2">
                            <p class="text-sm font-medium text-yellow-800">Silakan isi alamat domisili secara manual karena berbeda dengan KTP.</p>
                        </div>

                        <div>
                            <label for="selectedProvinsi" class="block text-sm font-medium text-gray-700">Provinsi <span wire:loading wire:target="updatedIsDomisiliSesuaiKtp" class="text-blue-500 text-xs">(Loading...)</span></label>
                            <select wire:model.live="selectedProvinsi" id="selectedProvinsi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov['id'] }}|{{ $prov['nama'] }}">{{ $prov['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedProvinsi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="selectedKota" class="block text-sm font-medium text-gray-700">Kota/Kabupaten <span wire:loading wire:target="selectedProvinsi" class="text-blue-500 text-xs">(Loading...)</span></label>
                            <select wire:model.live="selectedKota" id="selectedKota" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" @if(!$selectedProvinsi) disabled @endif>
                                <option value="">Pilih Kota/Kabupaten</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city['id'] }}|{{ $city['nama'] }}">{{ $city['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedKota') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="selectedKecamatan" class="block text-sm font-medium text-gray-700">Kecamatan <span wire:loading wire:target="selectedKota" class="text-blue-500 text-xs">(Loading...)</span></label>
                            <select wire:model.live="selectedKecamatan" id="selectedKecamatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" @if(!$selectedKota) disabled @endif>
                                <option value="">Pilih Kecamatan</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district['id'] }}|{{ $district['nama'] }}">{{ $district['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedKecamatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="selectedKelurahan" class="block text-sm font-medium text-gray-700">Kelurahan/Desa <span wire:loading wire:target="selectedKecamatan" class="text-blue-500 text-xs">(Loading...)</span></label>
                            <select wire:model="selectedKelurahan" id="selectedKelurahan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" @if(!$selectedKecamatan) disabled @endif>
                                <option value="">Pilih Kelurahan/Desa</option>
                                @foreach($villages as $village)
                                    <option value="{{ $village['id'] }}|{{ $village['nama'] }}">{{ $village['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedKelurahan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label for="alamat_detail_domisili" class="block text-sm font-medium text-gray-700">Detail Alamat Domisili (Jalan, Blok, No Rumah)</label>
                            <textarea wire:model="alamat_detail_domisili" id="alamat_detail_domisili" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Contoh: Jl. Merdeka No 123"></textarea>
                            @error('alamat_detail_domisili') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="rt_rw_domisili" class="block text-sm font-medium text-gray-700">RT/RW</label>
                            <input type="text" wire:model="rt_rw_domisili" id="rt_rw_domisili" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="001/002">
                        </div>

                        <div>
                            <label for="kode_pos_domisili" class="block text-sm font-medium text-gray-700">Kode Pos</label>
                            <input type="text" wire:model="kode_pos_domisili" id="kode_pos_domisili" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>
                    @endif

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
                    <button type="submit" @if($existingWarga) disabled @endif class="w-full inline-flex justify-center py-3.5 px-4 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-colors @if($existingWarga) opacity-50 cursor-not-allowed @endif">
                        Simpan Data Warga
                    </button>
                </div>
            </form>
        </div>
        @if($showExistingWargaModal && $existingWarga)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-5 border-b pb-2">
                            <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                                Detail Data Warga (Duplikat)
                            </h3>
                            <button type="button" wire:click="closeExistingWargaModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Info -->
                            <div>
                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Informasi Pribadi</h4>
                                <table class="w-full text-sm mb-4">
                                    <tbody>
                                        <tr><td class="py-1 font-medium text-gray-600 w-1/3">NIK</td><td class="py-1 font-bold">{{ $existingWarga->nik }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">Nama</td><td class="py-1">{{ $existingWarga->nama }}</td></tr>
                                        <tr><td class="py-1 font-medium text-gray-600">No HP</td><td class="py-1">{{ $existingWarga->no_wa_hp }}</td></tr>
                                    </tbody>
                                </table>
                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Alamat KTP</h4>
                                <p class="text-sm mb-4">{{ $existingWarga->alamat_ktp }}</p>
                                
                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Alamat Domisili</h4>
                                @if($existingWarga->is_domisili_sesuai_ktp)
                                    <p class="text-sm italic text-gray-500 mb-4">Sesuai KTP</p>
                                @else
                                    <p class="text-sm mb-4">
                                        {{ $existingWarga->alamat_detail_domisili }}<br>
                                        Kel/Desa: {{ $existingWarga->kel_desa_domisili }}, Kec: {{ $existingWarga->kecamatan_domisili }}<br>
                                        Kab/Kota: {{ $existingWarga->kota_kab_domisili }}, Prov: {{ $existingWarga->provinsi_domisili }}<br>
                                        Kode Pos: {{ $existingWarga->kode_pos_domisili }}
                                    </p>
                                @endif

                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Pekerjaan</h4>
                                <p class="text-sm">{{ $existingWarga->pekerjaan }}</p>
                            </div>
                            <!-- Photos -->
                            <div>
                                <h4 class="font-semibold text-gray-700 border-b pb-1 mb-3">Foto Bukti</h4>
                                <div class="bg-gray-100 rounded border border-gray-200 h-32 flex items-center justify-center mb-4 overflow-hidden">
                                    @if($existingWarga->foto_ktp_path)
                                        <img src="{{ route('secure.image', ['folder' => 'ktp', 'filename' => basename($existingWarga->foto_ktp_path)]) }}" alt="Foto KTP" class="max-h-full">
                                    @else
                                        <span class="text-xs text-gray-400">Tidak ada foto KTP</span>
                                    @endif
                                </div>
                                <div class="bg-gray-100 rounded border border-gray-200 h-32 flex items-center justify-center overflow-hidden">
                                    @if($existingWarga->foto_wajah_path)
                                        <img src="{{ route('secure.image', ['folder' => 'wajah', 'filename' => basename($existingWarga->foto_wajah_path)]) }}" alt="Foto Wajah" class="max-h-full">
                                    @else
                                        <span class="text-xs text-gray-400">Tidak ada foto Wajah</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeExistingWargaModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
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
                ktpInput.onchange = async function(e) {
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

                    const debugEl = document.getElementById('ocr-debug-info');
                    if (debugEl) {
                        debugEl.innerText = "";
                        debugEl.classList.add('hidden');
                    }

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
                            if (result.nik) {
                                let cleanNik = result.nik.replace('RAW:', '').trim();
                                @this.set('nik', cleanNik);
                            }
                            const fields = {
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

                            // Tampilkan raw OCR jika ada untuk membantu debug
                            if (debugEl && result.raw_ocr_text) {
                                debugEl.innerText = "Raw OCR Text:\n" + result.raw_ocr_text;
                                debugEl.classList.remove('hidden');
                            }

                            if (!result.nik) {
                                alert("Data KTP kurang jelas dan NIK gagal diekstrak. Silakan foto ulang atau ketik NIK secara manual.");
                                statusEl.classList.add('hidden');
                                e.target.value = '';
                            } else if (result.nik.startsWith('RAW:')) {
                                let rawNik = result.nik.replace('RAW:', '').trim();
                                alert("NIK terbaca kurang jelas/lengkap (" + rawNik + "). Angka telah dimasukkan ke kolom NIK, silakan lengkapi menjadi 16 digit.");
                                statusEl.classList.add('hidden');
                                e.target.value = '';
                            } else {
                                statusEl.innerText = "✅ Semua data KTP berhasil terisi otomatis!";
                                statusEl.className = "text-sm font-bold text-green-600";
                            }
                        } else {
                            alert("Gagal membaca data KTP. Silakan foto ulang atau ketik manual.");
                            statusEl.classList.add('hidden');
                            e.target.value = '';
                            if (debugEl && result.raw_ocr_text) {
                                debugEl.innerText = "Raw Response:\n" + result.raw_ocr_text;
                                debugEl.classList.remove('hidden');
                            }
                        }
                    } catch (err) {
                        console.error(err);
                        alert("Terjadi kesalahan OCR: " + err.message);
                        statusEl.classList.add('hidden');
                        e.target.value = '';
                    }
                };
            }

            // Listener ketika form berhasil disimpan
            Livewire.on('warga-saved', () => {
                // Reset form HTML secara menyeluruh
                const form = document.getElementById('wargaForm');
                if (form) form.reset();

                // Tampilkan Popup Alert
                alert("✅ BERHASIL! Data warga telah berhasil disimpan ke dalam database.\n\nForm isian kini telah direset kembali.");
                
                // Reset elemen UI manual (OCR)
                const previewContainer = document.getElementById('ocr-preview-container');
                if (previewContainer) previewContainer.classList.add('hidden');
                
                const statusEl = document.getElementById('ocr-status');
                if (statusEl) statusEl.classList.add('hidden');

                const debugEl = document.getElementById('ocr-debug-info');
                if (debugEl) {
                    debugEl.innerText = '';
                    debugEl.classList.add('hidden');
                }

                const ocrInput = document.getElementById('ocr-ktp');
                if (ocrInput) ocrInput.value = '';

                // Scroll kembali ke atas
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            
            // Kalkulator Umur Otomatis
            setInterval(function() {
                const ttlInput = document.getElementById('tempat_tgl_lahir');
                const umurSpan = document.getElementById('umur-kalkulator');
                if (ttlInput && umurSpan) {
                    if (!ttlInput.value) {
                        umurSpan.innerText = '';
                        return;
                    }
                    const match = ttlInput.value.match(/(\d{2})[- \/.](\d{2})[- \/.](\d{4})/);
                    if (match && match.length === 4) {
                        const day = parseInt(match[1]);
                        const month = parseInt(match[2]) - 1;
                        const year = parseInt(match[3]);
                        const birthDate = new Date(year, month, day);
                        const today = new Date();
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const m = today.getMonth() - birthDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if (age >= 0 && age < 150) {
                            umurSpan.innerText = '(Umur: ' + age + ' Tahun)';
                        } else {
                            umurSpan.innerText = '';
                        }
                    } else {
                        umurSpan.innerText = '';
                    }
                }
            }, 500);
        });
    </script>
</div>