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
 * Laporan Posyandu – Filter jenis laporan + bulan/tahun
 * Data sinkron: hanya menampilkan data yang masih ada di database
 */
$page_title = 'Laporan Posyandu';
require_once __DIR__ . '/../../includes/header.php';

$pengaturan    = getPengaturan();
$nama_desa     = $pengaturan['nama_desa'] ?? 'Desa';
$kecamatan     = $pengaturan['kecamatan'] ?? '';
$kabupaten     = $pengaturan['kabupaten'] ?? '';
$nama_posyandu = $pengaturan['nama_posyandu'] ?? 'Posyandu';

$jenis = $_GET['jenis'] ?? 'semua';
$tahun = (int)($_GET['tahun'] ?? date('Y'));
$bulan = (int)($_GET['bulan'] ?? date('n'));
if ($bulan < 1 || $bulan > 12) $bulan = (int)date('n');
if ($tahun < 2000 || $tahun > 2100) $tahun = (int)date('Y');

$nama_bulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
$awal  = sprintf('%04d-%02d-01', $tahun, $bulan);
$akhir = date('Y-m-t', strtotime($awal));

$daftar_jenis = [
    'semua'           => 'Semua Laporan',
    'balita'          => 'Balita',
    'bayi'            => 'Bayi',
    'ibu_hamil'       => 'Ibu Hamil',
    'lansia'          => 'Lansia',
    'remaja'          => 'Remaja',
    'imunisasi'       => 'Imunisasi',
    'vitamin'         => 'Vitamin',
    'kb'              => 'KB',
    'kegiatan'        => 'Kegiatan Posyandu',
    'kunjungan_rumah' => 'Kunjungan Rumah',
    'kependudukan'    => 'Kependudukan',
];
if (!isset($daftar_jenis[$jenis])) $jenis = 'semua';

function fmt($n) { return ((int)$n > 0) ? number_format((int)$n) : '-'; }
function total_lp($l,$p) { $t=(int)$l+(int)$p; return $t>0?number_format($t):'-'; }

// ========== AMBIL DATA SESUAI JENIS ==========
$judul_laporan = $daftar_jenis[$jenis];
$total_l = 0; $total_p = 0; $total_all = 0;
$dilayani_l = 0; $dilayani_p = 0; $dilayani_all = 0;
$rows = []; // daftar nama
$extra_stats = []; // statistik tambahan

try {
switch ($jenis) {

case 'semua':
    // Ringkasan semua modul (hanya yang tabelnya ada & data masih aktif)
    $ringkasan = [];
    $modul_cek = [
        'balita' => ["SELECT COUNT(*) c FROM balita WHERE status_aktif=1 OR status_aktif IS NULL", "SELECT COUNT(*) c FROM pemeriksaan_balita pb INNER JOIN balita b ON b.id=pb.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL) WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'"],
        'bayi' => ["SELECT COUNT(*) c FROM bayi WHERE status_aktif=1 OR status_aktif IS NULL", "SELECT COUNT(*) c FROM pemeriksaan_bayi pb INNER JOIN bayi b ON b.id=pb.bayi_id AND (b.status_aktif=1 OR b.status_aktif IS NULL) WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'"],
        'ibu_hamil' => ["SELECT COUNT(*) c FROM ibu_hamil WHERE status_aktif=1 OR status_aktif IS NULL", "SELECT COUNT(*) c FROM pemeriksaan_ibu_hamil pi INNER JOIN ibu_hamil i ON i.id=pi.ibu_hamil_id AND (i.status_aktif=1 OR i.status_aktif IS NULL) WHERE pi.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'"],
        'lansia' => ["SELECT COUNT(*) c FROM lansia WHERE status_aktif=1 OR status_aktif IS NULL", "SELECT COUNT(*) c FROM pemeriksaan_lansia pl INNER JOIN lansia l ON l.id=pl.lansia_id AND (l.status_aktif=1 OR l.status_aktif IS NULL) WHERE pl.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'"],
        'remaja' => ["SELECT COUNT(*) c FROM remaja WHERE status_aktif=1 OR status_aktif IS NULL", "SELECT COUNT(*) c FROM pemeriksaan_remaja pr INNER JOIN remaja r ON r.id=pr.remaja_id AND (r.status_aktif=1 OR r.status_aktif IS NULL) WHERE pr.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'"],
        'imunisasi' => ["SELECT COUNT(*) c FROM imunisasi", "SELECT COUNT(*) c FROM imunisasi WHERE tanggal_imunisasi BETWEEN '{$awal}' AND '{$akhir}'"],
        'vitamin' => ["SELECT COUNT(*) c FROM vitamin", "SELECT COUNT(*) c FROM vitamin WHERE tanggal_pemberian BETWEEN '{$awal}' AND '{$akhir}'"],
        'kb' => ["SELECT COUNT(*) c FROM kb WHERE status='Aktif'", "SELECT COUNT(*) c FROM kb WHERE tanggal_pelayanan BETWEEN '{$awal}' AND '{$akhir}'"],
        'kegiatan' => ["SELECT COUNT(*) c FROM kegiatan_posyandu", "SELECT COUNT(*) c FROM kegiatan_posyandu WHERE tanggal_kegiatan BETWEEN '{$awal}' AND '{$akhir}'"],
        'kunjungan_rumah' => ["SELECT COUNT(*) c FROM kunjungan_rumah", "SELECT COUNT(*) c FROM kunjungan_rumah WHERE tanggal_kunjungan BETWEEN '{$awal}' AND '{$akhir}'"],
    ];
    foreach ($modul_cek as $key => $sqls) {
        $sasaran = 0; $bulan_ini = 0;
        // Hanya hitung aktivitas di bulan/tahun terpilih (sqls[1]), bukan total keseluruhan
        try { $r = fetchOne($sqls[1]); $bulan_ini = (int)($r['c']??0); } catch (Throwable $e) {}
        $sasaran = $bulan_ini; // sasaran = yang ada di periode ini saja
        if ($bulan_ini > 0) {
            $ringkasan[] = [
                'key' => $key,
                'nama' => $daftar_jenis[$key] ?? $key,
                'sasaran' => $sasaran,
                'bulan_ini' => $bulan_ini,
            ];
        }
    }
    // kependudukan
    $pend = 0; $kk = 0;
    if (opensid_available()) {
        try {
            $r = opensid_fetchOne("SELECT COUNT(*) c FROM tweb_penduduk WHERE status_dasar=1"); $pend=(int)($r['c']??0);
            $r = opensid_fetchOne("SELECT COUNT(*) c FROM tweb_keluarga k INNER JOIN tweb_penduduk p ON p.id=k.nik_kepala AND p.status_dasar=1"); $kk=(int)($r['c']??0);
        } catch(Throwable $e){}
    }
    if ($pend > 0 || $kk > 0) {
        $ringkasan[] = ['key'=>'kependudukan','nama'=>'Kependudukan','sasaran'=>$pend,'bulan_ini'=>$kk];
    }
    $rows = $ringkasan;
    $total_all = count($ringkasan);
    break;

case 'balita':
    // Hanya data pemeriksaan pada bulan/tahun yang dipilih
    $r = fetchOne("SELECT 
        COUNT(DISTINCT b.id) AS c,
        COUNT(*) AS jml_periksa,
        SUM(CASE WHEN b.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) AS l,
        SUM(CASE WHEN b.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) AS p
        FROM pemeriksaan_balita pb
        INNER JOIN balita b ON b.id = pb.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all = (int)($r['c']??0);
    $dilayani_all = (int)($r['jml_periksa']??0);
    $total_l = $dilayani_l = (int)($r['l']??0);
    $total_p = $dilayani_p = (int)($r['p']??0);

    $rows = fetchAll("SELECT b.nama_lengkap AS nama, b.jenis_kelamin, b.tanggal_lahir, b.nama_ibu,
        pb.tanggal_pemeriksaan AS tanggal, pb.berat_badan, pb.tinggi_badan, pb.status_gizi, pb.risiko_stunting,
        k.nama AS nama_petugas
        FROM pemeriksaan_balita pb
        INNER JOIN balita b ON b.id = pb.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        LEFT JOIN kader k ON k.id = pb.petugas_id
        WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY pb.tanggal_pemeriksaan DESC, b.nama_lengkap ASC") ?: [];

    $extra_stats['Stunting'] = 0; $extra_stats['Gizi Kurang/Buruk'] = 0;
    try {
        $r = fetchOne("SELECT COUNT(*) c FROM pemeriksaan_balita pb INNER JOIN balita b ON b.id=pb.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL) WHERE pb.risiko_stunting='Stunting' AND pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
        $extra_stats['Stunting'] = (int)($r['c']??0);
        $r = fetchOne("SELECT COUNT(*) c FROM pemeriksaan_balita pb INNER JOIN balita b ON b.id=pb.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL) WHERE pb.status_gizi IN ('Kurang','Buruk') AND pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
        $extra_stats['Gizi Kurang/Buruk'] = (int)($r['c']??0);
    } catch(Throwable $e){}
    break;

case 'bayi':
    $r = fetchOne("SELECT COUNT(DISTINCT b.id) c, COUNT(*) jml_periksa,
        SUM(CASE WHEN b.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) l,
        SUM(CASE WHEN b.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) p
        FROM pemeriksaan_bayi pb
        INNER JOIN bayi b ON b.id=pb.bayi_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=(int)($r['jml_periksa']??0);
    $total_l=$dilayani_l=(int)($r['l']??0); $total_p=$dilayani_p=(int)($r['p']??0);

    $rows = fetchAll("SELECT b.nama_lengkap AS nama, b.jenis_kelamin, b.tanggal_lahir, b.nama_ibu,
        pb.tanggal_pemeriksaan AS tanggal, pb.berat_badan, pb.panjang_badan AS tinggi_badan,
        pb.keluhan AS catatan, pb.status_gizi
        FROM pemeriksaan_bayi pb
        INNER JOIN bayi b ON b.id=pb.bayi_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        WHERE pb.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY pb.tanggal_pemeriksaan DESC, b.nama_lengkap ASC") ?: [];
    break;

case 'ibu_hamil':
    $r = fetchOne("SELECT COUNT(DISTINCT i.id) c, COUNT(*) jml_periksa FROM pemeriksaan_ibu_hamil pih
        INNER JOIN ibu_hamil i ON i.id=pih.ibu_hamil_id AND (i.status_aktif=1 OR i.status_aktif IS NULL)
        WHERE pih.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=(int)($r['jml_periksa']??0);
    $total_p=$dilayani_p=$total_all; $total_l=$dilayani_l=0;

    $rows = fetchAll("SELECT i.nama, i.tanggal_lahir, i.hpht,
        pih.tanggal_pemeriksaan AS tanggal, pih.berat_badan, pih.tekanan_darah,
        COALESCE(
          NULLIF(pih.usia_kandungan, 0),
          NULLIF(i.usia_kehamilan, 0),
          TIMESTAMPDIFF(WEEK, i.hpht, pih.tanggal_pemeriksaan)
        ) AS usia_kehamilan,
        pih.usia_kandungan, i.usia_kehamilan AS usia_kehamilan_master,
        pih.catatan_bidan AS catatan, pih.keluhan,
        k.nama AS nama_petugas
        FROM pemeriksaan_ibu_hamil pih
        INNER JOIN ibu_hamil i ON i.id=pih.ibu_hamil_id AND (i.status_aktif=1 OR i.status_aktif IS NULL)
        LEFT JOIN kader k ON k.id = pih.petugas_id
        WHERE pih.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY pih.tanggal_pemeriksaan DESC, i.nama ASC") ?: [];
    break;

case 'lansia':
    $r = fetchOne("SELECT COUNT(DISTINCT l.id) c, COUNT(*) jml_periksa,
        SUM(CASE WHEN l.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) l,
        SUM(CASE WHEN l.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) p
        FROM pemeriksaan_lansia pl
        INNER JOIN lansia l ON l.id=pl.lansia_id AND (l.status_aktif=1 OR l.status_aktif IS NULL)
        WHERE pl.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=(int)($r['jml_periksa']??0);
    $total_l=$dilayani_l=(int)($r['l']??0); $total_p=$dilayani_p=(int)($r['p']??0);

    $rows = fetchAll("SELECT l.nama, l.jenis_kelamin, l.tanggal_lahir,
        pl.tanggal_pemeriksaan AS tanggal, pl.berat_badan, pl.tekanan_darah,
        pl.catatan_petugas AS catatan, pl.keluhan, k.nama AS nama_petugas
        FROM pemeriksaan_lansia pl
        INNER JOIN lansia l ON l.id=pl.lansia_id AND (l.status_aktif=1 OR l.status_aktif IS NULL)
        LEFT JOIN kader k ON k.id = pl.petugas_id
        WHERE pl.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY pl.tanggal_pemeriksaan DESC, l.nama ASC") ?: [];
    break;

case 'remaja':
    $r = fetchOne("SELECT COUNT(DISTINCT r.id) c, COUNT(*) jml_periksa,
        SUM(CASE WHEN r.jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) l,
        SUM(CASE WHEN r.jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) p
        FROM pemeriksaan_remaja pr
        INNER JOIN remaja r ON r.id=pr.remaja_id AND (r.status_aktif=1 OR r.status_aktif IS NULL)
        WHERE pr.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=(int)($r['jml_periksa']??0);
    $total_l=$dilayani_l=(int)($r['l']??0); $total_p=$dilayani_p=(int)($r['p']??0);

    $rows = fetchAll("SELECT r.nama, r.jenis_kelamin, r.tanggal_lahir,
        pr.tanggal_pemeriksaan AS tanggal, pr.berat_badan, pr.tinggi_badan, pr.catatan,
        pr.keluhan, k.nama AS nama_petugas
        FROM pemeriksaan_remaja pr
        INNER JOIN remaja r ON r.id=pr.remaja_id AND (r.status_aktif=1 OR r.status_aktif IS NULL)
        LEFT JOIN kader k ON k.id = pr.petugas_id
        WHERE pr.tanggal_pemeriksaan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY pr.tanggal_pemeriksaan DESC, r.nama ASC") ?: [];
    break;

case 'imunisasi':
    $r = fetchOne("SELECT COUNT(*) c FROM imunisasi i
        INNER JOIN balita b ON b.id=i.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        WHERE i.tanggal_imunisasi BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=$total_all;

    $rows = fetchAll("SELECT i.tanggal_imunisasi AS tanggal, i.jenis_imunisasi, i.dosis, i.keterangan,
        b.nama_lengkap AS nama, b.jenis_kelamin, k.nama AS nama_petugas
        FROM imunisasi i
        INNER JOIN balita b ON b.id=i.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        LEFT JOIN kader k ON k.id = i.petugas_id
        WHERE i.tanggal_imunisasi BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY i.tanggal_imunisasi DESC, b.nama_lengkap ASC") ?: [];
    break;

case 'vitamin':
    $r = fetchOne("SELECT COUNT(*) c FROM vitamin WHERE tanggal_pemberian BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=$total_all;
    $rows = fetchAll("SELECT v.tanggal_pemberian AS tanggal, v.jenis_vitamin, v.dosis, v.catatan,
        COALESCE(b.nama_lengkap, i.nama, l.nama, '-') AS nama,
        k.nama AS nama_petugas
        FROM vitamin v
        LEFT JOIN balita b ON b.id=v.balita_id AND (b.status_aktif=1 OR b.status_aktif IS NULL)
        LEFT JOIN ibu_hamil i ON i.id=v.ibu_hamil_id AND (i.status_aktif=1 OR i.status_aktif IS NULL)
        LEFT JOIN lansia l ON l.id=v.lansia_id AND (l.status_aktif=1 OR l.status_aktif IS NULL)
        LEFT JOIN kader k ON k.id=v.petugas_id
        WHERE v.tanggal_pemberian BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY v.tanggal_pemberian DESC") ?: [];
    break;

case 'kb':
    $r = fetchOne("SELECT COUNT(*) c FROM kb WHERE tanggal_pelayanan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=$total_all;
    $rows = fetchAll("SELECT kb.nama, kb.jenis_kelamin, kb.jenis_kontrasepsi, kb.tanggal_pelayanan AS tanggal,
        kb.status, kb.keterangan, k.nama AS nama_petugas
        FROM kb
        LEFT JOIN kader k ON k.id = kb.petugas_id
        WHERE kb.tanggal_pelayanan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY kb.tanggal_pelayanan DESC, kb.nama ASC") ?: [];
    break;

case 'kegiatan':
    $r = fetchOne("SELECT COUNT(*) c FROM kegiatan_posyandu WHERE tanggal_kegiatan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=$total_all;
    $rows = fetchAll("SELECT kp.tanggal_kegiatan AS tanggal, kp.nama_posyandu, kp.lokasi, kp.kader_bertugas,
        kp.jumlah_sasaran, kp.jumlah_hadir, kp.keterangan, kp.status, k.nama AS nama_petugas
        FROM kegiatan_posyandu kp
        LEFT JOIN kader k ON k.id = kp.petugas_id
        WHERE kp.tanggal_kegiatan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY kp.tanggal_kegiatan DESC") ?: [];
    break;

case 'kunjungan_rumah':
    $r = fetchOne("SELECT COUNT(*) c FROM kunjungan_rumah WHERE tanggal_kunjungan BETWEEN '{$awal}' AND '{$akhir}'");
    $total_all=(int)($r['c']??0); $dilayani_all=$total_all;
    $rows = fetchAll("SELECT kr.tanggal_kunjungan AS tanggal, kr.nama_keluarga AS nama, kr.alamat, kr.prioritas,
        kr.masalah_ditemukan, kr.tindakan, kr.catatan, k.nama AS nama_petugas
        FROM kunjungan_rumah kr
        LEFT JOIN kader k ON k.id = kr.kader_id
        WHERE kr.tanggal_kunjungan BETWEEN '{$awal}' AND '{$akhir}'
        ORDER BY kr.tanggal_kunjungan DESC, kr.nama_keluarga ASC") ?: [];
    break;

case 'kependudukan':
    if (opensid_available()) {
        $r = opensid_fetchOne("SELECT COUNT(*) c,
            SUM(CASE WHEN sex=1 THEN 1 ELSE 0 END) l,
            SUM(CASE WHEN sex=2 THEN 1 ELSE 0 END) p
            FROM tweb_penduduk WHERE status_dasar=1");
        $total_all=(int)($r['c']??0); $total_l=(int)($r['l']??0); $total_p=(int)($r['p']??0);
        $r = opensid_fetchOne("SELECT COUNT(*) c FROM tweb_keluarga k INNER JOIN tweb_penduduk p ON p.id=k.nik_kepala AND p.status_dasar=1");
        $extra_stats['Jumlah KK'] = (int)($r['c']??0);
    } else {
        try {
            $r = fetchOne("SELECT COUNT(*) c FROM penduduk"); $total_all=(int)($r['c']??0);
            $r = fetchOne("SELECT COUNT(*) c FROM keluarga"); $extra_stats['Jumlah KK']=(int)($r['c']??0);
        } catch(Throwable $e){}
    }
    $rows = [];
    break;
}
} catch (Throwable $e) {
    // tabel tidak ada / error → data kosong, tidak crash
    $rows = [];
}
?>

<section class="content-header no-print">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-file-alt me-2 text-primary"></i>Laporan Posyandu</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li>
          <li class="breadcrumb-item active">Laporan</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <!-- FILTER -->
  <div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body py-3">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label mb-0 small fw-semibold">Jenis Laporan</label>
          <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($daftar_jenis as $k => $v): ?>
            <option value="<?= $k ?>" <?= $jenis===$k?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label mb-0 small fw-semibold">Tahun</label>
          <select name="tahun" class="form-select form-select-sm">
            <?php for ($y=date('Y'); $y>=date('Y')-5; $y--): ?>
            <option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label mb-0 small fw-semibold">Bulan</label>
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
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCetak" onclick="cetakLaporan()">
            <i class="fas fa-print me-1"></i> Cetak
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- HASIL LAPORAN -->
  <div class="card border-0 shadow-sm" id="area-cetak">
    <div class="card-body">

      <div class="text-center mb-3">
        <h5 class="mb-0 fw-bold text-uppercase">PEMERINTAH KABUPATEN/KOTA <?= strtoupper(htmlspecialchars($kabupaten)) ?></h5>
        <h6 class="mb-0 fw-bold text-uppercase">LAPORAN <?= strtoupper(htmlspecialchars($judul_laporan)) ?> POSYANDU</h6>
        <p class="mb-0 small"><?= htmlspecialchars($nama_posyandu) ?> · <?= htmlspecialchars($nama_desa) ?><?= $kecamatan ? ', Kec. '.htmlspecialchars($kecamatan) : '' ?></p>
        <p class="mb-0 small text-muted">Periode: <?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
      </div>

      <!-- Ringkasan angka -->
      <div class="row text-center mb-3 g-2">
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 bg-light">
            <div class="fs-4 fw-bold text-primary"><?= fmt($total_all) ?></div>
            <small class="text-muted">Orang di Periode Ini</small>
          </div>
        </div>
        <?php if ($jenis !== 'kependudukan' && $jenis !== 'kb' && $jenis !== 'kegiatan' && $jenis !== 'kunjungan_rumah' && $jenis !== 'imunisasi' && $jenis !== 'vitamin'): ?>
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 bg-light">
            <div class="fs-4 fw-bold"><?= fmt($total_l) ?> / <?= fmt($total_p) ?></div>
            <small class="text-muted">L / P</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 bg-light">
            <div class="fs-4 fw-bold text-success"><?= fmt($dilayani_all) ?></div>
            <small class="text-muted">Jumlah Pemeriksaan</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 bg-light">
            <div class="fs-4 fw-bold"><?= fmt($dilayani_l) ?> / <?= fmt($dilayani_p) ?></div>
            <small class="text-muted">Dilayani L / P</small>
          </div>
        </div>
        <?php else: ?>
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 bg-light">
            <div class="fs-4 fw-bold text-success"><?= fmt($dilayani_all) ?></div>
            <small class="text-muted">Jumlah Bulan Ini</small>
          </div>
        </div>
        <?php endif; ?>
        <?php foreach ($extra_stats as $label => $val): ?>
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 bg-light">
            <div class="fs-4 fw-bold text-danger"><?= fmt($val) ?></div>
            <small class="text-muted"><?= htmlspecialchars($label) ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Tabel formal L/P (untuk sasaran) -->
      <?php if (in_array($jenis, ['balita','bayi','ibu_hamil','lansia','remaja'])): ?>
      <div class="table-responsive mb-3">
        <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:0.85rem">
          <thead class="table-primary">
            <tr>
              <th rowspan="2" class="align-middle">NO</th>
              <th rowspan="2" class="align-middle text-start">URAIAN</th>
              <th colspan="3">JUMLAH</th>
            </tr>
            <tr><th>L</th><th>P</th><th>L+P</th></tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td class="text-start">Jumlah orang yang diperiksa (<?= $nama_bulan[$bulan] ?> <?= $tahun ?>)</td>
              <td><?= fmt($total_l) ?></td>
              <td><?= fmt($total_p) ?></td>
              <td><?= total_lp($total_l, $total_p) ?></td>
            </tr>
            <tr>
              <td>2</td>
              <td class="text-start">Jumlah kunjungan / pemeriksaan (<?= $nama_bulan[$bulan] ?> <?= $tahun ?>)</td>
              <td><?= fmt($dilayani_l) ?></td>
              <td><?= fmt($dilayani_p) ?></td>
              <td><?= total_lp($dilayani_l, $dilayani_p) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Daftar nama yang diperiksa -->
      <h6 class="fw-bold mb-2">
        <?php if ($jenis === 'semua'): ?>
          Ringkasan Semua Laporan — <?= $nama_bulan[$bulan] ?> <?= $tahun ?>
        <?php elseif (in_array($jenis, ['balita','bayi','ibu_hamil','lansia','remaja'])): ?>
          Daftar Nama yang Melakukan Pemeriksaan — <?= $nama_bulan[$bulan] ?> <?= $tahun ?>
        <?php else: ?>
          Daftar Data — <?= $nama_bulan[$bulan] ?> <?= $tahun ?>
        <?php endif; ?>
      </h6>

      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size:0.82rem">
          <thead class="table-primary">
            <tr>
              <th style="width:40px">No</th>
              <?php if ($jenis === 'semua'): ?>
                <th class="text-start">Jenis Laporan</th><th>Total Sasaran</th><th>Bulan Ini</th><th class="no-print">Aksi</th>
              <?php elseif ($jenis === 'balita'): ?>
                <th>Nama Balita</th><th>JK</th><th>Tgl Lahir</th><th>Nama Ibu</th><th>Tgl Periksa</th><th>BB</th><th>TB</th><th>Status Gizi</th><th>Stunting</th><th>Petugas</th>
              <?php elseif ($jenis === 'bayi'): ?>
                <th>Nama Bayi</th><th>JK</th><th>Tgl Lahir</th><th>Nama Ibu</th><th>Tgl Periksa</th><th>BB</th><th>TB</th><th>Catatan</th>
              <?php elseif ($jenis === 'ibu_hamil'): ?>
                <th>Nama Ibu</th><th>Usia Kehamilan</th><th>HPHT</th><th>Tgl Periksa</th><th>BB</th><th>Tekanan Darah</th><th>Catatan / Petugas</th>
              <?php elseif ($jenis === 'lansia'): ?>
                <th>Nama</th><th>JK</th><th>Tgl Lahir</th><th>Tgl Periksa</th><th>BB</th><th>Tekanan Darah</th><th>Catatan</th>
              <?php elseif ($jenis === 'remaja'): ?>
                <th>Nama</th><th>JK</th><th>Tgl Lahir</th><th>Tgl Periksa</th><th>BB</th><th>TB</th><th>Catatan</th>
              <?php elseif ($jenis === 'imunisasi'): ?>
                <th>Tanggal</th><th>Nama</th><th>JK</th><th>Jenis Imunisasi</th><th>Dosis</th><th>Petugas</th>
              <?php elseif ($jenis === 'vitamin'): ?>
                <th>Tanggal</th><th>Jenis Vitamin</th><th>Nama Penerima</th><th>Petugas / Catatan</th>
              <?php elseif ($jenis === 'kb'): ?>
                <th>Nama</th><th>Jenis Kontrasepsi</th><th>Status</th><th>Petugas</th>
              <?php elseif ($jenis === 'kegiatan'): ?>
                <th>Tanggal</th><th>Nama Posyandu / Kegiatan</th><th>Lokasi</th><th>Kader / Petugas</th>
              <?php elseif ($jenis === 'kunjungan_rumah'): ?>
                <th>Tanggal</th><th>Nama Keluarga</th><th>Prioritas / Petugas</th>
              <?php elseif ($jenis === 'kependudukan'): ?>
                <th colspan="4" class="text-center">Data diambil dari OpenSID / database lokal (ringkasan di atas)</th>
              <?php else: ?>
                <th>Data</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
          <?php if ($jenis === 'semua'): ?>
            <?php if (empty($rows)): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data modul aktif.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td class="text-start">
                <a href="?jenis=<?= urlencode($r['key']) ?>&tahun=<?= $tahun ?>&bulan=<?= $bulan ?>" class="fw-semibold no-print">
                  <?= htmlspecialchars($r['nama']) ?>
                </a>
                <span class="d-none d-print-inline"><?= htmlspecialchars($r['nama']) ?></span>
              </td>
              <td class="text-center"><?= fmt($r['sasaran']) ?></td>
              <td class="text-center"><?= fmt($r['bulan_ini']) ?></td>
              <td class="text-start small text-muted no-print">
                <a href="?jenis=<?= urlencode($r['key']) ?>&tahun=<?= $tahun ?>&bulan=<?= $bulan ?>">Detail →</a>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          <?php elseif (empty($rows) && $jenis !== 'kependudukan'): ?>
            <tr><td colspan="12" class="text-center text-muted py-4">Tidak ada data pemeriksaan / kegiatan pada periode ini.<br><small>Data yang sudah dihapus tidak ditampilkan.</small></td></tr>
          <?php elseif ($jenis === 'kependudukan'): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Lihat ringkasan jumlah penduduk & KK di kotak statistik di atas.</td></tr>
          <?php else:
            $no = 1;
            foreach ($rows as $row):
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <?php if ($jenis === 'balita'): ?>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal_lahir'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_ibu'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['berat_badan'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['tinggi_badan'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['status_gizi'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['risiko_stunting'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_petugas'] ?? '-') ?></td>
              <?php elseif ($jenis === 'bayi'): ?>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal_lahir'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_ibu'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['berat_badan'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['tinggi_badan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
              <?php elseif ($jenis === 'ibu_hamil'): ?>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td class="text-center"><?php
                  $usia = $row['usia_kehamilan'] ?? $row['usia_kandungan'] ?? $row['usia_kehamilan_master'] ?? null;
                  echo ($usia !== null && $usia !== '') ? htmlspecialchars($usia) . ' minggu' : '-';
                ?></td>
                <td><?= htmlspecialchars($row['hpht'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['berat_badan'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['tekanan_darah'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['catatan'] ?? $row['keluhan'] ?? $row['nama_petugas'] ?? '-') ?></td>
              <?php elseif ($jenis === 'lansia' || $jenis === 'remaja'): ?>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal_lahir'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['berat_badan'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row[$jenis==='lansia'?'tekanan_darah':'tinggi_badan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
              <?php elseif ($jenis === 'imunisasi'): ?>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['jenis_imunisasi'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['dosis'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_petugas'] ?? $row['petugas'] ?? '-') ?></td>
              <?php elseif ($jenis === 'vitamin'): ?>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['jenis_vitamin'] ?? $row['jenis'] ?? '-') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_petugas'] ?? $row['catatan'] ?? '-') ?></td>
              <?php elseif ($jenis === 'kb'): ?>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['jenis_kontrasepsi'] ?? $row['metode'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_petugas'] ?? $row['keterangan'] ?? '-') ?></td>
              <?php elseif ($jenis === 'kegiatan'): ?>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama_posyandu'] ?? $row['nama_kegiatan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['kader_bertugas'] ?? $row['nama_petugas'] ?? $row['keterangan'] ?? '-') ?></td>
              <?php elseif ($jenis === 'kunjungan_rumah'): ?>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['prioritas'] ?? '-') ?> — <?= htmlspecialchars($row['nama_petugas'] ?? $row['catatan'] ?? $row['tindakan'] ?? '-') ?></td>
              <?php endif; ?>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <div class="row mt-4 d-none d-print-flex">
        <div class="col-6 text-center">
          <p>Mengetahui,</p>
          <p class="mb-5">Kepala Desa <?= htmlspecialchars($nama_desa) ?></p>
          <p class="fw-bold text-decoration-underline">________________________</p>
        </div>
        <div class="col-6 text-center">
          <p><?= htmlspecialchars($nama_desa) ?>, <?= date('d') ?> <?= $nama_bulan[(int)date('n')] ?> <?= date('Y') ?></p>
          <p class="mb-5">Penanggung Jawab Posyandu</p>
          <p class="fw-bold text-decoration-underline">________________________</p>
        </div>
      </div>

      <p class="text-muted small mt-3 mb-0 no-print">
        Data hanya menampilkan record yang masih ada di database. Data yang sudah dihapus tidak ikut terbaca.
      </p>
    </div>
  </div>

</div>
</section>

<style>
/* Sembunyikan elemen admin saat print */
@media print {
  .main-sidebar,
  .main-header,
  .main-footer,
  .content-header,
  .control-sidebar,
  .no-print,
  .breadcrumb,
  .navbar,
  aside,
  footer.main-footer,
  #btnCetak {
    display: none !important;
    visibility: hidden !important;
  }
  html, body {
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    height: auto !important;
    overflow: visible !important;
  }
  .wrapper, .content-wrapper, .content, .container-fluid, .card, .card-body {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background: #fff !important;
    width: 100% !important;
    max-width: 100% !important;
  }
  #area-cetak {
    display: block !important;
    visibility: visible !important;
    position: relative !important;
    width: 100% !important;
  }
  #area-cetak * {
    visibility: visible !important;
  }
  .d-print-flex {
    display: flex !important;
  }
  table {
    font-size: 10pt !important;
    page-break-inside: auto;
  }
  tr { page-break-inside: avoid; page-break-after: auto; }
  thead { display: table-header-group; }
  a[href]::after { content: none !important; }
}
</style>

<script>
function cetakLaporan() {
  // Pastikan area cetak terlihat
  var area = document.getElementById('area-cetak');
  if (!area) {
    window.print();
    return;
  }
  // Trigger print dialog
  setTimeout(function() {
    window.print();
  }, 100);
}

// Shortcut Ctrl+P tetap berfungsi normal
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
