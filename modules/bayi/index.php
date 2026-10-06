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
 * SIMPOSYANDU - Data Bayi
 * Standar usia: 0–11 bulan (Kemenkes / Posyandu)
 * Sumber utama: OpenSID + data manual lokal
 * ==========================================================
 */

$page_title = 'Data Bayi (0–11 Bulan)';
require_once __DIR__ . '/../../includes/header.php';

// Standar sasaran bayi Posyandu: 0–11 bulan (< 1 tahun)
$MIN_BULAN = 0;
$MAX_BULAN = 11;

$opensid_ok = opensid_available();
$bayi_opensid = $opensid_ok ? getBalitaOpenSID($MAX_BULAN, $MIN_BULAN) : [];

// Data lokal tabel bayi
$lokal = [];
try {
    $lokal = fetchAll("SELECT b.*, TIMESTAMPDIFF(MONTH, b.tanggal_lahir, CURDATE()) AS umur_bulan
        FROM bayi b
        WHERE (b.status_aktif = 1 OR b.status_aktif IS NULL)
        ORDER BY b.tanggal_lahir DESC") ?: [];
} catch (Throwable $e) {
    $lokal = [];
}

$lokal_by_opensid = [];
$lokal_by_nik = [];
$lokal_only = [];
foreach ($lokal as $b) {
    $ub = (int)($b['umur_bulan'] ?? 0);
    // Hanya yang masih dalam rentang standar bayi
    if ($ub < $MIN_BULAN || $ub > $MAX_BULAN) continue;
    $oid = (int)($b['id_penduduk_opensid'] ?? 0);
    $nik = trim($b['nik_bayi'] ?? '');
    if ($oid > 0) $lokal_by_opensid[$oid] = $b;
    if ($nik !== '') $lokal_by_nik[$nik] = $b;
    if ($oid <= 0) $lokal_only[] = $b;
}

$rows = [];
$seen_nik = [];
foreach ($bayi_opensid as $p) {
    $oid = (int)$p['id_penduduk'];
    $nik = trim($p['nik'] ?? '');
    $local = $lokal_by_opensid[$oid] ?? ($nik !== '' ? ($lokal_by_nik[$nik] ?? null) : null);
    $rows[] = [
        'sumber'        => 'opensid',
        'id_penduduk'   => $oid,
        'nik'           => $nik,
        'no_kk'         => $p['no_kk'] ?? '',
        'nama'          => $p['nama'],
        'jenis_kelamin' => $p['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
        'umur_bulan'    => (int)($p['umur_bulan'] ?? 0),
        'nama_ayah'     => $p['nama_ayah'] ?? '',
        'nama_ibu'      => $p['nama_ibu'] ?? ($local['nama_ibu'] ?? ''),
        'dusun'         => $p['dusun'] ?? '',
        'berat_lahir'   => $local['berat_lahir'] ?? null,
        'panjang_lahir' => $local['panjang_lahir'] ?? null,
        'asi_eksklusif' => $local['asi_eksklusif'] ?? null,
        'local_id'      => $local['id'] ?? null,
        'nomor_peserta' => $local['nomor_peserta'] ?? null,
        'terdaftar'     => $local ? true : false,
    ];
    if ($nik !== '') $seen_nik[$nik] = true;
}

foreach ($lokal_only as $b) {
    $nik = trim($b['nik_bayi'] ?? '');
    if ($nik !== '' && isset($seen_nik[$nik])) continue;
    $rows[] = [
        'sumber'        => 'lokal',
        'id_penduduk'   => 0,
        'nik'           => $nik,
        'no_kk'         => $b['no_kk'] ?? '',
        'nama'          => $b['nama_lengkap'],
        'jenis_kelamin' => $b['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $b['tanggal_lahir'] ?? '',
        'umur_bulan'    => (int)($b['umur_bulan'] ?? 0),
        'nama_ayah'     => $b['nama_ayah'] ?? '',
        'nama_ibu'      => $b['nama_ibu'] ?? '',
        'dusun'         => $b['dusun'] ?? '',
        'berat_lahir'   => $b['berat_lahir'] ?? null,
        'panjang_lahir' => $b['panjang_lahir'] ?? null,
        'asi_eksklusif' => $b['asi_eksklusif'] ?? null,
        'local_id'      => $b['id'],
        'nomor_peserta' => $b['nomor_peserta'] ?? null,
        'terdaftar'     => true,
    ];
}

usort($rows, function ($a, $b) {
    return $a['umur_bulan'] <=> $b['umur_bulan'];
});

$total = count($rows);
$total_l = count(array_filter($rows, fn($r) => ($r['jenis_kelamin'] ?? '') === 'L'));
$total_p = count(array_filter($rows, fn($r) => ($r['jenis_kelamin'] ?? '') === 'P'));
$total_opensid = count(array_filter($rows, fn($r) => $r['sumber'] === 'opensid'));
$total_manual = count(array_filter($rows, fn($r) => $r['sumber'] === 'lokal'));
$total_terdaftar = count(array_filter($rows, fn($r) => !empty($r['terdaftar'])));
$canInput = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-baby me-2 text-info"></i>Data Bayi</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Bayi</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <div class="alert alert-info border-0 shadow-sm">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="flex-grow-1">
        <strong><i class="fas fa-info-circle me-1"></i>Standar sasaran:</strong>
        Bayi = usia <strong>0–<?= $MAX_BULAN ?> bulan</strong> (&lt; 1 tahun) sesuai standar Posyandu/Kemenkes.
        Data diambil dari <strong>OpenSID</strong>. Anak 12–59 bulan ada di menu <em>Balita</em>.
        Tombol <em>Tambah Manual</em> hanya jika belum ada di OpenSID.
      </div>
      <?php if (!$opensid_ok): ?>
        <span class="badge bg-warning text-dark">OpenSID tidak terhubung — data lokal saja</span>
      <?php else: ?>
        <span class="badge bg-success">OpenSID terhubung</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="fs-4 fw-bold text-info"><?= number_format($total) ?></div>
        <small class="text-muted">Total Bayi (0–<?= $MAX_BULAN ?> bln)</small>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="fs-4 fw-bold"><?= number_format($total_l) ?> / <?= number_format($total_p) ?></div>
        <small class="text-muted">Laki-laki / Perempuan</small>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="fs-4 fw-bold text-success"><?= number_format($total_terdaftar) ?></div>
        <small class="text-muted">Sudah Terdaftar Posyandu</small>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="fs-4 fw-bold text-secondary"><?= number_format($total_manual) ?></div>
        <small class="text-muted">Manual (bukan OpenSID)</small>
      </div></div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white">
      <h5 class="mb-0"><i class="fas fa-list me-2 text-info"></i>Daftar Bayi</h5>
      <div class="d-flex gap-2">
        <?php if ($canInput): ?>
        <a href="<?= APP_URL ?>/modules/bayi/tambah.php" class="btn btn-outline-info btn-sm">
          <i class="fas fa-plus me-1"></i>Tambah Manual
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover datatable align-middle" style="width:100%">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama Bayi</th>
              <th>L/P</th>
              <th>Tgl Lahir</th>
              <th>Umur</th>
              <th>NIK</th>
              <th>Orang Tua</th>
              <th>Sumber</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-4">
                Tidak ada data bayi usia 0–<?= $MAX_BULAN ?> bulan.
                <?php if (!$opensid_ok): ?><br>Hubungkan OpenSID atau tambah manual.<?php endif; ?>
              </td>
            </tr>
          <?php else: foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($r['nama']) ?></div>
                <?php if ($r['nomor_peserta']): ?>
                  <small class="text-muted"><?= htmlspecialchars($r['nomor_peserta']) ?></small>
                <?php endif; ?>
                <?php if (!empty($r['dusun'])): ?>
                  <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($r['dusun']) ?></small>
                <?php endif; ?>
              </td>
              <td>
                <?php if (($r['jenis_kelamin'] ?? '') === 'L'): ?>
                  <span class="badge bg-info">L</span>
                <?php elseif (($r['jenis_kelamin'] ?? '') === 'P'): ?>
                  <span class="badge" style="background:#e91e63">P</span>
                <?php else: ?>-<?php endif; ?>
              </td>
              <td><?= $r['tanggal_lahir'] ? formatTanggal($r['tanggal_lahir']) : '-' ?></td>
              <td>
                <span class="badge bg-<?= $r['umur_bulan'] < 6 ? 'warning text-dark' : 'info' ?>">
                  <?= function_exists('formatUmurBalita')
                      ? formatUmurBalita($r['tanggal_lahir'], $r['umur_bulan'])
                      : ((int)$r['umur_bulan'] . ' bln') ?>
                </span>
              </td>
              <td><code class="small"><?= htmlspecialchars($r['nik'] ?: '-') ?></code></td>
              <td class="small">
                <?php if ($r['nama_ayah'] || $r['nama_ibu']): ?>
                  <?= $r['nama_ayah'] ? 'Ayah: ' . htmlspecialchars($r['nama_ayah']) : '' ?>
                  <?= ($r['nama_ayah'] && $r['nama_ibu']) ? '<br>' : '' ?>
                  <?= $r['nama_ibu'] ? 'Ibu: ' . htmlspecialchars($r['nama_ibu']) : '' ?>
                <?php else: ?>-<?php endif; ?>
              </td>
              <td>
                <?php if ($r['sumber'] === 'opensid'): ?>
                  <span class="badge bg-success">OpenSID</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Manual</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($r['terdaftar'])): ?>
                  <span class="badge bg-primary">Terdaftar</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">Belum</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <?php if (!empty($r['local_id'])): ?>
                  <button type="button" class="btn btn-sm btn-primary btn-menu-aksi"
                    data-id="<?= (int)$r['local_id'] ?>"
                    data-nama="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>"
                    data-mode="lokal">
                    <i class="fas fa-bolt me-1"></i>Aksi
                  </button>
                <?php elseif (($r['sumber'] ?? '') === 'opensid' && $canInput): ?>
                  <button type="button" class="btn btn-sm btn-success btn-menu-aksi"
                    data-id="<?= (int)$r['id_penduduk'] ?>"
                    data-nama="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>"
                    data-mode="opensid">
                    <i class="fas fa-user-plus me-1"></i>Daftarkan
                  </button>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</section>

<?php
$__app = APP_URL;
$__csrf = csrfToken();
$extra_js = <<<JS
<script>
\$(function() {
  var APP = '{$__app}';
  var CSRF = '{$__csrf}';

  function urlAksi(aksi, localId) {
    localId = parseInt(localId, 10) || 0;
    if (!localId) return '#';
    if (aksi === 'pemeriksaan') return APP + '/modules/pemeriksaan_bayi/tambah.php?bayi_id=' + localId;
    if (aksi === 'detail')      return APP + '/modules/bayi/detail.php?id=' + localId;
    if (aksi === 'hapus')       return APP + '/ajax/delete.php?type=bayi&id=' + localId;
    return APP + '/modules/bayi/index.php';
  }

  function tampilMenuAksi(nama, localId) {
    localId = parseInt(localId, 10) || 0;
    var hapusUrl = urlAksi('hapus', localId);
    Swal.fire({
      title: nama || 'Bayi',
      html:
        '<p class="text-muted small mb-3">Pilih menu yang ingin dilakukan</p>' +
        '<div class="d-grid gap-2 text-start">' +
        '<a class="btn btn-success" href="' + urlAksi('pemeriksaan', localId) + '"><i class="fas fa-stethoscope me-2"></i>Pemeriksaan Bayi</a>' +
        '<a class="btn btn-outline-primary" href="' + urlAksi('detail', localId) + '"><i class="fas fa-eye me-2"></i>Detail</a>' +
        '<hr class="my-1">' +
        '<button type="button" class="btn btn-outline-danger btn-hapus-bayi" data-url="' + hapusUrl + '" data-nama="' + \$('<div>').text(String(nama || 'Bayi')).html() + '"><i class="fas fa-trash me-2"></i>Hapus Data</button>' +
        '</div>',
      showConfirmButton: false,
      showCloseButton: true,
      width: 420,
      didOpen: function() {
        var btn = document.querySelector('.btn-hapus-bayi');
        if (!btn) return;
        btn.addEventListener('click', function() {
          var url = this.getAttribute('data-url');
          var nm = this.getAttribute('data-nama') || 'Bayi';
          Swal.close();
          if (typeof confirmDelete === 'function') confirmDelete(url, nm);
          else if (confirm('Hapus data "' + nm + '"?')) window.location.href = url;
        });
      }
    });
  }

  function daftarOpenSID(idPenduduk, nama) {
    if (typeof showLoading === 'function') showLoading('Mendaftarkan bayi...');
    \$.ajax({
      url: APP + '/ajax/daftar_bayi_opensid.php',
      type: 'POST',
      dataType: 'json',
      data: { id_penduduk_opensid: idPenduduk, csrf_token: CSRF }
    }).done(function(r) {
      if (typeof hideLoading === 'function') hideLoading();
      if (!r || !r.success) {
        Swal.fire({ icon: 'error', title: 'Gagal mendaftarkan', text: (r && r.message) ? r.message : 'Tidak dapat mendaftarkan' });
        return;
      }
      var localId = (r.id != null) ? r.id : (r.data && r.data.id != null ? r.data.id : null);
      localId = parseInt(localId, 10) || 0;
      if (!localId) {
        Swal.fire('Gagal', 'ID lokal tidak diterima. Refresh halaman.', 'error');
        return;
      }
      Swal.fire({
        icon: 'success',
        title: 'Berhasil didaftarkan',
        text: r.message || 'Bayi sudah terdaftar di Posyandu',
        showCancelButton: true,
        confirmButtonText: 'Pemeriksaan',
        cancelButtonText: 'Menu aksi'
      }).then(function(res) {
        if (res.isConfirmed) window.location.href = urlAksi('pemeriksaan', localId);
        else tampilMenuAksi(nama, localId);
      });
    }).fail(function(xhr) {
      if (typeof hideLoading === 'function') hideLoading();
      var msg = 'Koneksi bermasalah';
      try { var j = JSON.parse(xhr.responseText); if (j && j.message) msg = j.message; } catch (e) {}
      Swal.fire({ icon: 'error', title: 'Error', text: msg });
    });
  }

  \$(document).on('click', '.btn-menu-aksi', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var \$btn = \$(this);
    var mode = \$btn.data('mode') || 'lokal';
    var id = \$btn.data('id');
    var nama = \$btn.data('nama') || 'Bayi';

    if (mode === 'lokal') {
      tampilMenuAksi(nama, id);
      return;
    }

    Swal.fire({
      title: 'Daftarkan Bayi?',
      html: 'Anak <strong>' + \$('<div>').text(String(nama)).html() + '</strong> (usia 0–11 bulan) akan didaftarkan ke Posyandu.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, daftarkan',
      cancelButtonText: 'Batal'
    }).then(function(res) {
      if (!res.isConfirmed) return;
      daftarOpenSID(id, nama);
    });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
