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
 * Laporan Kependudukan Bulanan (Lampiran A-9)
 * Gaya OpenSID – tidak mengubah kode lama
 */
$page_title = 'Laporan Kependudukan Bulanan';
require_once __DIR__ . '/../../includes/header.php';

$pengaturan = getPengaturan();
$nama_desa = $pengaturan['nama_desa'] ?? 'Balaan';
$kecamatan = $pengaturan['kecamatan'] ?? 'Nuhon';
$kabupaten = $pengaturan['kabupaten'] ?? 'Banggai';
$provinsi  = $pengaturan['provinsi'] ?? '';

// Filter tahun & bulan
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
$akhir_bulan_lalu = date('Y-m-d', strtotime($awal_bulan . ' -1 day'));

/**
 * Ambil statistik dari OpenSID jika tersedia, fallback ke tabel lokal
 */
function hitungPenduduk($tanggal_batas, $jenis = 'awal') {
    $result = [
        'wni_l' => 0, 'wni_p' => 0,
        'wna_l' => 0, 'wna_p' => 0,
        'kk_l'  => 0, 'kk_p'  => 0,
    ];

    if (opensid_available()) {
        // OpenSID: tweb_penduduk
        // status_dasar = 1 (hidup/aktif)
        // sex = 1 Laki, 2 Perempuan
        // warganegara = 1 WNI, 2 WNA (bisa berbeda versi)
        // kk_level = 1 Kepala Keluarga

        // Penduduk sampai tanggal batas (yang masih aktif)
        $sql = "
            SELECT 
                SUM(CASE WHEN sex = 1 AND (warganegara = 1 OR warganegara IS NULL OR warganegara = 0) THEN 1 ELSE 0 END) AS wni_l,
                SUM(CASE WHEN sex = 2 AND (warganegara = 1 OR warganegara IS NULL OR warganegara = 0) THEN 1 ELSE 0 END) AS wni_p,
                SUM(CASE WHEN sex = 1 AND warganegara = 2 THEN 1 ELSE 0 END) AS wna_l,
                SUM(CASE WHEN sex = 2 AND warganegara = 2 THEN 1 ELSE 0 END) AS wna_p
            FROM tweb_penduduk
            WHERE status_dasar = 1
              AND (tanggallahir IS NULL OR tanggallahir <= '{$tanggal_batas}')
        ";
        // Catatan: OpenSID sering tidak punya created_at yang akurat untuk "awal bulan".
        // Kita ambil total aktif saat ini sebagai approximation untuk akhir bulan,
        // dan hitung mutasi dari log/peristiwa jika tersedia.

        $r = opensid_fetchOne($sql);
        if ($r) {
            $result['wni_l'] = (int)($r['wni_l'] ?? 0);
            $result['wni_p'] = (int)($r['wni_p'] ?? 0);
            $result['wna_l'] = (int)($r['wna_l'] ?? 0);
            $result['wna_p'] = (int)($r['wna_p'] ?? 0);
        }

        // Jumlah KK
        $sql_kk = "
            SELECT 
                SUM(CASE WHEN p.sex = 1 THEN 1 ELSE 0 END) AS kk_l,
                SUM(CASE WHEN p.sex = 2 THEN 1 ELSE 0 END) AS kk_p
            FROM tweb_keluarga k
            INNER JOIN tweb_penduduk p ON p.id = k.nik_kepala AND p.status_dasar = 1
        ";
        $rkk = opensid_fetchOne($sql_kk);
        if ($rkk) {
            $result['kk_l'] = (int)($rkk['kk_l'] ?? 0);
            $result['kk_p'] = (int)($rkk['kk_p'] ?? 0);
        }
    } else {
        // Fallback ke tabel lokal SIMPOSYANDU
        try {
            $r = fetchOne("
                SELECT 
                    SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) AS wni_l,
                    SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) AS wni_p
                FROM penduduk WHERE status_aktif = 1 OR status_aktif IS NULL
            ");
            if ($r) {
                $result['wni_l'] = (int)($r['wni_l'] ?? 0);
                $result['wni_p'] = (int)($r['wni_p'] ?? 0);
            }
            $rkk = fetchOne("SELECT COUNT(*) AS c FROM keluarga");
            $result['kk_l'] = (int)($rkk['c'] ?? 0); // tidak bisa bedakan L/P KK di lokal sederhana
        } catch (Throwable $e) {}
    }

    return $result;
}

// Data akhir bulan (saat ini / total aktif)
$akhir = hitungPenduduk($akhir_bulan, 'akhir');

// Untuk mutasi (kelahiran, kematian, pendatang, pindah) – OpenSID biasanya punya log_penduduk atau peristiwa
$mutasi = [
    'lahir_l' => 0, 'lahir_p' => 0,
    'mati_l'  => 0, 'mati_p'  => 0,
    'datang_l'=> 0, 'datang_p'=> 0,
    'pindah_l'=> 0, 'pindah_p'=> 0,
    'hilang_l'=> 0, 'hilang_p'=> 0,
    'kk_baru' => 0, 'kk_pindah' => 0,
];

if (opensid_available()) {
    // Coba ambil dari log_penduduk / log_keluarga jika tabel ada
    // Versi OpenSID berbeda-beda; kita coba beberapa kemungkinan

    // Kelahiran bulan ini (tanggallahir dalam rentang)
    $r = opensid_fetchOne("
        SELECT 
            SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) AS l,
            SUM(CASE WHEN sex = 2 THEN 1 ELSE 0 END) AS p
        FROM tweb_penduduk
        WHERE status_dasar = 1
          AND tanggallahir BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'
    ");
    if ($r) {
        $mutasi['lahir_l'] = (int)($r['l'] ?? 0);
        $mutasi['lahir_p'] = (int)($r['p'] ?? 0);
    }

    // Kematian – status_dasar = 2 atau 3 (mati) + tgl_peristiwa
    // Banyak instalasi OpenSID menyimpan di log_penduduk
    $try_logs = [
        "SELECT SUM(CASE WHEN p.sex=1 THEN 1 ELSE 0 END) AS l, SUM(CASE WHEN p.sex=2 THEN 1 ELSE 0 END) AS p
         FROM log_penduduk lp
         JOIN tweb_penduduk p ON p.id = lp.id_pend
         WHERE lp.kode_peristiwa = 2
           AND lp.tgl_peristiwa BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'",
        "SELECT SUM(CASE WHEN sex=1 THEN 1 ELSE 0 END) AS l, SUM(CASE WHEN sex=2 THEN 1 ELSE 0 END) AS p
         FROM tweb_penduduk
         WHERE status_dasar IN (2,3)
           AND tgl_peristiwa BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'",
    ];
    foreach ($try_logs as $sql) {
        $r = @opensid_fetchOne($sql);
        if ($r && ((int)($r['l']??0) + (int)($r['p']??0)) > 0) {
            $mutasi['mati_l'] = (int)($r['l'] ?? 0);
            $mutasi['mati_p'] = (int)($r['p'] ?? 0);
            break;
        }
    }

    // Pendatang (kode_peristiwa = 3 atau status masuk)
    $try_datang = [
        "SELECT SUM(CASE WHEN p.sex=1 THEN 1 ELSE 0 END) AS l, SUM(CASE WHEN p.sex=2 THEN 1 ELSE 0 END) AS p
         FROM log_penduduk lp
         JOIN tweb_penduduk p ON p.id = lp.id_pend
         WHERE lp.kode_peristiwa = 3
           AND lp.tgl_peristiwa BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'",
    ];
    foreach ($try_datang as $sql) {
        $r = @opensid_fetchOne($sql);
        if ($r) {
            $mutasi['datang_l'] = (int)($r['l'] ?? 0);
            $mutasi['datang_p'] = (int)($r['p'] ?? 0);
            break;
        }
    }

    // Pindah (kode_peristiwa = 4 atau 5)
    $try_pindah = [
        "SELECT SUM(CASE WHEN p.sex=1 THEN 1 ELSE 0 END) AS l, SUM(CASE WHEN p.sex=2 THEN 1 ELSE 0 END) AS p
         FROM log_penduduk lp
         JOIN tweb_penduduk p ON p.id = lp.id_pend
         WHERE lp.kode_peristiwa IN (4,5)
           AND lp.tgl_peristiwa BETWEEN '{$awal_bulan}' AND '{$akhir_bulan}'",
    ];
    foreach ($try_pindah as $sql) {
        $r = @opensid_fetchOne($sql);
        if ($r) {
            $mutasi['pindah_l'] = (int)($r['l'] ?? 0);
            $mutasi['pindah_p'] = (int)($r['p'] ?? 0);
            break;
        }
    }
}

// Hitung awal bulan = akhir - lahir - datang + mati + pindah + hilang (approximation)
$awal = [
    'wni_l' => max(0, $akhir['wni_l'] - $mutasi['lahir_l'] - $mutasi['datang_l'] + $mutasi['mati_l'] + $mutasi['pindah_l'] + $mutasi['hilang_l']),
    'wni_p' => max(0, $akhir['wni_p'] - $mutasi['lahir_p'] - $mutasi['datang_p'] + $mutasi['mati_p'] + $mutasi['pindah_p'] + $mutasi['hilang_p']),
    'wna_l' => $akhir['wna_l'],
    'wna_p' => $akhir['wna_p'],
    'kk_l'  => max(0, $akhir['kk_l'] - $mutasi['kk_baru'] + $mutasi['kk_pindah']),
    'kk_p'  => $akhir['kk_p'],
];

function fmt($n) {
    return $n > 0 ? number_format($n) : '-';
}

function total_lp($l, $p) {
    $t = $l + $p;
    return $t > 0 ? number_format($t) : '-';
}
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-file-alt me-2 text-primary"></i>Laporan Kependudukan Bulanan</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li>
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
          <li class="breadcrumb-item active">Kependudukan Bulanan</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <!-- Filter & Tombol -->
  <div class="card border-0 shadow-sm mb-3">
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
          <a href="?tahun=<?= $tahun ?>&bulan=<?= $bulan ?>&export=1" class="btn btn-sm btn-outline-success">
            <i class="fas fa-download me-1"></i> Unduh
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Area Cetak -->
  <div class="card border-0 shadow-sm" id="area-cetak">
    <div class="card-body">

      <div class="text-center mb-3">
        <h5 class="mb-0 fw-bold text-uppercase">PEMERINTAH KABUPATEN/KOTA <?= strtoupper(htmlspecialchars($kabupaten)) ?></h5>
        <h6 class="mb-0 fw-bold text-uppercase">LAPORAN PERKEMBANGAN PENDUDUK (LAMPIRAN A - 9)</h6>
      </div>

      <table class="table table-sm table-borderless mb-3" style="max-width:500px">
        <tr>
          <td width="140">Desa/Kelurahan</td>
          <td width="10">:</td>
          <td><input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($nama_desa) ?>" readonly></td>
        </tr>
        <tr>
          <td>Kecamatan</td>
          <td>:</td>
          <td><input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($kecamatan) ?>" readonly></td>
        </tr>
        <tr>
          <td>Tahun</td>
          <td>:</td>
          <td>
            <div class="d-flex gap-2">
              <input type="text" class="form-control form-control-sm bg-light" style="width:80px" value="<?= $tahun ?>" readonly>
              <span class="align-self-center">Bulan</span>
              <input type="text" class="form-control form-control-sm bg-light" style="width:100px" value="<?= $nama_bulan[$bulan] ?>" readonly>
            </div>
          </td>
        </tr>
      </table>

      <div class="table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:0.85rem">
          <thead class="table-primary">
            <tr>
              <th rowspan="3" class="align-middle" style="width:40px">NO</th>
              <th rowspan="3" class="align-middle" style="min-width:180px">PERINCIAN</th>
              <th colspan="6">PENDUDUK</th>
              <th colspan="3">KELUARGA (KK)</th>
            </tr>
            <tr>
              <th colspan="2">WNI</th>
              <th colspan="2">WNA</th>
              <th colspan="2">JUMLAH</th>
              <th rowspan="2" class="align-middle">L</th>
              <th rowspan="2" class="align-middle">P</th>
              <th rowspan="2" class="align-middle">L+P</th>
            </tr>
            <tr>
              <th>L</th><th>P</th>
              <th>L</th><th>P</th>
              <th>L</th><th>P</th>
              <th>L+P</th>
            </tr>
            <tr class="table-secondary">
              <th>1</th><th>2</th>
              <th>3</th><th>4</th>
              <th>5</th><th>6</th>
              <th>7</th><th>8</th><th>9</th>
              <th>10</th><th>11</th><th>12</th>
            </tr>
          </thead>
          <tbody>
            <!-- 1. Awal bulan -->
            <tr>
              <td>1</td>
              <td class="text-start">Penduduk/Keluarga awal bulan ini</td>
              <td><?= fmt($awal['wni_l']) ?></td>
              <td><?= fmt($awal['wni_p']) ?></td>
              <td><?= fmt($awal['wna_l']) ?></td>
              <td><?= fmt($awal['wna_p']) ?></td>
              <td><?= fmt($awal['wni_l'] + $awal['wna_l']) ?></td>
              <td><?= fmt($awal['wni_p'] + $awal['wna_p']) ?></td>
              <td><?= total_lp($awal['wni_l']+$awal['wna_l'], $awal['wni_p']+$awal['wna_p']) ?></td>
              <td><?= fmt($awal['kk_l']) ?></td>
              <td><?= fmt($awal['kk_p']) ?></td>
              <td><?= total_lp($awal['kk_l'], $awal['kk_p']) ?></td>
            </tr>
            <!-- 2. Kelahiran / KK baru -->
            <tr>
              <td>2</td>
              <td class="text-start">Kelahiran/Keluarga baru bulan ini</td>
              <td><?= fmt($mutasi['lahir_l']) ?></td>
              <td><?= fmt($mutasi['lahir_p']) ?></td>
              <td>-</td><td>-</td>
              <td><?= fmt($mutasi['lahir_l']) ?></td>
              <td><?= fmt($mutasi['lahir_p']) ?></td>
              <td><?= total_lp($mutasi['lahir_l'], $mutasi['lahir_p']) ?></td>
              <td><?= fmt($mutasi['kk_baru']) ?></td>
              <td>-</td>
              <td><?= fmt($mutasi['kk_baru']) ?></td>
            </tr>
            <!-- 3. Kematian -->
            <tr>
              <td>3</td>
              <td class="text-start">Kematian bulan ini</td>
              <td><?= fmt($mutasi['mati_l']) ?></td>
              <td><?= fmt($mutasi['mati_p']) ?></td>
              <td>-</td><td>-</td>
              <td><?= fmt($mutasi['mati_l']) ?></td>
              <td><?= fmt($mutasi['mati_p']) ?></td>
              <td><?= total_lp($mutasi['mati_l'], $mutasi['mati_p']) ?></td>
              <td>-</td><td>-</td><td>-</td>
            </tr>
            <!-- 4. Pendatang -->
            <tr>
              <td>4</td>
              <td class="text-start">Pendatang bulan ini</td>
              <td><?= fmt($mutasi['datang_l']) ?></td>
              <td><?= fmt($mutasi['datang_p']) ?></td>
              <td>-</td><td>-</td>
              <td><?= fmt($mutasi['datang_l']) ?></td>
              <td><?= fmt($mutasi['datang_p']) ?></td>
              <td><?= total_lp($mutasi['datang_l'], $mutasi['datang_p']) ?></td>
              <td>-</td><td>-</td><td>-</td>
            </tr>
            <!-- 5. Pindah -->
            <tr>
              <td>5</td>
              <td class="text-start">Pindah/Keluarga pergi bulan ini</td>
              <td><?= fmt($mutasi['pindah_l']) ?></td>
              <td><?= fmt($mutasi['pindah_p']) ?></td>
              <td>-</td><td>-</td>
              <td><?= fmt($mutasi['pindah_l']) ?></td>
              <td><?= fmt($mutasi['pindah_p']) ?></td>
              <td><?= total_lp($mutasi['pindah_l'], $mutasi['pindah_p']) ?></td>
              <td><?= fmt($mutasi['kk_pindah']) ?></td>
              <td>-</td>
              <td><?= fmt($mutasi['kk_pindah']) ?></td>
            </tr>
            <!-- 6. Hilang -->
            <tr>
              <td>6</td>
              <td class="text-start">Penduduk hilang bulan ini</td>
              <td><?= fmt($mutasi['hilang_l']) ?></td>
              <td><?= fmt($mutasi['hilang_p']) ?></td>
              <td>-</td><td>-</td>
              <td><?= fmt($mutasi['hilang_l']) ?></td>
              <td><?= fmt($mutasi['hilang_p']) ?></td>
              <td><?= total_lp($mutasi['hilang_l'], $mutasi['hilang_p']) ?></td>
              <td>-</td><td>-</td><td>-</td>
            </tr>
            <!-- 7. Akhir bulan -->
            <tr class="table-light fw-semibold">
              <td>7</td>
              <td class="text-start">Penduduk/Keluarga akhir bulan ini</td>
              <td><?= fmt($akhir['wni_l']) ?></td>
              <td><?= fmt($akhir['wni_p']) ?></td>
              <td><?= fmt($akhir['wna_l']) ?></td>
              <td><?= fmt($akhir['wna_p']) ?></td>
              <td><?= fmt($akhir['wni_l'] + $akhir['wna_l']) ?></td>
              <td><?= fmt($akhir['wni_p'] + $akhir['wna_p']) ?></td>
              <td><?= total_lp($akhir['wni_l']+$akhir['wna_l'], $akhir['wni_p']+$akhir['wna_p']) ?></td>
              <td><?= fmt($akhir['kk_l']) ?></td>
              <td><?= fmt($akhir['kk_p']) ?></td>
              <td><?= total_lp($akhir['kk_l'], $akhir['kk_p']) ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="row mt-4 d-none d-print-flex">
        <div class="col-6"></div>
        <div class="col-6 text-center">
          <p><?= htmlspecialchars($nama_desa) ?>, <?= date('d') ?> <?= $nama_bulan[(int)date('n')] ?> <?= date('Y') ?></p>
          <p class="mb-5">Kepala Desa <?= htmlspecialchars($nama_desa) ?></p>
          <p class="fw-bold text-decoration-underline">________________________</p>
        </div>
      </div>

      <?php if (!opensid_available()): ?>
      <div class="alert alert-warning mt-3 mb-0 small">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Koneksi OpenSID tidak tersedia. Data diambil dari database lokal SIMPOSYANDU (mungkin tidak lengkap untuk mutasi).
      </div>
      <?php endif; ?>

    </div>
  </div>

</div>
</section>

<style>
@media print {
  .main-sidebar, .main-header, .content-header, .card-body > form, .btn, .breadcrumb, .alert { display: none !important; }
  .content-wrapper, .content, .container-fluid, .card { margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }
  #area-cetak { display: block !important; }
  body { background: #fff !important; }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
