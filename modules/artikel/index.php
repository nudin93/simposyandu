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

$page_title = 'Artikel Saya';
require_once __DIR__ . '/../../includes/header.php';

$user = currentUser();
$userId = (int)($user['id'] ?? 0);
$isAdm = isAdmin();

if ($isAdm) {
    $data = fetchAll("
        SELECT a.*, k.nama AS nama_penulis
        FROM artikel a
        LEFT JOIN kader k ON k.id = a.penulis_id
        ORDER BY a.created_at DESC
    ");
} else {
    $data = fetchAll("
        SELECT a.*, k.nama AS nama_penulis
        FROM artikel a
        LEFT JOIN kader k ON k.id = a.penulis_id
        WHERE a.penulis_id = $userId
        ORDER BY a.created_at DESC
    ");
}

$statusBadge = [
    'draft'       => 'secondary',
    'menunggu'    => 'warning',
    'diterbitkan' => 'success',
    'ditolak'     => 'danger',
];
$statusLabel = [
    'draft'       => 'Draft',
    'menunggu'    => 'Menunggu',
    'diterbitkan' => 'Diterbitkan',
    'ditolak'     => 'Ditolak',
];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-newspaper me-2 text-primary"></i><?= $isAdm ? 'Semua Artikel' : 'Artikel Saya' ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Artikel</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">
  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Artikel</h5>
      <?php if (canInput()): ?>
      <a href="tambah.php" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Artikel
      </a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover datatable" style="width:100%">
          <thead>
            <tr>
              <th>No</th>
              <th>Gambar</th>
              <th>Judul</th>
              <th>Kategori</th>
              <?php if ($isAdm): ?><th>Penulis</th><?php endif; ?>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($data as $i => $r):
              $st = $r['status'] ?? 'draft';
              $badge = $statusBadge[$st] ?? 'secondary';
              $label = $statusLabel[$st] ?? ucfirst($st);
              $canEdit = $isAdm || in_array($st, ['draft', 'ditolak', 'menunggu'], true);
              $canDel  = $isAdm || ($st !== 'diterbitkan');
          ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <?php if (!empty($r['gambar'])): ?>
                  <img src="<?= APP_URL ?>/uploads/artikel/<?= htmlspecialchars($r['gambar']) ?>"
                       alt="" width="48" height="48" class="rounded" style="object-fit:cover">
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($r['judul']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($r['slug']) ?></small>
              </td>
              <td><span class="badge bg-info"><?= htmlspecialchars($r['kategori']) ?></span></td>
              <?php if ($isAdm): ?>
              <td><?= htmlspecialchars($r['nama_penulis'] ?? '-') ?></td>
              <?php endif; ?>
              <td><span class="badge bg-<?= $badge ?>"><?= $label ?></span></td>
              <td>
                <?php
                  $tgl = $r['tanggal_publish'] ?: substr($r['created_at'] ?? '', 0, 10);
                  echo formatTanggal($tgl);
                ?>
              </td>
              <td class="text-nowrap">
                <a href="detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-info" title="Lihat">
                  <i class="fas fa-eye"></i>
                </a>
                <?php if ($canEdit && canInput()): ?>
                <a href="edit.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <?php endif; ?>
                <?php if ($canDel && canInput()): ?>
                <button type="button" class="btn btn-sm btn-danger"
                        onclick="confirmDelete('<?= APP_URL ?>/ajax/delete_artikel.php?id=<?= (int)$r['id'] ?>&csrf_token=<?= urlencode(csrfToken()) ?>','<?= htmlspecialchars($r['judul'], ENT_QUOTES) ?>')"
                        title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
