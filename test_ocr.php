<?php
$text = "NIK
PROVINSI DKI JAKARTA
JAKARTA BARAT
: 3172051401070005
Nama
: FABIAN SYAH AL GHIFFARI
Tempat/Tgl Lahir
SJAKARTA, 14-01-2007
Jenis kelamin
LAKI-LAKE
Gol, Daral
Alamat
KP. GAGA
RT/RW
007/003
Kel/Desa
: SEMANAN
Kecamatan
: KALIDERES";

$extractedData = [
    'nik' => '',
    'nama' => '',
    'tempat_tgl_lahir' => '',
    'jenis_kelamin' => '',
    'alamat_ktp' => '',
    'rt_rw_ktp' => '',
    'kel_desa_ktp' => '',
    'kecamatan_ktp' => ''
];

$lines = array_map('trim', explode("\n", $text));
$values = [];
$headerLines = [];

$foundNik = false;
foreach ($lines as $line) {
    $cleanNoSpace = str_replace(' ', '', $line);
    if (preg_match('/(NIK|N1K|NlK|N\|K|NK|N1k|M1K|IIK|HIK|MK)/i', $line) || preg_match('/\d{15,16}/', $cleanNoSpace)) {
        $foundNik = true;
    }

    if (!$foundNik && strlen(trim($line)) > 3) {
        $headerLines[] = trim($line);
    }

    if (preg_match('/^(PROVINSI|KABUPATEN|KOTA|DAERAH|Membaca|KTP|Warga|Negara)/i', $line)) {
        continue;
    }

    $clean = preg_replace('/^(NIK|N1K|NlK|N\|K|NK|N1k|M1K|IIK|HIK|MK|Nama|Tempat\/?Tgl\s*Lahir|Tempat|Tgl|Lahir|Jenis\s*Kelamin|Kelamin|Gol\.?\s*Darah|Gol|Darah|Alamat|RT[\/\-]RW|RT\s*RW|RT|RW|Kel\/Desa|Kelurahan|Desa|Kecamatan|Kec|Agama|Status\s*Perkawinan|Status|Perkawinan|Pekerjaan|Kewarganegaraan|Berlaku\s*Hingga|Berlaku|Hingga)[\s:\-\.]*/i', '', $line);
    $clean = trim($clean);
    $clean = preg_replace('/^[:\-\.\=]+\s*/', '', $clean);

    if (strlen($clean) > 1 && !preg_match('/^[=\-\_\.\:\/]+$/', $clean)) {
        $values[] = $clean;
    }
}

$rtrwIndex = -1;
$jalan = '';
$usedIndices = [];

$searchChars  = ['I', 'l', 'L', '|', 'O', 'o', 's', 'S', '?', 'b', 'B', '!', 'Z', 'z', ']', '[', 'i', 'g', 'G', 'A', 'a', 'D', 'd', 't', 'T', 'u', 'U', 'e', 'E', 'f', 'F', 'y', 'Y', 'j', 'J', 'h', 'H', 'R', 'r', 'p', 'P', 'q', 'Q', '(', ')', '{', '}', '/', '\\'];
$replaceChars = ['1', '1', '1', '1', '0', '0', '5', '5', '7', '6', '8', '1', '2', '2', '1', '1', '1', '9', '9', '4', '4', '0', '0', '7', '7', '0', '0', '3', '3', '7', '7', '7', '7', '1', '1', '1', '1', '2', '2', '9', '9', '9', '9', '1', '1', '1', '1', '1', '1'];

foreach ($values as $i => $val) {
    if (empty($extractedData['nik'])) {
        $sanitized = str_replace($searchChars, $replaceChars, str_replace(' ', '', $val));
        if (preg_match('/\d{16}/', $sanitized, $mNik)) {
            $extractedData['nik'] = $mNik[0];
            $usedIndices[] = $i;
        }
    }
}

print_r($values);
print_r($extractedData);
