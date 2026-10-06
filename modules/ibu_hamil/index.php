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
 * Data Ibu Hamil – sumber utama OpenSID (perempuan usia subur 15–49 th).
 * Input manual hanya jika belum ada di OpenSID.
 * Tombol Daftarkan / Aksi seperti modul Balita.
 *
 * ==========================================================
 */

$page_title = 'Data Ibu Hamil';
require_once __DIR__ . '/../../includes/header.php';

// Wanita usia subur (WUS): 15–49 tahun
$MIN_TAHUN = 15;
$MAX_TAHUN = 49;

$opensid_ok = opensid_available();
$perempuan_opensid = $opensid_ok ? getPerempuanOpenSID($MIN_TAHUN, $MAX_TAHUN) : [];

// Data lokal ibu hamil
$lokal = fetchAll("SELECT i.*,
        TIMESTAMPDIFF(YEAR, i.tanggal_lahir, CURDATE()) AS umur_tahun,
        TIMESTAMPDIFF(WEEK, i.hpht, CURDATE()) AS usia_minggu
    FROM ibu_hamil i
    WHERE (i.status_aktif = 1 OR i.status_aktif IS NULL)
    ORDER BY i.created_at DESC") ?: [];

$lokal_by_opensid = [];
$lokal_by_nik = [];
$lokal_only = [];
foreach ($lokal as $b) {
    $oid = (int)($b['id_penduduk_opensid'] ?? 0);
    $nik = trim($b['nik'] ?? '');
    if ($oid > 0) $lokal_by_opensid[$oid] = $b;
    if ($nik !== '') $lokal_by_nik[$nik] = $b;
    if ($oid <= 0) $lokal_only[] = $b;
}

// Gabungan: OpenSID (perempuan) + lokal manual
$rows = [];
$seen_nik = [];
foreach ($perempuan_opensid as $p) {
    $oid = (int)$p['id_penduduk'];
    $nik = trim($p['nik'] ?? '');
    $local = $lokal_by_opensid[$oid] ?? ($nik !== '' ? ($lokal_by_nik[$nik] ?? null) : null);
    $rows[] = [
        'sumber'        => 'opensid',
        'id_penduduk'   => $oid,
        'nik'           => $nik,
        'no_kk'         => $p['no_kk'] ?? '',
        'nama'          => $p['nama'],
        'jenis_kelamin' => 'P',
        'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
        'umur_tahun'    => (int)($p['umur_tahun'] ?? 0),
        'no_hp'         => $p['no_hp'] ?? '',
        'dusun'         => $p['dusun'] ?? '',
        'nama_suami'    => $local['nama_suami'] ?? '',
        'hpht'          => $local['hpht'] ?? null,
        'hpl'           => $local['hpl'] ?? null,
        'usia_minggu'   => $local['usia_minggu'] ?? null,
        'kehamilan_ke'  => $local['kehamilan_ke'] ?? null,
        'local_id'      => $local['id'] ?? null,
        'nomor_peserta' => $local['nomor_peserta'] ?? null,
        'terdaftar'     => $local ? true : false,
    ];
    if ($nik !== '') $seen_nik[$nik] = true;
}

// Manual (tidak tertaut OpenSID)
foreach ($lokal_only as $b) {
    $nik = trim($b['nik'] ?? '');
    if ($nik !== '' && isset($seen_nik[$nik])) continue;
    $rows[] = [
        'sumber'        => 'lokal',
        'id_penduduk'   => 0,
        'nik'           => $nik,
        'no_kk'         => $b['no_kk'] ?? '',
        'nama'          => $b['nama'],
        'jenis_kelamin' => 'P',
        'tanggal_lahir' => $b['tanggal_lahir'] ?? '',
        'umur_tahun'    => (int)($b['umur_tahun'] ?? 0),
        'no_hp'         => $b['no_hp'] ?? '',
        'dusun'         => $b['dusun'] ?? '',
        'nama_suami'    => $b['nama_suami'] ?? '',
        'hpht'          => $b['hpht'] ?? null,
        'hpl'           => $b['hpl'] ?? null,
        'usia_minggu'   => $b['usia_minggu'] ?? null,
        'kehamilan_ke'  => $b['kehamilan_ke'] ?? null,
        'local_id'      => $b['id'],
        'nomor_peserta' => $b['nomor_peserta'] ?? null,
        'terdaftar'     => true,
    ];
}

// Urut: terdaftar dulu, lalu nama
usort($rows, function ($a, $b) {
    if ($a['terdaftar'] !== $b['terdaftar']) {
        return $a['terdaftar'] ? -1 : 1;
    }
    return strcasecmp($a['nama'] ?? '', $b['nama'] ?? '');
});

$total = count($rows);
$total_opensid = count(array_filter($rows, fn($r) => $r['sumber'] === 'opensid'));
$total_manual = count(array_filter($rows, fn($r) => $r['sumber'] === 'lokal'));
$total_terdaftar = count(array_filter($rows, fn($r) => !empty($r['terdaftar'])));
$total_belum = $total - $total_terdaftar;
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-female me-2 text-danger"></i>Data Ibu Hamil</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Ibu Hamil</li>
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
        <strong><i class="fas fa-info-circle me-1"></i>Sumber data:</strong>
        Daftar diambil dari <strong>OpenSID</strong> — penduduk <strong>perempuan</strong> usia <strong><?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> tahun</strong> (wanita usia subur).
        Klik <em>Daftarkan</em> untuk mendaftarkan ke Posyandu, lalu lengkapi HPHT/kehamilan.
        Tombol <em>Tambah Manual</em> hanya untuk yang <u>belum</u> terdata di OpenSID.
      </div>
      <?php if (!$opensid_ok): ?>
        <span class="badge bg-warning text-dark">OpenSID tidak terhubung — menampilkan data lokal saja</span>
      <?php else: ?>
        <span class="badge bg-success">OpenSID terhubung</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
        <div class="fs-4 fw-bold text-danger"><?= number_format($total) ?></div>
        <small class="text-muted">Total Perempuan (<?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> th)</small>
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
        <div class="fs-4 fw-bold text-warning"><?= number_format($total_belum) ?></div>
        <small class="text-muted">Belum Didaftarkan</small>
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
      <h5 class="mb-0"><i class="fas fa-list me-2 text-danger"></i>Daftar Ibu Hamil / Calon</h5>
      <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/modules/ibu_hamil/tambah.php" class="btn btn-outline-danger btn-sm">
          <i class="fas fa-plus me-1"></i>Tambah Manual
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover datatable align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>NIK</th>
              <th>Umur</th>
              <th>HPHT / Usia Kandungan</th>
              <th>Suami</th>
              <th>Sumber</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-4">
                Tidak ada data perempuan usia <?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> tahun.
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
              <td><code class="small"><?= htmlspecialchars($r['nik'] ?: '-') ?></code></td>
              <td>
                <?php if ($r['umur_tahun']): ?>
                  <span class="badge bg-secondary"><?= (int)$r['umur_tahun'] ?> th</span>
                <?php else: ?>-<?php endif; ?>
              </td>
              <td class="small">
                <?php if (!empty($r['hpht'])): ?>
                  <?= formatTanggal($r['hpht']) ?>
                  <?php if (!empty($r['usia_minggu'])): ?>
                    <br><span class="badge bg-info text-dark"><?= (int)$r['usia_minggu'] ?> minggu</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td class="small"><?= $r['nama_suami'] ? htmlspecialchars($r['nama_suami']) : '-' ?></td>
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
                <?php elseif (($r['sumber'] ?? '') === 'opensid'): ?>
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
    if (aksi === 'pemeriksaan') return APP + '/modules/pemeriksaan_ibu_hamil/tambah.php?ibu_hamil_id=' + localId;
    if (aksi === 'detail')      return APP + '/modules/ibu_hamil/detail.php?id=' + localId;
    if (aksi === 'edit')        return APP + '/modules/ibu_hamil/edit.php?id=' + localId;
    if (aksi === 'kartu')       return APP + '/modules/kartu/cetak.php?type=ibu_hamil&id=' + localId;
    if (aksi === 'hapus')       return APP + '/ajax/delete.php?type=ibu_hamil&id=' + localId;
    return APP + '/modules/ibu_hamil/index.php';
  }

  function tampilMenuAksi(nama, localId) {
    localId = parseInt(localId, 10) || 0;
    var hapusUrl = urlAksi('hapus', localId);
    Swal.fire({
      title: nama || 'Ibu Hamil',
      html:
        '<p class="text-muted small mb-3">Pilih menu yang ingin dilakukan</p>' +
        '<div class="d-grid gap-2 text-start">' +
        '<a class="btn btn-success" href="' + urlAksi('pemeriksaan', localId) + '"><i class="fas fa-stethoscope me-2"></i>Pemeriksaan ANC</a>' +
        '<a class="btn btn-outline-primary" href="' + urlAksi('detail', localId) + '"><i class="fas fa-eye me-2"></i>Detail</a>' +
        '<a class="btn btn-outline-warning" href="' + urlAksi('edit', localId) + '"><i class="fas fa-edit me-2"></i>Edit Data (HPHT, dll)</a>' +
        '<a class="btn btn-outline-secondary" href="' + urlAksi('kartu', localId) + '" target="_blank"><i class="fas fa-id-card me-2"></i>Cetak Kartu</a>' +
        '<hr class="my-1">' +
        '<button type="button" class="btn btn-outline-danger btn-hapus-ibu" data-url="' + hapusUrl + '" data-nama="' + \$('<div>').text(String(nama || 'Ibu Hamil')).html() + '"><i class="fas fa-trash me-2"></i>Hapus Data</button>' +
        '</div>',
      showConfirmButton: false,
      showCloseButton: true,
      width: 420,
      didOpen: function() {
        var btn = document.querySelector('.btn-hapus-ibu');
        if (!btn) return;
        btn.addEventListener('click', function() {
          var url = this.getAttribute('data-url');
          var nm = this.getAttribute('data-nama') || 'Ibu Hamil';
          Swal.close();
          if (typeof confirmDelete === 'function') {
            confirmDelete(url, nm);
          } else if (confirm('Hapus data "' + nm + '"?')) {
            window.location.href = url;
          }
        });
      }
    });
  }

  function hitungPreviewHPL(hphtStr) {
    if (!hphtStr) return { hpl: '', minggu: '' };
    try {
      var d = new Date(hphtStr + 'T00:00:00');
      if (isNaN(d.getTime())) return { hpl: '', minggu: '' };
      var hpl = new Date(d.getTime());
      hpl.setDate(hpl.getDate() + 280);
      var y = hpl.getFullYear();
      var m = String(hpl.getMonth() + 1).padStart(2, '0');
      var day = String(hpl.getDate()).padStart(2, '0');
      var today = new Date();
      today.setHours(0,0,0,0);
      var minggu = Math.floor((today - d) / (7 * 86400000));
      if (minggu < 0) minggu = 0;
      return { hpl: y + '-' + m + '-' + day, minggu: minggu };
    } catch (e) {
      return { hpl: '', minggu: '' };
    }
  }

  function daftarOpenSID(idPenduduk, nama, formData) {
    if (typeof showLoading === 'function') showLoading('Mendaftarkan ke Posyandu...');
    var payload = {
      id_penduduk_opensid: idPenduduk,
      csrf_token: CSRF,
      hpht: formData.hpht || '',
      kehamilan_ke: formData.kehamilan_ke || 1,
      nama_suami: formData.nama_suami || '',
      nik_suami: formData.nik_suami || '',
      pekerjaan_suami: formData.pekerjaan_suami || '',
      no_hp_suami: formData.no_hp_suami || ''
    };
    \$.ajax({
      url: APP + '/ajax/daftar_ibu_hamil_opensid.php',
      type: 'POST',
      dataType: 'json',
      data: payload
    }).done(function(r) {
      if (typeof hideLoading === 'function') hideLoading();
      if (!r || !r.success) {
        Swal.fire('Gagal', (r && r.message) ? r.message : 'Tidak dapat mendaftarkan', 'error');
        return;
      }
      var localId = (r.id != null) ? r.id : (r.data && r.data.id != null ? r.data.id : null);
      localId = parseInt(localId, 10) || 0;
      if (!localId) {
        Swal.fire('Gagal', 'ID lokal tidak diterima dari server', 'error');
        return;
      }
      var usia = (r.data && r.data.usia_kehamilan != null) ? r.data.usia_kehamilan : '-';
      var hpl = (r.data && r.data.hpl) ? r.data.hpl : '-';
      var suami = (r.data && r.data.nama_suami) ? r.data.nama_suami : '';
      Swal.fire({
        icon: 'success',
        title: 'Berhasil didaftarkan',
        html: '<p class="mb-1">' + (r.message || '') + '</p>' +
          '<p class="mb-0 small text-muted">Usia kehamilan: <strong>' + usia + ' minggu</strong> · HPL: <strong>' + hpl + '</strong>' +
          (suami ? '<br>Suami: <strong>' + \$('<div>').text(suami).html() + '</strong> (otomatis dari OpenSID)' : '') + '</p>',
        showCancelButton: true,
        confirmButtonText: 'Pemeriksaan ANC',
        cancelButtonText: 'Menu aksi'
      }).then(function(res) {
        if (res.isConfirmed) {
          window.location.href = urlAksi('pemeriksaan', localId);
        } else {
          tampilMenuAksi(nama, localId);
        }
      });
    }).fail(function(xhr) {
      if (typeof hideLoading === 'function') hideLoading();
      var msg = 'Koneksi bermasalah';
      try {
        var j = JSON.parse(xhr.responseText);
        if (j.message) msg = j.message;
      } catch (e) {}
      Swal.fire('Error', msg, 'error');
    });
  }

  function formDaftarKehamilan(idPenduduk, nama) {
    var safeNama = \$('<div>').text(String(nama || '')).html();
    Swal.fire({
      title: 'Daftarkan Ibu Hamil',
      html:
        '<p class="text-start small mb-2">Mendaftarkan <strong>' + safeNama + '</strong></p>' +
        '<div id="swal-suami-info" class="text-start alert alert-secondary py-2 small mb-2">Memeriksa data suami di OpenSID...</div>' +
        '<div class="text-start">' +
        '<label class="form-label fw-semibold">HPHT <span class="text-danger">*</span></label>' +
        '<input type="date" id="swal-hpht" class="form-control mb-2" required>' +
        '<label class="form-label fw-semibold">Kehamilan ke-</label>' +
        '<input type="number" id="swal-kehamilan" class="form-control mb-2" value="1" min="1" max="20">' +
        '<div class="alert alert-light border py-2 mb-3 small">' +
        'HPL (otomatis): <strong id="swal-hpl">-</strong><br>' +
        'Usia kehamilan: <strong id="swal-usia">-</strong>' +
        '</div>' +
        '<hr class="my-2">' +
        '<p class="fw-semibold mb-2"><i class="fas fa-male me-1"></i>Data Suami</p>' +
        '<label class="form-label">Nama Suami</label>' +
        '<input type="text" id="swal-nama-suami" class="form-control mb-2" placeholder="Nama lengkap suami">' +
        '<label class="form-label">NIK Suami</label>' +
        '<input type="text" id="swal-nik-suami" class="form-control mb-2" maxlength="16" placeholder="16 digit NIK">' +
        '<label class="form-label">Pekerjaan Suami</label>' +
        '<input type="text" id="swal-pek-suami" class="form-control mb-2" placeholder="Pekerjaan">' +
        '<label class="form-label">No. HP Suami</label>' +
        '<input type="text" id="swal-hp-suami" class="form-control mb-1" placeholder="08xxxxxxxxxx">' +
        '<p class="text-muted small mb-0" id="swal-suami-hint">Jika ibu Kepala Keluarga / pisah KK, isi suami secara manual.</p>' +
        '</div>',
      showCancelButton: true,
      confirmButtonText: 'Daftarkan',
      cancelButtonText: 'Batal',
      focusConfirm: false,
      width: 480,
      didOpen: function() {
        var hphtEl = document.getElementById('swal-hpht');
        function updatePreview() {
          var p = hitungPreviewHPL(hphtEl.value);
          document.getElementById('swal-hpl').textContent = p.hpl || '-';
          document.getElementById('swal-usia').textContent = (p.minggu !== '' ? p.minggu + ' minggu' : '-');
        }
        hphtEl.addEventListener('change', updatePreview);
        hphtEl.addEventListener('input', updatePreview);

        // Cek suami dari OpenSID
        \$.getJSON(APP + '/ajax/get_suami_opensid.php', { id_penduduk: idPenduduk }, function(sr) {
          var box = document.getElementById('swal-suami-info');
          if (!box) return;
          if (!sr || !sr.success) {
            box.className = 'text-start alert alert-warning py-2 small mb-2';
            box.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Tidak dapat cek OpenSID. Isi data suami manual.';
            return;
          }
          var statusKK = (sr.penduduk && sr.penduduk.status_dalam_keluarga) ? sr.penduduk.status_dalam_keluarga : '';
          if (sr.manual_required) {
            box.className = 'text-start alert alert-warning py-2 small mb-2';
            box.innerHTML = '<i class="fas fa-edit me-1"></i><strong>Isi suami manual.</strong> ' +
              (sr.alasan || 'Ibu Kepala Keluarga / pisah KK dengan suami.') +
              (statusKK ? ' <span class="text-muted">(Status di KK: ' + statusKK + ')</span>' : '');
          } else if (sr.data) {
            box.className = 'text-start alert alert-success py-2 small mb-2';
            box.innerHTML = '<i class="fas fa-check-circle me-1"></i>Suami ditemukan di KK OpenSID' +
              (statusKK ? ' <span class="text-muted">(Status ibu: ' + statusKK + ')</span>' : '') +
              '. Anda masih bisa mengubah jika tidak sesuai.';
            document.getElementById('swal-nama-suami').value = sr.data.nama || '';
            document.getElementById('swal-nik-suami').value = sr.data.nik || '';
            document.getElementById('swal-pek-suami').value = sr.data.pekerjaan || '';
            document.getElementById('swal-hp-suami').value = sr.data.no_hp || '';
          } else {
            box.className = 'text-start alert alert-warning py-2 small mb-2';
            box.innerHTML = '<i class="fas fa-edit me-1"></i>Suami tidak ditemukan di KK. Isi manual.';
          }
        }).fail(function() {
          var box = document.getElementById('swal-suami-info');
          if (box) {
            box.className = 'text-start alert alert-warning py-2 small mb-2';
            box.innerHTML = '<i class="fas fa-edit me-1"></i>Gagal cek OpenSID. Isi data suami manual.';
          }
        });
      },
      preConfirm: function() {
        var hpht = document.getElementById('swal-hpht').value;
        var kehamilan = parseInt(document.getElementById('swal-kehamilan').value, 10) || 1;
        if (!hpht) {
          Swal.showValidationMessage('HPHT wajib diisi agar usia kehamilan dapat dihitung');
          return false;
        }
        return {
          hpht: hpht,
          kehamilan_ke: kehamilan,
          nama_suami: document.getElementById('swal-nama-suami').value.trim(),
          nik_suami: document.getElementById('swal-nik-suami').value.trim(),
          pekerjaan_suami: document.getElementById('swal-pek-suami').value.trim(),
          no_hp_suami: document.getElementById('swal-hp-suami').value.trim()
        };
      }
    }).then(function(res) {
      if (!res.isConfirmed || !res.value) return;
      daftarOpenSID(idPenduduk, nama, res.value);
    });
  }

  \$(document).on('click', '.btn-menu-aksi', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var \$btn = \$(this);
    var mode = \$btn.data('mode') || 'lokal';
    var id   = \$btn.data('id');
    var nama = \$btn.data('nama') || 'Ibu Hamil';

    if (mode === 'lokal') {
      tampilMenuAksi(nama, id);
      return;
    }

    formDaftarKehamilan(id, nama);
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
