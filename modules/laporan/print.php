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

require_once __DIR__ . '/../../config/init.php';
requireLogin();

$type = $_GET['type'] ?? 'balita';
$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$dusun = $_GET['dusun'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$status_gizi = $_GET['status_gizi'] ?? '';
$risiko = $_GET['risiko'] ?? '';
$jenis = $_GET['jenis'] ?? '';
$status = $_GET['status'] ?? '';
$status_risiko = $_GET['status_risiko'] ?? '';

$pengaturan = getPengaturan();
$app_name = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$posyandu = $pengaturan['nama_posyandu'] ?? 'Posyandu';
$desa = $pengaturan['nama_desa'] ?? '';

$titles = [
  'balita' => 'Laporan Balita',
  'ibu_hamil' => 'Laporan Ibu Hamil',
  'lansia' => 'Laporan Lansia',
  'imunisasi' => 'Laporan Imunisasi',
  'stunting' => 'Laporan Stunting',
  'gizi' => 'Laporan Gizi',
  'kegiatan' => 'Laporan Kegiatan Posyandu',
];
$title = $titles[$type] ?? 'Laporan';

$rows = [];
$headers = [];

if ($type === 'balita') {
  $headers = ['No','No. Peserta','Nama Balita','L/P','Usia (bln)','Dusun','Tgl Periksa','BB','TB','Status Gizi','Risiko Stunting'];
  $where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($dusun) $where[] = "b.dusun='".escape($dusun)."'";
  if ($cari) $where[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%')";
  if ($status_gizi) $where[] = "pb.status_gizi='".escape($status_gizi)."'";
  if ($risiko) $where[] = "pb.risiko_stunting='".escape($risiko)."'";
  $data = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.tanggal_lahir,b.dusun,pb.tanggal_pemeriksaan,pb.berat_badan,pb.tinggi_badan,pb.status_gizi,pb.risiko_stunting,pb.umur_saat_periksa FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE ".implode(' AND ',$where)." ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['umur_saat_periksa']??hitungUmurBulan($r['tanggal_lahir']), $r['dusun']??'-', formatTanggal($r['tanggal_pemeriksaan']), $r['berat_badan']??'-', $r['tinggi_badan']??'-', $r['status_gizi'], $r['risiko_stunting']];
  }
} elseif ($type === 'ibu_hamil') {
  $headers = ['No','No. Peserta','Nama','Dusun','Tgl Periksa','Usia Kandungan','BB','TD','Kehamilan Ke','Status Risiko'];
  $where = ["pih.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($dusun) $where[] = "ih.dusun='".escape($dusun)."'";
  if ($cari) $where[] = "(ih.nama LIKE '%".escape($cari)."%' OR ih.nomor_peserta LIKE '%".escape($cari)."%')";
  if ($status_risiko) $where[] = "pih.status_risiko='".escape($status_risiko)."'";
  $data = fetchAll("SELECT ih.nomor_peserta,ih.nama,ih.dusun,ih.kehamilan_ke,pih.tanggal_pemeriksaan,pih.usia_kandungan,pih.berat_badan,pih.tekanan_darah,pih.status_risiko FROM pemeriksaan_ibu_hamil pih JOIN ibu_hamil ih ON ih.id=pih.ibu_hamil_id WHERE ".implode(' AND ',$where)." ORDER BY pih.tanggal_pemeriksaan DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nomor_peserta'], $r['nama'], $r['dusun']??'-', formatTanggal($r['tanggal_pemeriksaan']), $r['usia_kandungan']??'-', $r['berat_badan']??'-', $r['tekanan_darah']??'-', $r['kehamilan_ke'], $r['status_risiko']];
  }
} elseif ($type === 'lansia') {
  $headers = ['No','No. Peserta','Nama','L/P','Umur','Tgl Periksa','BB','TB','Tekanan Darah','Gula Darah','Kolesterol'];
  $where = ["pl.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($cari) $where[] = "(l.nama LIKE '%".escape($cari)."%' OR l.nomor_peserta LIKE '%".escape($cari)."%')";
  $data = fetchAll("SELECT l.nomor_peserta,l.nama,l.jenis_kelamin,l.tanggal_lahir,pl.tanggal_pemeriksaan,pl.berat_badan,pl.tinggi_badan,pl.tekanan_darah,pl.gula_darah,pl.kolesterol FROM pemeriksaan_lansia pl JOIN lansia l ON l.id=pl.lansia_id WHERE ".implode(' AND ',$where)." ORDER BY pl.tanggal_pemeriksaan DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nomor_peserta'], $r['nama'], $r['jenis_kelamin'], hitungUmur($r['tanggal_lahir']), formatTanggal($r['tanggal_pemeriksaan']), $r['berat_badan']??'-', $r['tinggi_badan']??'-', $r['tekanan_darah']??'-', $r['gula_darah']??'-', $r['kolesterol']??'-'];
  }
} elseif ($type === 'imunisasi') {
  $headers = ['No','No. Peserta','Nama Balita','L/P','Dusun','Jenis Imunisasi','Dosis','Tanggal'];
  $where = ["i.tanggal_imunisasi BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($jenis) $where[] = "i.jenis_imunisasi='".escape($jenis)."'";
  if ($cari) $where[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%')";
  $data = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.dusun,i.jenis_imunisasi,i.dosis,i.tanggal_imunisasi FROM imunisasi i JOIN balita b ON b.id=i.balita_id WHERE ".implode(' AND ',$where)." ORDER BY i.tanggal_imunisasi DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['dusun']??'-', $r['jenis_imunisasi'], $r['dosis']??'-', formatTanggal($r['tanggal_imunisasi'])];
  }
} elseif ($type === 'stunting') {
  $headers = ['No','No. Peserta','Nama','L/P','Usia (bln)','Dusun','Tgl Periksa','BB','TB','Status Gizi','Risiko'];
  $where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($dusun) $where[] = "b.dusun='".escape($dusun)."'";
  if ($risiko) $where[] = "pb.risiko_stunting='".escape($risiko)."'";
  else $where[] = "pb.risiko_stunting IN ('Risiko','Stunting')";
  $data = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.tanggal_lahir,b.dusun,pb.tanggal_pemeriksaan,pb.berat_badan,pb.tinggi_badan,pb.status_gizi,pb.risiko_stunting,pb.umur_saat_periksa FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE ".implode(' AND ',$where)." ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['umur_saat_periksa']??hitungUmurBulan($r['tanggal_lahir']), $r['dusun']??'-', formatTanggal($r['tanggal_pemeriksaan']), $r['berat_badan']??'-', $r['tinggi_badan']??'-', $r['status_gizi'], $r['risiko_stunting']];
  }
} elseif ($type === 'gizi') {
  $headers = ['No','No. Peserta','Nama','L/P','Dusun','Tgl Periksa','Usia (bln)','BB','TB','Status Gizi'];
  $where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($dusun) $where[] = "b.dusun='".escape($dusun)."'";
  if ($status_gizi) $where[] = "pb.status_gizi='".escape($status_gizi)."'";
  $data = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.dusun,pb.tanggal_pemeriksaan,pb.berat_badan,pb.tinggi_badan,pb.status_gizi,pb.umur_saat_periksa FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE ".implode(' AND ',$where)." ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['dusun']??'-', formatTanggal($r['tanggal_pemeriksaan']), $r['umur_saat_periksa']??'-', $r['berat_badan']??'-', $r['tinggi_badan']??'-', $r['status_gizi']];
  }
} elseif ($type === 'kegiatan') {
  $headers = ['No','Nama Kegiatan','Tanggal','Jam','Lokasi','Jenis','PJ','Status'];
  $where = ["tanggal_kegiatan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
  if ($jenis) $where[] = "jenis_kegiatan='".escape($jenis)."'";
  if ($status) $where[] = "status='".escape($status)."'";
  $data = fetchAll("SELECT * FROM jadwal WHERE ".implode(' AND ',$where)." ORDER BY tanggal_kegiatan DESC LIMIT 1000");
  foreach ($data as $i => $r) {
    $rows[] = [$i+1, $r['nama_kegiatan'], formatTanggal($r['tanggal_kegiatan']), date('H:i', strtotime($r['jam'])), $r['lokasi']??'-', $r['jenis_kegiatan'], $r['penanggung_jawab']??'-', $r['status']];
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($app_name) ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; background: #fff; padding: 15px; }
    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .header h1 { font-size: 16pt; margin-bottom: 4px; }
    .header h2 { font-size: 13pt; font-weight: normal; margin-bottom: 2px; }
    .header p { font-size: 10pt; color: #333; }
    .meta { margin-bottom: 12px; font-size: 10pt; }
    .meta span { margin-right: 16px; }
    table { width: 100%; border-collapse: collapse; font-size: 9pt; }
    th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; vertical-align: top; }
    th { background: #e0e0e0; font-weight: bold; }
    tr:nth-child(even) { background: #f5f5f5; }
    .footer { margin-top: 20px; font-size: 9pt; text-align: right; color: #555; }
    .empty { text-align: center; padding: 30px; color: #666; border: 1px solid #ccc; }
    .no-print-btn { margin-bottom: 12px; }
    .no-print-btn button {
      padding: 8px 16px; font-size: 14px; background: #3c8dbc; color: #fff;
      border: none; border-radius: 4px; cursor: pointer; margin-right: 8px;
    }
    .no-print-btn button.secondary { background: #6c757d; }
    @media print {
      .no-print-btn { display: none !important; }
      body { padding: 0; }
      @page { margin: 12mm; }
    }
  </style>
</head>
<body>
  <div class="no-print-btn">
    <button onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    <button class="secondary" onclick="window.close()">Tutup</button>
  </div>

  <div class="header">
    <h1><?= htmlspecialchars($app_name) ?></h1>
    <h2><?= htmlspecialchars($posyandu) ?><?= $desa ? ' - '.$desa : '' ?></h2>
    <p><?= htmlspecialchars($title) ?></p>
  </div>

  <div class="meta">
    <span><strong>Periode:</strong> <?= formatTanggal($tgl_dari) ?> s/d <?= formatTanggal($tgl_sampai) ?></span>
    <?php if ($dusun): ?><span><strong>Dusun:</strong> <?= htmlspecialchars($dusun) ?></span><?php endif; ?>
    <span><strong>Total:</strong> <?= count($rows) ?> data</span>
    <span><strong>Dicetak:</strong> <?= date('d/m/Y H:i') ?></span>
  </div>

  <?php if (empty($rows)): ?>
    <div class="empty">Tidak ada data sesuai filter yang dipilih.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <?php foreach ($headers as $h): ?>
            <th><?= htmlspecialchars($h) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td><?= htmlspecialchars((string)$cell) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div class="footer">
    Dicetak dari sistem <?= htmlspecialchars($app_name) ?> · <?= date('d/m/Y H:i:s') ?>
  </div>

  <script>
    // Otomatis buka dialog print setelah halaman siap (opsional, bisa dikomentari)
    // window.onload = function() { setTimeout(function(){ window.print(); }, 400); };
  </script>
</body>
</html>
