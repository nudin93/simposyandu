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
$id = (int)($_GET['id'] ?? 0);
$a = null;
if ($id > 0) {
    try {
        $a = fetchOne("SELECT * FROM artikel WHERE id=$id AND status='diterbitkan' LIMIT 1");
    } catch (Throwable $e) {}
}
if (!$a) {
    header('Location: artikel.php');
    exit;
}
$page_title = ($a['judul'] ?? 'Artikel') . ' · ' . $nama_posyandu;
include __DIR__ . '/_layout_top.php';
?>
<section class="section">
  <div class="container" style="max-width:760px">
    <a href="artikel.php" class="small text-decoration-none" style="color:#0f766e"><i class="fas fa-arrow-left me-1"></i>Kembali ke artikel</a>
    <h1 class="mt-2 mb-2" style="font-size:1.45rem;font-weight:700"><?= htmlspecialchars($a['judul'] ?? '') ?></h1>
    <p class="small text-muted mb-3">
      <?php if (!empty($a['kategori'])): ?><span class="badge me-1" style="background:#f1f5f9;color:#475569"><?= htmlspecialchars($a['kategori']) ?></span><?php endif; ?>
      <?= htmlspecialchars($a['tanggal_publish'] ?? substr($a['created_at'] ?? '', 0, 10)) ?>
    </p>
    <div class="panel panel-b" style="line-height:1.7">
      <?= nl2br(htmlspecialchars($a['isi_artikel'] ?? '')) ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_layout_bottom.php'; ?>
