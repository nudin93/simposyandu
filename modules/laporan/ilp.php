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

/**
 * Laporan ILP Posyandu – format formal seperti OpenSID (Lampiran style)
 * Tidak mengubah kode lama, hanya mengganti tampilan laporan ILP
 */
$page_title = 'Laporan ILP Posyandu';
require_once __DIR__ . '/../../includes/header.php';

$pengaturan = getPengaturan();
$nama_desa   = $pengaturan['nama_desa'] ?? 'Balaan';
$kecamatan   = $pengaturan['kecamatan'] ?? 'Nuhon';
$kabupaten   = $pengaturan['kabupaten'] ?? 'Banggai';
$nama_posyandu = $pengaturan['nama_posyandu'] ?? 'Posyandu';

$tahun = (int)($_GET['tahun'] ?? date('Y'));
$bulan = (int)($_GET['bulan'] ?? date('n'));
if ($bulan < 1 || $bulan > 12) $bulan = (int)date('n');
if ($tahun < 2000 || $tahun > 2100) $tahun = (int)date('Y');

$nama_bulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

$awal_bulan  = sprintf('%04d-%02d-01', $tahun, $bulan);
$akhir_bulan = date('Y-m-t', strtotime($awal_bulan));

function fmt($n) {
    return ($n !== null && $n > 0) ? number_format((int)$n) : '-';
}
function total_lp($l, $p) {
    $t = (int)$l + (int)$p;
    return $t > 0 ? number_format($t) : '-';
}

/**
 * Hitung jumlah L/P dari tabel
 */
function countLP($table, $jk_col = 'jenis_kelamin', $extra = "AND (status_aktif=1 OR status_aktif IS NULL)") {
    $out = ['L'=>0,'P'=>0];
    try {
        $r = fetchOne("
            SELECT 
                SUM(CASE WHEN {$jk_col} IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) AS l,
                SUM(CASE WHEN {$jk_col} IN ('P','Perempuan','2') THEN 1 ELSE 0 END) AS p
            FROM {$table}
            WHERE 1=1 {$extra}
        ");
        if ($r) {
            $out['L'] = (int)($r['l'] ?? 0);
            $out['P'] = (int)($r['p'] ?? 0);
        }
    } catch (Throwable $e) {}
    return $out;
}

// ===== DATA SASARAN =====
$bayi   = countLP('bayi');
$balita = countLP('balita');
$bumil  = ['L'=>0,'P'=>0]; // Ibu hamil selalu P
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM ibu_hamil WHERE status_aktif=1 OR status_aktif IS NULL");
    $bumil['P'] = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
$remaja = countLP('remaja');
$up     = countLP('usia_produktif');
$lansia = countLP('lansia');

// ===== DATA PELAYANAN BULAN INI =====
$periksa_bayi = ['L'=>0,'P'=>0];
try {
    $r = fetchOne("
        SELECT 
            SUM(CASE WHEN b.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) AS l,
            SUM(CASE WHEN b.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) AS p
        FROM pemeriksaan_bayi pb
        JOIN bayi b ON b.id = pb.bayi_id
        WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'
    ");
    if ($r) { $periksa_bayi['L']=(int)($r['l']??0); $periksa_bayi['P']=(int)($r['p']??0); }
} catch (Throwable $e) {}

$periksa_balita = ['L'=>0,'P'=>0];
try {
    $r = fetchOne("
        SELECT 
            SUM(CASE WHEN b.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) AS l,
            SUM(CASE WHEN b.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) AS p
        FROM pemeriksaan_balita pb
        JOIN balita b ON b.id = pb.balita_id
        WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'
    ");
    if ($r) { $periksa_balita['L']=(int)($r['l']??0); $periksa_balita['P']=(int)($r['p']??0); }
} catch (Throwable $e) {}

$periksa_bumil = 0;
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM pemeriksaan_ibu_hamil WHERE tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'");
    $periksa_bumil = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}

$periksa_remaja = ['L'=>0,'P'=>0];
try {
    $r = fetchOne("
        SELECT 
            SUM(CASE WHEN r.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) AS l,
            SUM(CASE WHEN r.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) AS p
        FROM pemeriksaan_remaja pr
        JOIN remaja r ON r.id = pr.remaja_id
        WHERE pr.tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'
    ");
    if ($r) { $periksa_remaja['L']=(int)($r['l']??0); $periksa_remaja['P']=(int)($r['p']??0); }
} catch (Throwable $e) {}

$periksa_lansia = ['L'=>0,'P'=>0];
try {
    $r = fetchOne("
        SELECT 
            SUM(CASE WHEN l.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) AS l,
            SUM(CASE WHEN l.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) AS p
        FROM pemeriksaan_lansia pl
        JOIN lansia l ON l.id = pl.lansia_id
        WHERE pl.tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'
    ");
    if ($r) { $periksa_lansia['L']=(int)($r['l']??0); $periksa_lansia['P']=(int)($r['p']??0); }
} catch (Throwable $e) {}

// Indikator lain
$imunisasi = 0; $vitamin = 0; $stunting = 0; $gizi_kurang = 0; $kunjungan = 0; $kegiatan = 0; $kb = 0;
try { $imunisasi = numRows("SELECT id FROM imunisasi WHERE tanggal BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'"); } catch(Throwable $e){}
try { $vitamin   = numRows("SELECT id FROM vitamin WHERE tanggal BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'"); } catch(Throwable $e){}
try { $stunting  = numRows("SELECT id FROM pemeriksaan_balita WHERE risiko_stunting='Stunting' AND tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'"); } catch(Throwable $e){}
try { $gizi_kurang = numRows("SELECT id FROM pemeriksaan_balita WHERE status_gizi IN ('Kurang','Buruk') AND tanggal_pemeriksaan BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'"); } catch(Throwable $e){}
try { $kunjungan = numRows("SELECT id FROM kunjungan_rumah WHERE tanggal BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'"); } catch(Throwable $e){}
try { $kegiatan  = numRows("SELECT id FROM kegiatan_posyandu WHERE tanggal BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'"); } catch(Throwable $e){}
try { $kb        = numRows("SELECT id FROM kb WHERE status='Aktif'"); } catch(Throwable $e){}
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-hospital me-2 text-primary"></i>Laporan ILP Posyandu</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li>
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
          <li class="breadcrumb-item active">ILP</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <!-- Filter -->
  <div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body py-2">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label mb-0 small">Tahun</label>
          <select name="tahun" class="form-select form-select-sm">
            <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
            <option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label mb-0 small">Bulan</label>
          <select name="bulan" class="form-select form-select-sm">
            <?php foreach ($nama_bulan as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $k==$bulan?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Tampilkan</button>
        </div>
        <div class="col-auto ms-auto">
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Cetak
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Area Cetak -->
  <div class="card border-0 shadow-sm" id="area-cetak">
    <div class="card-body">

      <div class="text-center mb-3">
        <h5 class="mb-0 fw-bold text-uppercase">PEMERINTAH KABUPATEN/KOTA <?= strtoupper(htmlspecialchars($kabupaten)) ?></h5>
        <h6 class="mb-0 fw-bold text-uppercase">LAPORAN INTEGRASI LAYANAN PRIMER (ILP) POSYANDU</h6>
        <p class="mb-0 small text-muted">Bulan <?= $nama_bulan[$bulan] ?> Tahun <?= $tahun ?></p>
      </div>

      <table class="table table-sm table-borderless mb-3" style="max-width:520px">
        <tr>
          <td width="150">Desa/Kelurahan</td>
          <td width="10">:</td>
          <td><input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($nama_desa) ?>" readonly></td>
        </tr>
        <tr>
          <td>Kecamatan</td>
          <td>:</td>
          <td><input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($kecamatan) ?>" readonly></td>
        </tr>
        <tr>
          <td>Nama Posyandu</td>
          <td>:</td>
          <td><input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($nama_posyandu) ?>" readonly></td>
        </tr>
        <tr>
          <td>Tahun / Bulan</td>
          <td>:</td>
          <td>
            <div class="d-flex gap-2">
              <input type="text" class="form-control form-control-sm bg-light" style="width:80px" value="<?= $tahun ?>" readonly>
              <input type="text" class="form-control form-control-sm bg-light" style="width:110px" value="<?= $nama_bulan[$bulan] ?>" readonly>
            </div>
          </td>
        </tr>
      </table>

      <!-- TABEL 1: SASARAN ILP -->
      <h6 class="fw-bold mt-3 mb-2">A. Sasaran Integrasi Layanan Primer</h6>
      <div class="table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:0.85rem">
          <thead class="table-primary">
            <tr>
              <th rowspan="2" class="align-middle" style="width:40px">NO</th>
              <th rowspan="2" class="align-middle" style="min-width:200px">KELOMPOK SASARAN</th>
              <th colspan="3">JUMLAH SASARAN</th>
              <th colspan="3">YANG DILAYANI BULAN INI</th>
              <th rowspan="2" class="align-middle">KETERANGAN</th>
            </tr>
            <tr>
              <th>L</th><th>P</th><th>L+P</th>
              <th>L</th><th>P</th><th>L+P</th>
            </tr>
            <tr class="table-secondary">
              <th>1</th><th>2</th>
              <th>3</th><th>4</th><th>5</th>
              <th>6</th><th>7</th><th>8</th>
              <th>9</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td class="text-start">Ibu Hamil</td>
              <td>-</td>
              <td><?= fmt($bumil['P']) ?></td>
              <td><?= fmt($bumil['P']) ?></td>
              <td>-</td>
              <td><?= fmt($periksa_bumil) ?></td>
              <td><?= fmt($periksa_bumil) ?></td>
              <td class="text-start small">Pemeriksaan ANC</td>
            </tr>
            <tr>
              <td>2</td>
              <td class="text-start">Bayi (0–11 bulan)</td>
              <td><?= fmt($bayi['L']) ?></td>
              <td><?= fmt($bayi['P']) ?></td>
              <td><?= total_lp($bayi['L'], $bayi['P']) ?></td>
              <td><?= fmt($periksa_bayi['L']) ?></td>
              <td><?= fmt($periksa_bayi['P']) ?></td>
              <td><?= total_lp($periksa_bayi['L'], $periksa_bayi['P']) ?></td>
              <td class="text-start small">Penimbangan & imunisasi</td>
            </tr>
            <tr>
              <td>3</td>
              <td class="text-start">Balita (12–59 bulan)</td>
              <td><?= fmt($balita['L']) ?></td>
              <td><?= fmt($balita['P']) ?></td>
              <td><?= total_lp($balita['L'], $balita['P']) ?></td>
              <td><?= fmt($periksa_balita['L']) ?></td>
              <td><?= fmt($periksa_balita['P']) ?></td>
              <td><?= total_lp($periksa_balita['L'], $periksa_balita['P']) ?></td>
              <td class="text-start small">Pertumbuhan & perkembangan</td>
            </tr>
            <tr>
              <td>4</td>
              <td class="text-start">Remaja</td>
              <td><?= fmt($remaja['L']) ?></td>
              <td><?= fmt($remaja['P']) ?></td>
              <td><?= total_lp($remaja['L'], $remaja['P']) ?></td>
              <td><?= fmt($periksa_remaja['L']) ?></td>
              <td><?= fmt($periksa_remaja['P']) ?></td>
              <td><?= total_lp($periksa_remaja['L'], $periksa_remaja['P']) ?></td>
              <td class="text-start small">Skrining kesehatan</td>
            </tr>
            <tr>
              <td>5</td>
              <td class="text-start">Usia Produktif</td>
              <td><?= fmt($up['L']) ?></td>
              <td><?= fmt($up['P']) ?></td>
              <td><?= total_lp($up['L'], $up['P']) ?></td>
              <td>-</td><td>-</td><td>-</td>
              <td class="text-start small">Skrining PTM</td>
            </tr>
            <tr>
              <td>6</td>
              <td class="text-start">Lansia</td>
              <td><?= fmt($lansia['L']) ?></td>
              <td><?= fmt($lansia['P']) ?></td>
              <td><?= total_lp($lansia['L'], $lansia['P']) ?></td>
              <td><?= fmt($periksa_lansia['L']) ?></td>
              <td><?= fmt($periksa_lansia['P']) ?></td>
              <td><?= total_lp($periksa_lansia['L'], $periksa_lansia['P']) ?></td>
              <td class="text-start small">Pemeriksaan kesehatan</td>
            </tr>
            <tr class="table-light fw-semibold">
              <td colspan="2" class="text-start">JUMLAH</td>
              <td><?= fmt($bayi['L']+$balita['L']+$remaja['L']+$up['L']+$lansia['L']) ?></td>
              <td><?= fmt($bumil['P']+$bayi['P']+$balita['P']+$remaja['P']+$up['P']+$lansia['P']) ?></td>
              <td><?= total_lp(
                    $bayi['L']+$balita['L']+$remaja['L']+$up['L']+$lansia['L'],
                    $bumil['P']+$bayi['P']+$balita['P']+$remaja['P']+$up['P']+$lansia['P']
                  ) ?></td>
              <td><?= fmt($periksa_bayi['L']+$periksa_balita['L']+$periksa_remaja['L']+$periksa_lansia['L']) ?></td>
              <td><?= fmt($periksa_bumil+$periksa_bayi['P']+$periksa_balita['P']+$periksa_remaja['P']+$periksa_lansia['P']) ?></td>
              <td><?= total_lp(
                    $periksa_bayi['L']+$periksa_balita['L']+$periksa_remaja['L']+$periksa_lansia['L'],
                    $periksa_bumil+$periksa_bayi['P']+$periksa_balita['P']+$periksa_remaja['P']+$periksa_lansia['P']
                  ) ?></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TABEL 2: INDIKATOR LAYANAN -->
      <h6 class="fw-bold mt-4 mb-2">B. Indikator Layanan Bulan Ini</h6>
      <div class="table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:0.85rem">
          <thead class="table-primary">
            <tr>
              <th style="width:40px">NO</th>
              <th class="text-start">INDIKATOR</th>
              <th style="width:100px">JUMLAH</th>
              <th class="text-start">KETERANGAN</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td class="text-start">Pemberian Imunisasi</td>
              <td><?= fmt($imunisasi) ?></td>
              <td class="text-start small">Semua dosis tercatat bulan ini</td>
            </tr>
            <tr>
              <td>2</td>
              <td class="text-start">Pemberian Vitamin A</td>
              <td><?= fmt($vitamin) ?></td>
              <td class="text-start small">Vitamin A bulan ini</td>
            </tr>
            <tr>
              <td>3</td>
              <td class="text-start">Kasus / Risiko Stunting</td>
              <td class="text-danger fw-semibold"><?= fmt($stunting) ?></td>
              <td class="text-start small">Dari pemeriksaan balita bulan ini</td>
            </tr>
            <tr>
              <td>4</td>
              <td class="text-start">Gizi Kurang / Buruk</td>
              <td><?= fmt($gizi_kurang) ?></td>
              <td class="text-start small">Status gizi pemeriksaan balita</td>
            </tr>
            <tr>
              <td>5</td>
              <td class="text-start">Kunjungan Rumah Kader</td>
              <td><?= fmt($kunjungan) ?></td>
              <td class="text-start small">Prioritas ILP</td>
            </tr>
            <tr>
              <td>6</td>
              <td class="text-start">Kegiatan Posyandu</td>
              <td><?= fmt($kegiatan) ?></td>
              <td class="text-start small">Kegiatan tercatat bulan ini</td>
            </tr>
            <tr>
              <td>7</td>
              <td class="text-start">Peserta KB Aktif</td>
              <td><?= fmt($kb) ?></td>
              <td class="text-start small">Status aktif</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="row mt-4 d-none d-print-flex">
        <div class="col-6"></div>
        <div class="col-6 text-center">
          <p><?= htmlspecialchars($nama_desa) ?>, <?= date('d') ?> <?= $nama_bulan[(int)date('n')] ?> <?= date('Y') ?></p>
          <p class="mb-5">Kepala Desa / Penanggung Jawab Posyandu</p>
          <p class="fw-bold text-decoration-underline">________________________</p>
        </div>
      </div>

      <p class="text-muted small mt-3 mb-0 no-print">
        Laporan ini mengikuti kerangka Integrasi Layanan Primer (ILP) Posyandu — Kementerian Kesehatan RI.
        Data sasaran dari database SIMPOSYANDU.
      </p>
    </div>
  </div>

  <div class="mt-2 no-print">
    <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
  </div>

</div>
</section>

<style>
@media print {
  .main-sidebar, .main-header, .content-header, .no-print, .breadcrumb, .btn { display: none !important; }
  .content-wrapper, .content, .container-fluid, .card { margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }
  #area-cetak { display: block !important; }
  body { background: #fff !important; font-size: 11pt; }
  table { font-size: 10pt !important; }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
