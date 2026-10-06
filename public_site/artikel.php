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

require_once __DIR__ . '/_init_public.php';
$page_title = 'Artikel · ' . $nama_posyandu;
$list = [];
try {
    $list = fetchAll("SELECT id, judul, isi_artikel, kategori, tanggal_publish, created_at
        FROM artikel WHERE status='diterbitkan'
        ORDER BY COALESCE(tanggal_publish, created_at) DESC LIMIT 24") ?: [];
} catch (Throwable $e) {}
include __DIR__ . '/_layout_top.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Artikel Kesehatan</h1>
    <?php if (empty($list)): ?>
      <div class="panel panel-b text-muted">Belum ada artikel yang diterbitkan.</div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($list as $a):
        $ringkas = mb_substr(strip_tags($a['isi_artikel'] ?? ''), 0, 140);
        if (mb_strlen(strip_tags($a['isi_artikel'] ?? '')) > 140) $ringkas .= '…';
      ?>
      <div class="col-md-6 col-lg-4">
        <article class="artikel-card">
          <div class="body">
            <?php if (!empty($a['kategori'])): ?>
              <span class="badge mb-2" style="background:#f1f5f9;color:#475569"><?= htmlspecialchars($a['kategori']) ?></span>
            <?php endif; ?>
            <h3><?= htmlspecialchars($a['judul'] ?? '') ?></h3>
            <p><?= htmlspecialchars($ringkas) ?></p>
            <a href="artikel_detail.php?id=<?= (int)$a['id'] ?>" class="small fw-semibold text-decoration-none" style="color:#0f766e">Baca selengkapnya →</a>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/_layout_bottom.php'; ?>
