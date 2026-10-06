<?php
/** SIMPOSYANDU - Versi aplikasi + catatan pembaruan (popup "Yang Baru"). */
if (!defined('APP_VERSION')) define('APP_VERSION', '26.10.1');
function app_changelog() {
  return [
    ['versi' => '26.10.1', 'tanggal' => '2026-10-06', 'judul' => 'Penyesuaian versi',
     'items' => ['Penyesuaian nomor versi mengikuti rilis', 'Perbaikan struktur database', 'Perbaikan tampilan informasi']],
    ['versi' => '1.0.13', 'tanggal' => '2026-09-29', 'judul' => 'Menu kader ikut hak akses',
     'items' => ['Tombol Data/Periksa/Laporan hanya tampil sesuai izin user', 'Tanpa tampilan administrator di Beranda Kader']],
    ['versi' => '1.0.12', 'tanggal' => '2026-09-29', 'judul' => 'Audit + sinkronisasi versi',
     'items' => ['Label versi fallback disamakan, tidak ada popup nyangkut', 'Beranda Kader terverifikasi bersih dari sisa kode builder']],
    ['versi' => '1.0.11', 'tanggal' => '2026-09-29', 'judul' => 'Dashboard kader ala referensi',
     'items' => ['Beranda Kader mobile ungu untuk user kader', 'Kartu periksa hari/bulan ini, total balita, jadwal', 'Grafik 7 hari + navigasi bawah + mode gelap']],
    ['versi' => '1.0.10', 'tanggal' => '2026-09-29', 'judul' => 'Popup otomatis dikembalikan',
     'items' => ['Popup Yang Baru tampil lagi setiap versi baru dibuka', 'Bisa ditutup dengan tombol Mengerti']],
    ['versi' => '1.0.9', 'tanggal' => '2026-09-29', 'judul' => 'Popup otomatis dimatikan',
     'items' => ['Tidak ada lagi popup setiap buka menu', 'Info versi hanya dibuka manual via lonceng / tombol Yang Baru']],
    ['versi' => '1.0.8', 'tanggal' => '2026-09-29', 'judul' => 'Perbaikan paket timpa',
     'items' => ['File versi dan API pembaruan kini ikut dalam ZIP (sebelumnya tertinggal)', 'Lonceng dan popup Yang Baru aktif setelah timpa']],
    ['versi' => '1.0.7', 'tanggal' => '2026-09-29', 'judul' => 'Lonceng jadi modal tengah',
     'items' => ['Klik lonceng membuka popup di tengah layar, tulisan terlihat semua', 'Tombol Perbarui Sekarang + Semua Perubahan di dalam popup']],
    ['versi' => '1.0.6', 'tanggal' => '2026-09-29', 'judul' => 'Perbaikan panel lonceng',
     'items' => ['Panel lonceng pas layar HP, tulisan tidak terpotong', 'Isi panel hanya fungsi update versi terbaru']],
    ['versi' => '1.0.5', 'tanggal' => '2026-09-29', 'judul' => 'Lonceng pembaruan di samping profil',
     'items' => ['Ikon lonceng di samping foto profil dengan badge jumlah', 'Klik lonceng: tampil versi terbaru + daftar menu yang diperbarui', 'Tombol Perbarui Sekarang dengan panduan update']],
    ['versi' => '1.0.4', 'tanggal' => '2026-09-29', 'judul' => 'Popup info pembaruan',
     'items' => ['Setiap versi baru tampil otomatis setelah login', 'Tombol "Yang Baru" di bawah sidebar untuk buka ulang', 'Nomor versi tampil dinamis di sidebar']],
    ['versi' => '1.0.3', 'tanggal' => '2026-09-28', 'judul' => 'Perbaikan tombol Aksi Anak TK',
     'items' => ['Tombol Aksi kini bisa ditekan (script dipindah setelah jQuery dimuat)']],
    ['versi' => '1.0.2', 'tanggal' => '2026-09-28', 'judul' => 'Bulk Anak TK',
     'items' => ['Centang semua + Naik ke TK B massal', 'Luluskan massal (otomatis pindah ke daftar Lulus)', 'Hapus massal untuk koreksi salah input']],
    ['versi' => '1.0.1', 'tanggal' => '2026-09-28', 'judul' => 'Keamanan',
     'items' => ['Helper anti-injection terpusat di config/database.php', 'Anti Excel-formula-injection di export CSV', 'Validasi tanggal laporan + .htaccess diperkeras']],
  ];
}
function app_changelog_since($seen) {
  $seen = trim((string)$seen);
  if ($seen === '' || $seen === '0') return app_changelog();
  $out = [];
  foreach (app_changelog() as $n) {
    if (version_compare($n['versi'], $seen, '>')) $out[] = $n;
  }
  return $out;
}
