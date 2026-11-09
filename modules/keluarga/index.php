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

$page_title = 'Data Keluarga (OpenSID)';
require_once __DIR__ . '/../../includes/header.php';

$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(10, min(100, (int)($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $perPage;
$data = [];
$total = 0;
$opensidOk = opensid_available();

if ($opensidOk) {
    $total = countKeluargaOpenSID($search);
    if ($search !== '') {
        $data = cariKeluargaOpenSID($search, $perPage, $offset);
    } else {
        $data = listKeluargaOpenSID($perPage, $offset);
    }
}
$totalPages = max(1, (int)ceil($total / max(1, $perPage)));
if (!function_exists('buildQs')) {
    function buildQs(array $params): string {
        return http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
    }
}
$baseQs = ['q' => $search, 'per_page' => $perPage];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-home me-2 text-primary"></i>Data Keluarga</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li>
          <li class="breadcrumb-item active">Keluarga</li>
        </ol>
      </div>
    </div>
  </div>
</section>
<section class="content">
<div class="container-fluid">
  <?php if (!$opensidOk): ?>
  <div class="alert alert-danger">Koneksi OpenSID gagal. Periksa config/database.php.</div>
  <?php endif; ?>

  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-light py-2">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text">Cari:</span>
            <input type="text" name="q" class="form-control" placeholder="No KK / Nama Kepala / Dusun..." value="<?= htmlspecialchars($search) ?>">
          </div>
        </div>
        <div class="col-9 col-md-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text">Tampilkan</span>
            <select name="per_page" class="form-select" onchange="this.form.submit()">
              <?php foreach ([10,25,50,100] as $pp): ?>
              <option value="<?= $pp ?>" <?= $perPage===$pp?'selected':'' ?>><?= $pp ?></option>
              <?php endforeach; ?>
            </select>
            <span class="input-group-text">entri</span>
          </div>
        </div>
        <div class="col-3 col-md-1">
          <button class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button>
        </div>
        <div class="col-12 col-md-3 text-md-end">
          <span class="text-muted small"><?= number_format($total) ?> keluarga</span>
        </div>
      </form>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive opensid-scroll" style="-webkit-overflow-scrolling: touch;">
        <table class="table table-hover table-sm table-striped mb-0 align-middle" style="min-width:720px; font-size:0.85rem;">
          <thead class="table-primary text-nowrap">
            <tr>
              <th class="text-center" style="width:40px">NO</th>
              <th>NO. KK</th>
              <th>KEPALA KELUARGA</th>
              <th>NIK KEPALA</th>
              <th class="text-center">ANGGOTA</th>
              <th>DUSUN</th>
              <th>RT/RW</th>
              <th style="width:70px">AKSI</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($data)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
            <?php else: foreach ($data as $i => $k): ?>
            <tr>
              <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
              <td><code style="font-size:0.8rem"><?= htmlspecialchars($k['no_kk'] ?? '') ?></code></td>
              <td class="fw-semibold text-nowrap"><?= htmlspecialchars($k['nama_kepala'] ?? '-') ?></td>
              <td><code style="font-size:0.8rem"><?= htmlspecialchars($k['nik_kepala_str'] ?? '-') ?></code></td>
              <td class="text-center"><?= (int)($k['jml'] ?? 0) ?></td>
              <td class="text-nowrap"><?= htmlspecialchars($k['dusun'] ?? '-') ?></td>
              <td><?= htmlspecialchars(($k['rt'] ?? '-') . '/' . ($k['rw'] ?? '-')) ?></td>
              <td>
                <a href="detail.php?no_kk=<?= urlencode($k['no_kk'] ?? '') ?>" class="btn btn-sm btn-info text-white py-0 px-2" title="Detail">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($total > 0): ?>
      <div class="d-flex flex-wrap justify-content-between align-items-center p-2 border-top bg-light gap-2">
        <div class="text-muted small">
          Menampilkan <?= $offset + 1 ?> sampai <?= min($offset + $perPage, $total) ?> dari <?= number_format($total) ?> entri
        </div>
        <div class="d-flex align-items-center gap-2">
          <?php
          $qs = buildQs($baseQs);
          $pageBase = $qs ? "?$qs" : "?";
          ?>
          <a class="btn btn-sm btn-outline-primary <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $pageBase . ($qs ? '&' : '') ?>page=<?= max(1, $page - 1) ?>">Sebelumnya</a>
          <span class="small text-muted">Halaman</span>
          <input type="number" class="form-control form-control-sm page-jump" style="width:74px" min="1" max="<?= $totalPages ?>" value="<?= $page ?>" data-base="<?= htmlspecialchars($pageBase) ?>" aria-label="Ke halaman">
          <span class="small text-muted">/ <?= number_format($totalPages) ?></span>
          <a class="btn btn-sm btn-outline-primary <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= $pageBase . ($qs ? '&' : '') ?>page=<?= min($totalPages, $page + 1) ?>">Selanjutnya</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <p class="text-muted small text-center mt-2 d-md-none">
    <i class="fas fa-hand-point-right me-1"></i> Geser tabel ke samping untuk melihat kolom lainnya
  </p>
</div>
</section>
<style>
.opensid-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
.opensid-scroll::-webkit-scrollbar { height: 6px; }
.opensid-scroll::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 3px; }
</style>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
