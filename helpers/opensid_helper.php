<?php
/**
 * POSYANDU DIGITAL DESA
 * Versi: 1.0.0
 * Dikembangkan oleh: Zainudin Larau
 *
 * Tujuan:
 * Aplikasi digital untuk membantu pendataan,
 * pelayanan, pemantauan, dan pelaporan kegiatan Posyandu Desa.
 */
/**
 * ==========================================================
 * SIMPOSYANDU - Sistem Informasi Posyandu Terintegrasi
 * ==========================================================
 *
 * Pengembang  : Zainudin Larau
 * Tahun       : 2026
 *
 * Deskripsi:
 * Aplikasi ini dikembangkan untuk membantu pengelolaan
 * data Posyandu secara digital, meliputi data balita,
 * ibu hamil, pemeriksaan kesehatan, pertumbuhan anak,
 * statistik, dan pelayanan masyarakat.
 *
 * Ketentuan:
 * Aplikasi ini merupakan karya pengembang dan
 * TIDAK DIPERJUALBELIKAN.
 *
 * Setiap penggunaan, penggandaan, perubahan kode,
 * atau pendistribusian aplikasi wajib mendapatkan
 * izin dari pemilik dan pengembang.
 *
 * Hak Cipta:
 * © 2026 Zainudin Larau
 * Semua Hak Dilindungi.
 *
 * ==========================================================
 */

/**
 * Helper integrasi OpenSID – data penduduk & keluarga (read-only)
 * Dipakai oleh Controller & Model
 */
if (!function_exists('cariPendudukOpenSID')) {
    // Fungsi utama sudah ada di config/database.php
    // File ini hanya sebagai placeholder jika dipisah di masa depan
}

/**
 * Validasi id_penduduk_opensid masih hidup di OpenSID
 */
function validasiPendudukOpenSID($id_penduduk)
{
    if (!opensid_available() || !(int)$id_penduduk) {
        return false;
    }
    $row = opensid_fetchOne(
        "SELECT id FROM tweb_penduduk WHERE id = " . (int)$id_penduduk . " AND status_dasar = 1 LIMIT 1"
    );
    return !empty($row);
}

/**
 * Ambil data penduduk siap isi form (array flat)
 */
function pendudukUntukForm($id_penduduk)
{
    $p = getPendudukOpenSIDById($id_penduduk);
    if (!$p) {
        return null;
    }
    return [
        'id_penduduk_opensid' => $p['id_penduduk'],
        'nik'                 => $p['nik'],
        'no_kk'               => $p['no_kk'],
        'nama'                => $p['nama'],
        'jenis_kelamin'       => $p['jenis_kelamin'],
        'tanggal_lahir'       => $p['tanggal_lahir'],
        'tempat_lahir'        => $p['tempat_lahir'] ?? '',
        'nama_ayah'           => $p['nama_ayah'] ?? '',
        'nama_ibu'            => $p['nama_ibu'] ?? '',
        'alamat'              => $p['alamat'] ?? '',
        'dusun'               => $p['dusun'] ?? '',
        'rt'                  => $p['rt'] ?? '',
        'rw'                  => $p['rw'] ?? '',
        'status_integrasi'    => 'terhubung',
    ];
}
