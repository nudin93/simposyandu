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
 * SIMPOSYANDU - Data Lansia
 * Standar usia: 60–150 tahun | Sumber: OpenSID + manual
 */
$page_title = 'Data Lansia';
require_once __DIR__ . '/../../includes/header.php';

$MIN_TAHUN = 60;
$MAX_TAHUN = 150;
$opensid_ok = opensid_available();
$opensid_list = $opensid_ok ? getPendudukByUsiaOpenSID($MIN_TAHUN, $MAX_TAHUN) : [];

$lokal = [];
try {
    $lokal = fetchAll("SELECT x.*, TIMESTAMPDIFF(YEAR, x.tanggal_lahir, CURDATE()) AS umur_tahun
        FROM `lansia` x
        WHERE (x.status_aktif = 1 OR x.status_aktif IS NULL)
        ORDER BY x.tanggal_lahir DESC") ?: [];
} catch (Throwable $e) { $lokal = []; }

$lokal_by_opensid = [];
$lokal_by_nik = [];
$lokal_only = [];
foreach ($lokal as $b) {
    $ub = (int)($b['umur_tahun'] ?? 0);
    if ($ub < $MIN_TAHUN || $ub > $MAX_TAHUN) continue;
    $oid = (int)($b['id_penduduk_opensid'] ?? 0);
    $nik = trim($b['nik'] ?? '');
    if ($oid > 0) $lokal_by_opensid[$oid] = $b;
    if ($nik !== '') $lokal_by_nik[$nik] = $b;
    if ($oid <= 0) $lokal_only[] = $b;
}

$rows = [];
$seen_nik = [];
foreach ($opensid_list as $p) {
    $oid = (int)$p['id_penduduk'];
    $nik = trim($p['nik'] ?? '');
    $local = $lokal_by_opensid[$oid] ?? ($nik !== '' ? ($lokal_by_nik[$nik] ?? null) : null);
    $rows[] = [
        'sumber' => 'opensid',
        'id_penduduk' => $oid,
        'nik' => $nik,
        'nama' => $p['nama'],
        'jenis_kelamin' => $p['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
        'umur_tahun' => (int)($p['umur_tahun'] ?? 0),
        'dusun' => $p['dusun'] ?? '',
        'local_id' => $local['id'] ?? null,
        'nomor_peserta' => $local['nomor_peserta'] ?? null,
        'terdaftar' => $local ? true : false,
        'extra' => '',
    ];
    if ($nik !== '') $seen_nik[$nik] = true;
}
foreach ($lokal_only as $b) {
    $nik = trim($b['nik'] ?? '');
    if ($nik !== '' && isset($seen_nik[$nik])) continue;
    $rows[] = [
        'sumber' => 'lokal',
        'id_penduduk' => 0,
        'nik' => $nik,
        'nama' => $b['nama'],
        'jenis_kelamin' => $b['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $b['tanggal_lahir'] ?? '',
        'umur_tahun' => (int)($b['umur_tahun'] ?? 0),
        'dusun' => $b['dusun'] ?? '',
        'local_id' => $b['id'],
        'nomor_peserta' => $b['nomor_peserta'] ?? null,
        'terdaftar' => true,
        'extra' => '',
    ];
}
usort($rows, function($a,$b){ return $a['umur_tahun'] <=> $b['umur_tahun']; });

$total = count($rows);
$total_l = count(array_filter($rows, function($r){ return ($r['jenis_kelamin']??'') === 'L'; }));
$total_p = count(array_filter($rows, function($r){ return ($r['jenis_kelamin']??'') === 'P'; }));
$total_terdaftar = count(array_filter($rows, function($r){ return !empty($r['terdaftar']); }));
$total_manual = count(array_filter($rows, function($r){ return $r['sumber'] === 'lokal'; }));
$canInput = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-user-injured me-2 text-warning"></i>Data Lansia</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Data Lansia</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="alert alert-info border-0 shadow-sm">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="flex-grow-1">
        <strong><i class="fas fa-info-circle me-1"></i>Standar sasaran:</strong>
        Data Lansia = usia <strong><?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> tahun</strong>.
        Data dari <strong>OpenSID</strong>. Tambah manual hanya jika belum ada di OpenSID.
      </div>
      <?php if (!$opensid_ok): ?>
        <span class="badge bg-warning text-dark">OpenSID tidak terhubung</span>
      <?php else: ?>
        <span class="badge bg-success">OpenSID terhubung</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-warning"><?= number_format($total) ?></div>
      <small class="text-muted">Total (<?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> th)</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold"><?= number_format($total_l) ?> / <?= number_format($total_p) ?></div>
      <small class="text-muted">Laki-laki / Perempuan</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-success"><?= number_format($total_terdaftar) ?></div>
      <small class="text-muted">Sudah Terdaftar</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-secondary"><?= number_format($total_manual) ?></div>
      <small class="text-muted">Manual</small>
    </div></div></div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white">
      <h5 class="mb-0"><i class="fas fa-list me-2 text-warning"></i>Daftar Data Lansia</h5>
      <?php if ($canInput): ?>
      <a href="<?= APP_URL ?>/modules/lansia/tambah.php" class="btn btn-outline-warning btn-sm"><i class="fas fa-plus me-1"></i>Tambah Manual</a>
      <?php endif; ?>
    </div>
    <div class="card-body"><div class="table-responsive">
      <table class="table table-hover datatable align-middle" style="width:100%">
        <thead class="table-light">
          <tr>
            <th>No</th><th>Nama</th><th>L/P</th><th>Tgl Lahir</th><th>Umur</th><th>NIK</th>
            
            <th>Sumber</th><th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="12" class="text-center text-muted py-4">Tidak ada data usia <?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> tahun.</td></tr>
        <?php else: foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($r['nama']) ?></div>
              <?php if ($r['nomor_peserta']): ?><small class="text-muted"><?= htmlspecialchars($r['nomor_peserta']) ?></small><?php endif; ?>
              <?php if (!empty($r['dusun'])): ?><br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($r['dusun']) ?></small><?php endif; ?>
            </td>
            <td><?php if (($r['jenis_kelamin']??'')==='L'): ?><span class="badge bg-info">L</span><?php elseif (($r['jenis_kelamin']??'')==='P'): ?><span class="badge" style="background:#e91e63">P</span><?php else: ?>-<?php endif; ?></td>
            <td><?= $r['tanggal_lahir'] ? formatTanggal($r['tanggal_lahir']) : '-' ?></td>
            <td><span class="badge bg-secondary"><?= (int)$r['umur_tahun'] ?> th</span></td>
            <td><code class="small"><?= htmlspecialchars($r['nik'] ?: '-') ?></code></td>
            
            <td><?php if ($r['sumber']==='opensid'): ?><span class="badge bg-success">OpenSID</span><?php else: ?><span class="badge bg-secondary">Manual</span><?php endif; ?></td>
            <td><?php if (!empty($r['terdaftar'])): ?><span class="badge bg-primary">Terdaftar</span><?php else: ?><span class="badge bg-warning text-dark">Belum</span><?php endif; ?></td>
            <td class="text-nowrap">
              <?php if (!empty($r['local_id'])): ?>
                <button type="button" class="btn btn-sm btn-primary btn-menu-aksi" data-id="<?= (int)$r['local_id'] ?>" data-nama="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>" data-mode="lokal"><i class="fas fa-bolt me-1"></i>Aksi</button>
              <?php elseif ($r['sumber']==='opensid' && $canInput): ?>
                <button type="button" class="btn btn-sm btn-success btn-menu-aksi" data-id="<?= (int)$r['id_penduduk'] ?>" data-nama="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>" data-mode="opensid"><i class="fas fa-user-plus me-1"></i>Daftarkan</button>
              <?php else: ?><span class="text-muted small">-</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div></section>
<?php
$__app = APP_URL;
$__csrf = csrfToken();
$extra_js = <<<JS
<script>
\$(function(){
  var APP = '{$__app}';
  var CSRF = '{$__csrf}';
  var TYPE = 'lansia';
  function urlAksi(aksi, id) {
    id = parseInt(id,10)||0;
    if (!id) return '#';
    if (aksi === 'pemeriksaan') return APP + '/modules/pemeriksaan_lansia/tambah.php?lansia_id=' + id;
    if (aksi === 'detail') return APP + '/modules/lansia/detail.php?id=' + id;
    if (aksi === 'hapus') return APP + '/ajax/delete.php?type=' + TYPE + '&id=' + id;
    return APP + '/modules/lansia/index.php';
  }
  function menuAksi(nama, id) {
    var hapusUrl = urlAksi('hapus', id);
    Swal.fire({
      title: nama || 'Data Lansia',
      html: '<div class="d-grid gap-2 text-start">' +
        '<a class="btn btn-success" href="' + urlAksi('pemeriksaan', id) + '"><i class="fas fa-stethoscope me-2"></i>Pemeriksaan</a>' +
        '<a class="btn btn-outline-primary" href="' + urlAksi('detail', id) + '"><i class="fas fa-eye me-2"></i>Detail</a>' +
        '<hr class="my-1"><button type="button" class="btn btn-outline-danger btn-hapus-mod" data-url="'+hapusUrl+'" data-nama="'+\$('<div>').text(String(nama||'')).html()+'"><i class="fas fa-trash me-2"></i>Hapus</button></div>',
      showConfirmButton: false, showCloseButton: true, width: 400,
      didOpen: function(){
        var b = document.querySelector('.btn-hapus-mod');
        if (!b) return;
        b.addEventListener('click', function(){
          var u=this.getAttribute('data-url'), n=this.getAttribute('data-nama')||'';
          Swal.close();
          if (typeof confirmDelete==='function') confirmDelete(u,n);
          else if (confirm('Hapus "'+n+'"?')) location.href=u;
        });
      }
    });
  }
  function daftarOS(idP, nama) {
    if (typeof showLoading==='function') showLoading('Mendaftarkan...');
    \$.ajax({
      url: APP+'/ajax/daftar_opensid_modul.php', type:'POST', dataType:'json',
      data: { type: TYPE, id_penduduk_opensid: idP, csrf_token: CSRF }
    }).done(function(r){
      if (typeof hideLoading==='function') hideLoading();
      if (!r||!r.success) { Swal.fire('Gagal', (r&&r.message)||'Gagal daftar','error'); return; }
      var lid = parseInt((r.id!=null)?r.id:(r.data&&r.data.id),10)||0;
      if (!lid) { Swal.fire('Gagal','ID tidak diterima. Refresh halaman.','error'); return; }
      Swal.fire({icon:'success',title:'Berhasil',text:r.message||'Terdaftar',showCancelButton:true,confirmButtonText:'Pemeriksaan',cancelButtonText:'Menu aksi'}).then(function(res){
        if (res.isConfirmed) location.href = urlAksi('pemeriksaan', lid);
        else menuAksi(nama, lid);
      });
    }).fail(function(xhr){
      if (typeof hideLoading==='function') hideLoading();
      var msg='Koneksi bermasalah';
      try {
        var j = JSON.parse(xhr.responseText);
        if (j && j.message) msg = j.message;
      } catch(e) {
        if (xhr.responseText && xhr.responseText.length < 400) msg = xhr.responseText.replace(/<[^>]+>/g,'').trim() || msg;
        else if (xhr.status) msg = 'Error server (HTTP ' + xhr.status + '). Coba refresh halaman.';
      }
      Swal.fire({icon:'error', title:'Gagal mendaftarkan', text: msg});
    });
  }
  \$(document).on('click','.btn-menu-aksi', function(e){
    e.preventDefault();
    var mode=\$(this).data('mode')||'lokal', id=\$(this).data('id'), nama=\$(this).data('nama')||'Data Lansia';
    if (mode==='lokal') { menuAksi(nama, id); return; }
    Swal.fire({title:'Daftarkan?', html:'<strong>'+\$('<div>').text(String(nama)).html()+'</strong> akan didaftarkan ke Posyandu.', icon:'question', showCancelButton:true, confirmButtonText:'Ya, daftarkan', cancelButtonText:'Batal'}).then(function(res){
      if (!res.isConfirmed) return;
      daftarOS(id, nama);
    });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
