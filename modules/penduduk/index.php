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

$page_title = 'Data Penduduk (OpenSID)';
require_once __DIR__ . '/../../includes/header.php';

$search       = trim($_GET['q'] ?? '');
$jkFilter     = $_GET['jk'] ?? '';
$dusunFilter  = trim($_GET['dusun'] ?? '');
$statusFilter = $_GET['status'] ?? '1';
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = max(10, min(100, (int)($_GET['per_page'] ?? 10)));
$offset       = ($page - 1) * $perPage;

$data      = [];
$total     = 0;
$opensidOk = opensid_available();
$dusunList = [];

if ($opensidOk) {
    $dusunList = opensid_fetchAll("SELECT DISTINCT dusun FROM tweb_wil_clusterdesa WHERE dusun IS NOT NULL AND dusun != '' AND dusun != '0' ORDER BY dusun");

    $where = "1=1";
    if ($statusFilter !== '' && $statusFilter !== 'all') {
        $where .= " AND p.status_dasar = " . (int)$statusFilter;
    } else {
        $where .= " AND p.status_dasar = 1";
    }
    if ($search !== '') {
        $s = opensid_escape($search);
        $where .= " AND (p.nama LIKE '%$s%' OR p.nik LIKE '%$s%' OR IFNULL(k.no_kk,'') LIKE '%$s%' OR IFNULL(w.dusun,'') LIKE '%$s%' OR IFNULL(p.nama_ayah,'') LIKE '%$s%' OR IFNULL(p.nama_ibu,'') LIKE '%$s%')";
    }
    if ($jkFilter === 'L') $where .= " AND p.sex = 1";
    if ($jkFilter === 'P') $where .= " AND p.sex = 2";
    if ($dusunFilter !== '') {
        $df = opensid_escape($dusunFilter);
        $where .= " AND w.dusun = '$df'";
    }

    $countRow = opensid_fetchOne("SELECT COUNT(*) AS c FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        WHERE $where");
    $total = (int)($countRow['c'] ?? 0);

    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            p.tag_id_card,
            p.foto,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur,
            p.kk_level,
            p.nama_ayah,
            p.nama_ibu,
            p.alamat_sekarang AS alamat,
            p.status_kawin,
            p.created_at AS tgl_terdaftar,
            w.dusun, w.rt, w.rw,
            pk.nama AS pekerjaan,
            pd.nama AS pendidikan,
            rtm.no_kk AS no_rtm
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        LEFT JOIN tweb_penduduk_pekerjaan pk ON pk.id = p.pekerjaan_id
        LEFT JOIN tweb_penduduk_pendidikan_kk pd ON pd.id = p.pendidikan_kk_id
        LEFT JOIN tweb_rtm rtm ON rtm.id = CAST(NULLIF(p.id_rtm,'') AS UNSIGNED)
        WHERE $where
        ORDER BY p.nama ASC
        LIMIT $perPage OFFSET $offset";
    $rows = opensid_fetchAll($sql);

    foreach ($rows as $r) {
        $umur = (int)($r['umur'] ?? 0);
        $jk   = mapSex($r['sex']);
        $data[] = [
            'id'            => $r['id_penduduk'],
            'nik'           => $r['nik'] ?? '',
            'tag_id_card'   => $r['tag_id_card'] ?? '',
            'foto'          => $r['foto'] ?? '',
            'no_kk'         => $r['no_kk'] ?? '',
            'nama'          => $r['nama'] ?? '',
            'jenis_kelamin' => $jk,
            'umur'          => $umur,
            'dusun'         => $r['dusun'] ?? '-',
            'rt'            => $r['rt'] ?? '-',
            'rw'            => $r['rw'] ?? '-',
            'alamat'        => $r['alamat'] ?? '',
            'nama_ayah'     => $r['nama_ayah'] ?? '',
            'nama_ibu'      => $r['nama_ibu'] ?? '',
            'pendidikan'    => $r['pendidikan'] ?? 'TIDAK / BELUM SEKOLAH',
            'pekerjaan'     => $r['pekerjaan'] ?? 'BELUM/TIDAK BEKERJA',
            'status_kawin'  => mapKawin($r['status_kawin'] ?? 0),
            'no_rtm'        => $r['no_rtm'] ?? '',
            'is_balita'     => ($umur >= 0 && $umur <= 5),
            'is_bumil'      => ($jk === 'P' && $umur >= 10 && $umur <= 55),
            'is_lansia'     => ($umur >= 60),
        ];
    }
}
$totalPages = max(1, (int)ceil($total / max(1, $perPage)));

function buildQs(array $params): string {
    return http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
}
$baseQs = [
    'q' => $search,
    'jk' => $jkFilter,
    'dusun' => $dusunFilter,
    'status' => $statusFilter,
    'per_page' => $perPage,
];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-users me-2 text-primary"></i>Data Penduduk</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li>
          <li class="breadcrumb-item active">Data Penduduk</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

<?php if (!$opensidOk): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-1"></i>
    Koneksi OpenSID gagal. Periksa <code>config/database.php</code>.
  </div>
<?php endif; ?>

<div class="card card-outline card-primary shadow-sm">
  <div class="card-header bg-white border-bottom py-2">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <span class="badge bg-success"><i class="fas fa-database me-1"></i> OpenSID (read-only)</span>
      <span class="text-muted small"><?= number_format($total) ?> jiwa</span>
    </div>
  </div>

  <!-- Filter ringkas (mirip OpenSID mobile) -->
  <div class="card-header bg-light py-2">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="1" <?= $statusFilter==='1'?'selected':'' ?>>Hidup</option>
          <option value="2" <?= $statusFilter==='2'?'selected':'' ?>>Mati</option>
          <option value="3" <?= $statusFilter==='3'?'selected':'' ?>>Pindah</option>
          <option value="4" <?= $statusFilter==='4'?'selected':'' ?>>Hilang</option>
          <option value="all" <?= $statusFilter==='all'?'selected':'' ?>>Semua Status</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="jk" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Pilih Jenis Kelamin</option>
          <option value="L" <?= $jkFilter==='L'?'selected':'' ?>>Laki-laki</option>
          <option value="P" <?= $jkFilter==='P'?'selected':'' ?>>Perempuan</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="dusun" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">Pilih Dusun</option>
          <?php foreach ($dusunList as $d): ?>
          <option value="<?= htmlspecialchars($d['dusun']) ?>" <?= $dusunFilter===$d['dusun']?'selected':'' ?>><?= htmlspecialchars($d['dusun']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
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
      <div class="col-9 col-md-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text">Cari:</span>
          <input type="text" name="q" class="form-control" placeholder="kata kunci pencarian" value="<?= htmlspecialchars($search) ?>">
        </div>
      </div>
      <div class="col-3 col-md-1">
        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button>
      </div>
    </form>
  </div>

  <div class="card-body p-0">
    <!-- Satu tabel untuk semua perangkat — geser horizontal di HP -->
    <div class="table-responsive opensid-scroll" style="-webkit-overflow-scrolling: touch;">
      <table class="table table-hover table-sm table-striped mb-0 align-middle opensid-table" style="min-width:1100px; font-size:0.82rem;">
        <thead class="table-primary text-nowrap sticky-top">
          <tr>
            <th class="text-center" style="width:36px">NO</th>
            <th style="width:110px">AKSI</th>
            <th class="text-center" style="width:52px">FOTO</th>
            <th>NIK</th>
            <th>TAG ID</th>
            <th>NAMA</th>
            <th>NO. KK</th>
            <th>NAMA AYAH</th>
            <th>NAMA IBU</th>
            <th>L/P</th>
            <th>ALAMAT</th>
            <th>DUSUN</th>
            <th>RW</th>
            <th>RT</th>
            <th>PENDIDIKAN KK</th>
            <th class="text-center">UMUR</th>
            <th>PEKERJAAN</th>
            <th>KAWIN</th>
            <th>NO. RTM</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($data)): ?>
          <tr>
            <td colspan="19" class="text-center text-muted py-5">
              Tidak ada data penduduk yang cocok dengan filter.
            </td>
          </tr>
          <?php else: foreach ($data as $i => $p):
            $id   = (int)$p['id'];
            $nik  = urlencode($p['nik']);
            $nokk = urlencode($p['no_kk']);
            $isL  = $p['jenis_kelamin'] === 'L';
          ?>
          <tr>
            <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-info text-white py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size:0.72rem; white-space:nowrap;">
                  <i class="fas fa-arrow-circle-down"></i> Pilih Aksi
                </button>
                <ul class="dropdown-menu shadow" style="font-size:0.85rem; min-width:230px;">
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/penduduk/profil.php?id=<?= $id ?>"><i class="fas fa-id-card me-2 text-primary"></i>Detail Biodata</a></li>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/penduduk/riwayat.php?id=<?= $id ?>&nik=<?= $nik ?>"><i class="fas fa-notes-medical me-2 text-success"></i>Riwayat Pemeriksaan</a></li>
                  <?php if (!empty($p['no_kk'])): ?>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/keluarga/detail.php?no_kk=<?= $nokk ?>"><i class="fas fa-home me-2 text-warning"></i>Anggota Keluarga</a></li>
                  <?php endif; ?>
                  <li><hr class="dropdown-divider"></li>
                  <?php if ($p['is_balita']): ?>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/balita/tambah.php?id_penduduk=<?= $id ?>&nik=<?= $nik ?>"><i class="fas fa-baby me-2 text-info"></i>Daftarkan Balita</a></li>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/pemeriksaan_balita/tambah.php?nik=<?= $nik ?>"><i class="fas fa-stethoscope me-2 text-info"></i>Pemeriksaan Balita</a></li>
                  <?php endif; ?>
                  <?php if ($p['is_bumil']): ?>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/ibu_hamil/tambah.php?id_penduduk=<?= $id ?>&nik=<?= $nik ?>"><i class="fas fa-female me-2" style="color:#9b59b6"></i>Daftarkan Ibu Hamil</a></li>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/pemeriksaan_ibu_hamil/tambah.php?nik=<?= $nik ?>"><i class="fas fa-heartbeat me-2" style="color:#9b59b6"></i>Pemeriksaan ANC</a></li>
                  <?php endif; ?>
                  <?php if ($p['is_lansia']): ?>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/lansia/tambah.php?id_penduduk=<?= $id ?>&nik=<?= $nik ?>"><i class="fas fa-user-injured me-2 text-secondary"></i>Daftarkan Lansia</a></li>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/pemeriksaan_lansia/tambah.php?nik=<?= $nik ?>"><i class="fas fa-stethoscope me-2 text-secondary"></i>Pemeriksaan Lansia</a></li>
                  <?php endif; ?>
                  <?php if ($p['jenis_kelamin']==='P' && $p['umur'] >= 15 && $p['umur'] <= 49): ?>
                  <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/kb/tambah.php?nik=<?= $nik ?>"><i class="fas fa-pills me-2 text-warning"></i>Pelayanan KB</a></li>
                  <?php endif; ?>
                </ul>
              </div>
            </td>
            <td class="text-center">
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                    style="width:38px;height:38px;font-size:0.9rem;<?= $isL ? 'background:#0d6efd' : 'background:#e91e63' ?>">
                <i class="fas fa-user"></i>
              </span>
            </td>
            <td><a href="<?= APP_URL ?>/modules/penduduk/profil.php?id=<?= $id ?>" class="text-primary text-decoration-none"><code style="font-size:0.78rem"><?= htmlspecialchars($p['nik'] ?: '-') ?></code></a></td>
            <td><small class="text-muted"><?= htmlspecialchars($p['tag_id_card'] ?: '-') ?></small></td>
            <td class="fw-semibold text-nowrap"><?= htmlspecialchars($p['nama']) ?></td>
            <td><small><?= htmlspecialchars($p['no_kk'] ?: '-') ?></small></td>
            <td><small><?= htmlspecialchars($p['nama_ayah'] ?: '-') ?></small></td>
            <td><small><?= htmlspecialchars($p['nama_ibu'] ?: '-') ?></small></td>
            <td>
              <?php if ($isL): ?>
                <span class="badge bg-info">L</span>
              <?php else: ?>
                <span class="badge" style="background:#e91e63">P</span>
              <?php endif; ?>
            </td>
            <td><small><?= htmlspecialchars($p['alamat'] ?: '-') ?></small></td>
            <td class="text-nowrap"><?= htmlspecialchars($p['dusun']) ?></td>
            <td class="text-center"><?= htmlspecialchars($p['rw']) ?></td>
            <td class="text-center"><?= htmlspecialchars($p['rt']) ?></td>
            <td><small><?= htmlspecialchars($p['pendidikan']) ?></small></td>
            <td class="text-center fw-semibold"><?= (int)$p['umur'] ?></td>
            <td><small><?= htmlspecialchars($p['pekerjaan']) ?></small></td>
            <td><small><?= htmlspecialchars($p['status_kawin']) ?></small></td>
            <td><small><?= htmlspecialchars($p['no_rtm'] ?: '-') ?></small></td>
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
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <?php
          $qs = buildQs($baseQs);
          $prev = max(1, $page - 1);
          $next = min($totalPages, $page + 1);
          ?>
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= $qs ?>&page=<?= $prev ?>">Sebelumnya</a>
          </li>
          <?php
          $start = max(1, $page - 1);
          $end   = min($totalPages, $page + 1);
          for ($pg = $start; $pg <= $end; $pg++): ?>
            <li class="page-item <?= $pg == $page ? 'active' : '' ?>">
              <a class="page-link" href="?<?= $qs ?>&page=<?= $pg ?>"><?= $pg ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= $qs ?>&page=<?= $next ?>">Selanjutnya</a>
          </li>
        </ul>
      </nav>
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
.opensid-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}
.opensid-scroll::-webkit-scrollbar { height: 6px; }
.opensid-scroll::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 3px; }
.opensid-table thead th {
  font-weight: 600;
  font-size: 0.72rem;
  white-space: nowrap;
  vertical-align: middle;
  padding: 0.5rem 0.45rem;
  background: #cfe2ff !important;
  position: sticky;
  top: 0;
  z-index: 2;
}
.opensid-table tbody td {
  padding: 0.4rem 0.45rem;
  vertical-align: middle;
}
.opensid-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
.opensid-table tbody tr:hover { background-color: rgba(13,110,253,0.06); }
.dropdown-menu { z-index: 2000; }
.dropdown-menu .dropdown-item { padding: 0.4rem 1rem; }
.dropdown-menu .dropdown-item i { width: 1.2rem; text-align: center; }
@media (max-width: 767.98px) {
  .content-header h1 { font-size: 1.2rem; }
  .opensid-table { font-size: 0.78rem !important; }
}
</style>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
