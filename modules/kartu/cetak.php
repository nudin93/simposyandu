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
$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$preview = $_GET['preview'] ?? 0;
$pengaturan = getPengaturan();

$data = null; $label = ''; $jadwal_kontrol = '';
if ($type === 'balita') {
    $data = fetchOne("SELECT b.*, k.nama as petugas FROM balita b LEFT JOIN kader k ON 1=1 WHERE b.id=$id LIMIT 1");
    $label = 'BALITA';
    $pmx = fetchOne("SELECT jadwal_kontrol FROM pemeriksaan_balita WHERE balita_id=$id ORDER BY tanggal_pemeriksaan DESC LIMIT 1");
    $jadwal_kontrol = $pmx['jadwal_kontrol'] ?? '';
    $nama = $data['nama_lengkap'] ?? '';
    $nomor = $data['nomor_peserta'] ?? '';
    $alamat = $data['desa'] ?? '';
    $foto = $data['foto_anak'] ? APP_URL.'/uploads/balita/'.$data['foto_anak'] : APP_URL.'/assets/img/user.png';
} elseif ($type === 'ibu_hamil') {
    $data = fetchOne("SELECT * FROM ibu_hamil WHERE id=$id");
    $label = 'IBU HAMIL';
    $pmx = fetchOne("SELECT jadwal_kontrol FROM pemeriksaan_ibu_hamil WHERE ibu_hamil_id=$id ORDER BY tanggal_pemeriksaan DESC LIMIT 1");
    $jadwal_kontrol = $pmx['jadwal_kontrol'] ?? '';
    $nama = $data['nama'] ?? '';
    $nomor = $data['nomor_peserta'] ?? '';
    $alamat = $data['desa'] ?? '';
    $foto = $data['foto_ibu'] ? APP_URL.'/uploads/ibu_hamil/'.$data['foto_ibu'] : APP_URL.'/assets/img/user.png';
} elseif ($type === 'lansia') {
    $data = fetchOne("SELECT * FROM lansia WHERE id=$id");
    $label = 'LANSIA';
    $pmx = fetchOne("SELECT jadwal_kontrol FROM pemeriksaan_lansia WHERE lansia_id=$id ORDER BY tanggal_pemeriksaan DESC LIMIT 1");
    $jadwal_kontrol = $pmx['jadwal_kontrol'] ?? '';
    $nama = $data['nama'] ?? '';
    $nomor = $data['nomor_peserta'] ?? '';
    $alamat = $data['alamat_lengkap'] ?? '';
    $foto = APP_URL.'/assets/img/user.png';
}

if (!$data) { echo '<p class="text-danger text-center p-4">Data tidak ditemukan</p>'; exit; }
?>
<?php if (!$preview): ?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kartu <?= $label ?> - <?= htmlspecialchars($nama) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/custom.css">
<style>body{background:#f0f4f8;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;}</style>
</head><body>
<?php endif; ?>
<div class="d-flex flex-column align-items-center gap-3">
  <div class="kartu-peserta" style="width:340px;font-family:'Inter',sans-serif">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div style="font-size:10px;opacity:.7;letter-spacing:1px;text-transform:uppercase"><?= htmlspecialchars($pengaturan['nama_posyandu']??'Posyandu') ?></div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.5px">KARTU <?= $label ?></div>
      </div>
      <div style="background:rgba(255,255,255,.2);border-radius:6px;padding:4px 8px;font-size:10px">ID: <?= htmlspecialchars($nomor) ?></div>
    </div>
    <div class="d-flex align-items-center gap-3 my-3">
      <img src="<?= $foto ?>" style="width:60px;height:60px;border-radius:8px;border:2px solid rgba(255,255,255,.3);object-fit:cover" onerror="this.src='<?= APP_URL ?>/assets/img/user.png'">
      <div>
        <div style="font-size:16px;font-weight:700;line-height:1.2"><?= htmlspecialchars($nama) ?></div>
        <div style="font-size:11px;opacity:.8;margin-top:4px"><?= htmlspecialchars(substr($alamat,0,40)) ?></div>
        <?php if ($jadwal_kontrol): ?><div style="font-size:10px;opacity:.7;margin-top:4px"><i>Kontrol: <?= date('d/m/Y', strtotime($jadwal_kontrol)) ?></i></div><?php endif; ?>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
      <div class="qr-box" id="qrcode<?= $id ?>"></div>
      <div style="text-align:right">
        <div style="font-size:9px;opacity:.6">Diterbitkan</div>
        <div style="font-size:10px;font-weight:600"><?= date('d/m/Y') ?></div>
        <div style="font-size:9px;opacity:.6;margin-top:4px"><?= htmlspecialchars($pengaturan['nama_desa']??'') ?></div>
      </div>
    </div>
  </div>
  <?php if (!$preview): ?>
  <div class="no-print d-flex gap-2">
    <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Kartu</button>
    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
  </div>
  <?php endif; ?>
</div>
<?php if (!$preview): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById('qrcode<?= $id ?>'), { text: '<?= $nomor ?>', width: 64, height: 64, colorDark: '#fff', colorLight: 'transparent' });</script>
</body></html>
<?php else: ?>
<script>if (typeof QRCode !== 'undefined') { new QRCode(document.getElementById('qrcode<?= $id ?>'), { text: '<?= $nomor ?>', width: 64, height: 64, colorDark: '#fff', colorLight: 'transparent' }); }</script>
<?php endif; ?>
