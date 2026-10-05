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

$page_title = 'Riwayat Pemeriksaan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$nik = trim($_GET['nik'] ?? '');

$p = null;
if ($id) $p = getPendudukOpenSIDById($id);
if (!$p && $nik) $p = getPendudukOpenSIDByNik($nik);
if ($p) $nik = $p['nik'] ?? $nik;

if (!$p && !$nik) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-warning mt-3">Data penduduk tidak ditemukan. <a href="index.php">Kembali</a></div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$nikEsc = escape($nik);
$umur = (int)($p['umur'] ?? 0);
$jk = $p['jenis_kelamin'] ?? '';

// Cari data peserta terkait di SIMPosyandu
$balita = $nik ? fetchOne("SELECT * FROM balita WHERE nik_anak='$nikEsc' OR id_penduduk_opensid=".(int)$id." LIMIT 1") : null;
$bumil  = $nik ? fetchOne("SELECT * FROM ibu_hamil WHERE nik='$nikEsc' OR id_penduduk_opensid=".(int)$id." LIMIT 1") : null;
$lansia = $nik ? fetchOne("SELECT * FROM lansia WHERE nik='$nikEsc' OR id_penduduk_opensid=".(int)$id." LIMIT 1") : null;
$kbList = $nik ? fetchAll("SELECT * FROM kb WHERE nik='$nikEsc' ORDER BY tanggal_pelayanan DESC LIMIT 20") : [];

$periksaBalita = [];
$imunisasi = [];
$vitamin = [];
$periksaBumil = [];
$periksaLansia = [];

if ($balita) {
    $bid = (int)$balita['id'];
    $periksaBalita = fetchAll("SELECT pb.*, k.nama as petugas FROM pemeriksaan_balita pb LEFT JOIN kader k ON k.id=pb.petugas_id WHERE pb.balita_id=$bid ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 50");
    $imunisasi = fetchAll("SELECT i.*, k.nama as petugas FROM imunisasi i LEFT JOIN kader k ON k.id=i.petugas_id WHERE i.balita_id=$bid ORDER BY i.tanggal_imunisasi DESC LIMIT 50");
    $vitamin = fetchAll("SELECT v.*, k.nama as petugas FROM vitamin v LEFT JOIN kader k ON k.id=v.petugas_id WHERE v.balita_id=$bid ORDER BY v.tanggal_pemberian DESC LIMIT 50");
}
if ($bumil) {
    $iid = (int)$bumil['id'];
    $periksaBumil = fetchAll("SELECT * FROM pemeriksaan_ibu_hamil WHERE ibu_hamil_id=$iid ORDER BY tanggal_pemeriksaan DESC LIMIT 50");
}
if ($lansia) {
    $lid = (int)$lansia['id'];
    $periksaLansia = fetchAll("SELECT * FROM pemeriksaan_lansia WHERE lansia_id=$lid ORDER BY tanggal_pemeriksaan DESC LIMIT 50");
}

$hasAny = $balita || $bumil || $lansia || !empty($kbList);
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-notes-medical me-2 text-success"></i>Riwayat Pemeriksaan</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Penduduk</a></li>
        <li class="breadcrumb-item active">Riwayat</li>
      </ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">

  <!-- Identitas -->
  <div class="card mb-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-user me-1"></i><?= htmlspecialchars($p['nama'] ?? '-') ?></h5>
      <a href="profil.php?id=<?= (int)($p['id'] ?? $id) ?>" class="btn btn-sm btn-light">Biodata</a>
    </div>
    <div class="card-body py-2">
      <div class="row small">
        <div class="col-6 col-md-3"><strong>NIK:</strong> <code><?= htmlspecialchars($nik ?: '-') ?></code></div>
        <div class="col-6 col-md-3"><strong>No. KK:</strong> <?= htmlspecialchars($p['no_kk'] ?? '-') ?></div>
        <div class="col-6 col-md-2"><strong>L/P:</strong> <?= ($jk==='L'?'Laki-laki':($jk==='P'?'Perempuan':'-')) ?></div>
        <div class="col-6 col-md-2"><strong>Umur:</strong> <?= $umur ?> tahun</div>
        <div class="col-12 col-md-2"><strong>Dusun:</strong> <?= htmlspecialchars($p['dusun'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <?php if (!$hasAny): ?>
  <div class="alert alert-warning">
    <i class="fas fa-info-circle me-1"></i>
    Belum ada data pelayanan Posyandu untuk warga ini.
    <?php if ($umur <= 5): ?>
      <a href="<?= APP_URL ?>/modules/balita/tambah.php?id_penduduk=<?= (int)$id ?>&nik=<?= urlencode($nik) ?>" class="alert-link">Daftarkan sebagai Balita</a>
    <?php elseif ($jk==='P' && $umur >= 10 && $umur <= 55): ?>
      <a href="<?= APP_URL ?>/modules/ibu_hamil/tambah.php?id_penduduk=<?= (int)$id ?>&nik=<?= urlencode($nik) ?>" class="alert-link">Daftarkan sebagai Ibu Hamil</a>
    <?php elseif ($umur >= 60): ?>
      <a href="<?= APP_URL ?>/modules/lansia/tambah.php?id_penduduk=<?= (int)$id ?>&nik=<?= urlencode($nik) ?>" class="alert-link">Daftarkan sebagai Lansia</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- BALITA -->
  <?php if ($balita): ?>
  <div class="card mb-3">
    <div class="card-header bg-info text-white">
      <h5 class="mb-0"><i class="fas fa-baby me-1"></i>Pemeriksaan Balita
        <small class="ms-2 opacity-75">No. <?= htmlspecialchars($balita['nomor_peserta'] ?? '') ?></small>
      </h5>
    </div>
    <div class="card-body p-0 table-responsive">
      <?php if (empty($periksaBalita)): ?>
      <p class="p-3 text-muted mb-0">Belum ada pemeriksaan. <a href="<?= APP_URL ?>/modules/pemeriksaan_balita/tambah.php?balita_id=<?= (int)$balita['id'] ?>">Tambah pemeriksaan</a></p>
      <?php else: ?>
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light"><tr>
          <th>Tanggal</th><th>Umur (bln)</th><th>BB (kg)</th><th>TB (cm)</th><th>LK</th><th>Status Gizi</th><th>Stunting</th><th>Petugas</th>
        </tr></thead>
        <tbody>
          <?php foreach ($periksaBalita as $r): ?>
          <tr>
            <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
            <td><?= htmlspecialchars($r['umur_saat_periksa'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['berat_badan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['tinggi_badan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['lingkar_kepala'] ?? '-') ?></td>
            <td><span class="badge bg-<?= ($r['status_gizi']??'')==='Normal'?'success':(($r['status_gizi']??'')==='Lebih'?'warning':'danger') ?>"><?= htmlspecialchars($r['status_gizi'] ?? '-') ?></span></td>
            <td><span class="badge bg-<?= ($r['risiko_stunting']??'')==='Tidak'?'success':'danger' ?>"><?= htmlspecialchars($r['risiko_stunting'] ?? '-') ?></span></td>
            <td><?= htmlspecialchars($r['petugas'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($imunisasi)): ?>
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-syringe me-1"></i>Riwayat Imunisasi</h5></div>
    <div class="card-body p-0 table-responsive">
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light"><tr><th>Tanggal</th><th>Jenis</th><th>Dosis</th><th>Petugas</th><th>Keterangan</th></tr></thead>
        <tbody>
          <?php foreach ($imunisasi as $r): ?>
          <tr>
            <td><?= formatTanggal($r['tanggal_imunisasi']) ?></td>
            <td><?= htmlspecialchars($r['jenis_imunisasi']) ?></td>
            <td><?= htmlspecialchars($r['dosis'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['petugas'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($vitamin)): ?>
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-capsules me-1"></i>Riwayat Vitamin</h5></div>
    <div class="card-body p-0 table-responsive">
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light"><tr><th>Tanggal</th><th>Jenis</th><th>Dosis</th><th>Petugas</th></tr></thead>
        <tbody>
          <?php foreach ($vitamin as $r): ?>
          <tr>
            <td><?= formatTanggal($r['tanggal_pemberian']) ?></td>
            <td><?= htmlspecialchars($r['jenis_vitamin']) ?></td>
            <td><?= htmlspecialchars($r['dosis'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['petugas'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- IBU HAMIL -->
  <?php if ($bumil): ?>
  <div class="card mb-3">
    <div class="card-header text-white" style="background:#9b59b6">
      <h5 class="mb-0"><i class="fas fa-female me-1"></i>Pemeriksaan Ibu Hamil (ANC)
        <small class="ms-2 opacity-75">No. <?= htmlspecialchars($bumil['nomor_peserta'] ?? '') ?></small>
      </h5>
    </div>
    <div class="card-body p-0 table-responsive">
      <?php if (empty($periksaBumil)): ?>
      <p class="p-3 text-muted mb-0">Belum ada pemeriksaan ANC.</p>
      <?php else: ?>
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light"><tr>
          <th>Tanggal</th><th>Usia (minggu)</th><th>BB</th><th>Tekanan Darah</th><th>TFU</th><th>DJJ</th><th>LILA</th>
        </tr></thead>
        <tbody>
          <?php foreach ($periksaBumil as $r): ?>
          <tr>
            <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
            <td><?= htmlspecialchars($r['usia_kandungan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['berat_badan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['tekanan_darah'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['tinggi_fundus'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['djj'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['lila'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- LANSIA -->
  <?php if ($lansia): ?>
  <div class="card mb-3">
    <div class="card-header bg-secondary text-white">
      <h5 class="mb-0"><i class="fas fa-user-injured me-1"></i>Pemeriksaan Lansia
        <small class="ms-2 opacity-75">No. <?= htmlspecialchars($lansia['nomor_peserta'] ?? '') ?></small>
      </h5>
    </div>
    <div class="card-body p-0 table-responsive">
      <?php if (empty($periksaLansia)): ?>
      <p class="p-3 text-muted mb-0">Belum ada pemeriksaan lansia.</p>
      <?php else: ?>
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light"><tr>
          <th>Tanggal</th><th>BB</th><th>TB</th><th>Tekanan Darah</th><th>Gula Darah</th><th>Kolesterol</th><th>Keluhan</th>
        </tr></thead>
        <tbody>
          <?php foreach ($periksaLansia as $r): ?>
          <tr>
            <td><?= formatTanggal($r['tanggal_pemeriksaan'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['berat_badan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['tinggi_badan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['tekanan_darah'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['gula_darah'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['kolesterol'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['keluhan'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- KB -->
  <?php if (!empty($kbList)): ?>
  <div class="card mb-3">
    <div class="card-header bg-warning">
      <h5 class="mb-0"><i class="fas fa-pills me-1"></i>Riwayat KB</h5>
    </div>
    <div class="card-body p-0 table-responsive">
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light"><tr>
          <th>Tanggal</th><th>Metode</th><th>Mulai</th><th>Kontrol</th><th>Status</th>
        </tr></thead>
        <tbody>
          <?php foreach ($kbList as $r): ?>
          <tr>
            <td><?= formatTanggal($r['tanggal_pelayanan'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['jenis_kontrasepsi'] ?? '-') ?></td>
            <td><?= formatTanggal($r['tanggal_mulai'] ?? '') ?></td>
            <td><?= formatTanggal($r['tanggal_kontrol'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['status'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
