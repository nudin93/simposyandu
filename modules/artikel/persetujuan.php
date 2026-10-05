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

$page_title = 'Persetujuan Artikel';
require_once __DIR__ . '/../../includes/header.php';
if (!isAdmin()) {
    echo '<script>window.location="' . APP_URL . '/dashboard.php";</script>';
    exit;
}

$data = fetchAll("
    SELECT a.*, k.nama AS nama_penulis
    FROM artikel a
    LEFT JOIN kader k ON k.id = a.penulis_id
    WHERE a.status = 'menunggu'
    ORDER BY a.created_at ASC
");
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-check-double me-2 text-warning"></i>Persetujuan Artikel</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="index.php">Artikel</a></li>
          <li class="breadcrumb-item active">Persetujuan</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">
  <?php if (empty($data)): ?>
  <div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 text-muted">
      <i class="fas fa-inbox fa-3x mb-3"></i>
      <p class="mb-0">Tidak ada artikel yang menunggu persetujuan.</p>
    </div>
  </div>
  <?php else: ?>
  <div class="row">
    <?php foreach ($data as $r): ?>
    <div class="col-12 col-lg-6 mb-3">
      <div class="card shadow-sm border-0 h-100">
        <?php if (!empty($r['gambar'])): ?>
        <img src="<?= APP_URL ?>/uploads/artikel/<?= htmlspecialchars($r['gambar']) ?>"
             class="card-img-top" alt="" style="height:180px;object-fit:cover">
        <?php endif; ?>
        <div class="card-body">
          <span class="badge bg-info mb-2"><?= htmlspecialchars($r['kategori']) ?></span>
          <h5 class="card-title"><?= htmlspecialchars($r['judul']) ?></h5>
          <p class="text-muted small mb-2">
            <i class="fas fa-user me-1"></i><?= htmlspecialchars($r['nama_penulis'] ?? '-') ?>
            · <i class="fas fa-clock me-1"></i><?= formatTanggal(substr($r['created_at'], 0, 10)) ?>
          </p>
          <div class="border rounded p-2 bg-light mb-3" style="max-height:160px;overflow:auto;white-space:pre-wrap;font-size:0.9rem">
            <?= nl2br(htmlspecialchars(mb_substr($r['isi_artikel'], 0, 600))) ?>
            <?= mb_strlen($r['isi_artikel']) > 600 ? '…' : '' ?>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <a href="detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-info">
              <i class="fas fa-eye me-1"></i>Baca Lengkap
            </a>
            <button type="button" class="btn btn-sm btn-success"
                    onclick="aksiArtikel(<?= (int)$r['id'] ?>,'setujui')">
              <i class="fas fa-check me-1"></i>Terima
            </button>
            <button type="button" class="btn btn-sm btn-danger"
                    onclick="aksiArtikel(<?= (int)$r['id'] ?>,'tolak')">
              <i class="fas fa-times me-1"></i>Tolak
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</section>
<script>
function aksiArtikel(id, aksi) {
  var label = aksi === 'setujui' ? 'menerbitkan' : 'menolak';
  if (!confirm('Yakin ' + label + ' artikel ini?')) return;
  var fd = new FormData();
  fd.append('id', id);
  fd.append('aksi', aksi);
  fd.append('csrf_token', <?= json_encode(csrfToken()) ?>);
  fetch('<?= APP_URL ?>/ajax/update_artikel.php', {
    method: 'POST',
    body: fd,
    credentials: 'same-origin'
  })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if (res.success) {
        if (typeof showSuccess === 'function') showSuccess(res.message, 'persetujuan.php');
        else { alert(res.message); location.reload(); }
      } else {
        if (typeof showError === 'function') showError(res.message);
        else alert(res.message);
      }
    })
    .catch(function(){ alert('Terjadi kesalahan jaringan'); });
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
