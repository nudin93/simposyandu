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
?>
<footer class="pub-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <h6 class="text-white mb-2"><?= htmlspecialchars($nama_posyandu) ?></h6>
        <p class="mb-1 opacity-75 small"><?= htmlspecialchars($nama_desa) ?><?= $kecamatan ? ', ' . htmlspecialchars($kecamatan) : '' ?></p>
        <p class="mb-0 opacity-75 small"><?= htmlspecialchars(trim(($kabupaten ? $kabupaten . ', ' : '') . $provinsi)) ?></p>
      </div>
      <div class="col-md-3">
        <h6 class="text-white mb-2">Menu</h6>
        <ul class="list-unstyled small mb-0">
          <li><a href="index.php">Beranda</a></li>
          <li><a href="statistik.php">Statistik</a></li>
          <li><a href="jadwal.php">Jadwal Posyandu</a></li>
          <li><a href="artikel.php">Artikel Kesehatan</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="text-white mb-2">Kontak</h6>
        <p class="small mb-1 opacity-75">
          <?php
 if ($kontak): ?><i class="fas fa-phone me-1"></i><?= htmlspecialchars($kontak) ?><br><?php endif; ?>
          <?php if ($email): ?><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($email) ?><?php endif; ?>
          <?php if (!$kontak && !$email): ?>Hubungi kader Posyandu desa.<?php endif; ?>
        </p>
      </div>
    </div>
    <hr class="border-secondary opacity-25 my-3">
    <div class="d-flex flex-wrap justify-content-between gap-2 small opacity-75">
      <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($nama_posyandu) ?></span>
      <span><?= htmlspecialchars($app_name) ?> · Data statistik publik (tanpa identitas pribadi)</span>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
