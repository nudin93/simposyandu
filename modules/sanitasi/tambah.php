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
 * Input Sanitasi - proses POST sebelum HTML (hindari blank putih)
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$msg = '';
$msgType = '';
$no_kk = trim($_GET['no_kk'] ?? '');

$hasSanitasi = false;
$chk = @query("SHOW TABLES LIKE 'sanitasi'");
if ($chk && $chk->num_rows > 0) $hasSanitasi = true;

// Auto-create minimal table
if (!$hasSanitasi) {
    @query("CREATE TABLE IF NOT EXISTS sanitasi (
      id INT PRIMARY KEY AUTO_INCREMENT,
      no_kk VARCHAR(16) NOT NULL,
      nama_kepala VARCHAR(150),
      nik_kepala VARCHAR(16),
      alamat TEXT,
      dusun VARCHAR(100),
      rt VARCHAR(5),
      rw VARCHAR(5),
      kepemilikan_jamban VARCHAR(50) DEFAULT 'Tidak Memiliki',
      jenis_jamban VARCHAR(50) DEFAULT 'Tidak Ada',
      kondisi_jamban VARCHAR(50) DEFAULT 'Tidak Ada',
      pembuangan_tinja VARCHAR(50) DEFAULT 'Tidak Diketahui',
      sumber_air VARCHAR(50) DEFAULT 'Sumur Gali',
      kondisi_air VARCHAR(50) DEFAULT 'Tidak Diketahui',
      pengelolaan_sampah VARCHAR(50) DEFAULT 'Dibakar',
      tempat_sampah_tersedia TINYINT(1) DEFAULT 0,
      tempat_sampah_tertutup TINYINT(1) DEFAULT 0,
      pemilahan_sampah TINYINT(1) DEFAULT 0,
      saluran_limbah VARCHAR(50) DEFAULT 'Tidak Tersedia',
      lantai VARCHAR(30) DEFAULT 'Semen',
      dinding VARCHAR(30) DEFAULT 'Tembok',
      atap VARCHAR(30) DEFAULT 'Genteng',
      ventilasi VARCHAR(30) DEFAULT 'Baik',
      pencahayaan VARCHAR(30) DEFAULT 'Baik',
      kepadatan_hunian VARCHAR(30) DEFAULT 'Sesuai',
      kebersihan_rumah VARCHAR(30) DEFAULT 'Cukup',
      tanggal_pemantauan DATE NULL,
      petugas_id INT NULL,
      catatan TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uk_no_kk (no_kk)
    )");
    $chk = @query("SHOW TABLES LIKE 'sanitasi'");
    if ($chk && $chk->num_rows > 0) $hasSanitasi = true;
}

$hasKeluarga = false;
$chk2 = @query("SHOW TABLES LIKE 'keluarga'");
if ($chk2 && $chk2->num_rows > 0) $hasKeluarga = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasSanitasi) {
        $msg = 'Tabel sanitasi belum tersedia.';
        $msgType = 'danger';
    } else {
        $no_kk = escape(trim($_POST['no_kk'] ?? ''));
        $nama_kepala = escape($_POST['nama_kepala'] ?? '');
        $nik_kepala = escape($_POST['nik_kepala'] ?? '');
        $alamat = escape($_POST['alamat'] ?? '');
        $dusun = escape($_POST['dusun'] ?? '');
        $rt = escape($_POST['rt'] ?? '');
        $rw = escape($_POST['rw'] ?? '');
        $kepemilikan = escape($_POST['kepemilikan_jamban'] ?? 'Tidak Memiliki');
        $jenis = escape($_POST['jenis_jamban'] ?? 'Tidak Ada');
        $kondisi = escape($_POST['kondisi_jamban'] ?? 'Tidak Ada');
        $tinja = escape($_POST['pembuangan_tinja'] ?? 'Tidak Diketahui');
        $sumber = escape($_POST['sumber_air'] ?? 'Sumur Gali');
        $kondisi_air = escape($_POST['kondisi_air'] ?? 'Tidak Diketahui');
        $sampah = escape($_POST['pengelolaan_sampah'] ?? 'Dibakar');
        $tmp_sampah = isset($_POST['tempat_sampah_tersedia']) ? 1 : 0;
        $tmp_tutup = isset($_POST['tempat_sampah_tertutup']) ? 1 : 0;
        $pilah = isset($_POST['pemilahan_sampah']) ? 1 : 0;
        $limbah = escape($_POST['saluran_limbah'] ?? 'Tidak Tersedia');
        $lantai = escape($_POST['lantai'] ?? 'Semen');
        $dinding = escape($_POST['dinding'] ?? 'Tembok');
        $atap = escape($_POST['atap'] ?? 'Genteng');
        $ventilasi = escape($_POST['ventilasi'] ?? 'Baik');
        $pencahayaan = escape($_POST['pencahayaan'] ?? 'Baik');
        $kepadatan = escape($_POST['kepadatan_hunian'] ?? 'Sesuai');
        $kebersihan = escape($_POST['kebersihan_rumah'] ?? 'Cukup');
        $tgl = escape($_POST['tanggal_pemantauan'] ?? date('Y-m-d'));
        if ($tgl === '') $tgl = date('Y-m-d');
        $catatan = escape($_POST['catatan'] ?? '');
        $petugas = (int)($_SESSION['kader_id'] ?? 0);
        $petugasSql = $petugas > 0 ? (string)$petugas : 'NULL';

        if ($no_kk === '') {
            $msg = 'No. KK wajib diisi. Cari dan pilih keluarga dulu.';
            $msgType = 'danger';
        } else {
            $cek = fetchOne("SELECT id FROM sanitasi WHERE no_kk='$no_kk'");
            if ($cek) {
                $ok = query("UPDATE sanitasi SET
                  nama_kepala='$nama_kepala', nik_kepala='$nik_kepala', alamat='$alamat',
                  dusun='$dusun', rt='$rt', rw='$rw',
                  kepemilikan_jamban='$kepemilikan', jenis_jamban='$jenis', kondisi_jamban='$kondisi',
                  pembuangan_tinja='$tinja', sumber_air='$sumber', kondisi_air='$kondisi_air',
                  pengelolaan_sampah='$sampah', tempat_sampah_tersedia=$tmp_sampah,
                  tempat_sampah_tertutup=$tmp_tutup, pemilahan_sampah=$pilah,
                  saluran_limbah='$limbah', lantai='$lantai', dinding='$dinding', atap='$atap',
                  ventilasi='$ventilasi', pencahayaan='$pencahayaan', kepadatan_hunian='$kepadatan',
                  kebersihan_rumah='$kebersihan', tanggal_pemantauan='$tgl', catatan='$catatan',
                  petugas_id=$petugasSql
                  WHERE no_kk='$no_kk'");
            } else {
                $ok = query("INSERT INTO sanitasi
                  (no_kk,nama_kepala,nik_kepala,alamat,dusun,rt,rw,kepemilikan_jamban,jenis_jamban,kondisi_jamban,
                   pembuangan_tinja,sumber_air,kondisi_air,pengelolaan_sampah,tempat_sampah_tersedia,tempat_sampah_tertutup,
                   pemilahan_sampah,saluran_limbah,lantai,dinding,atap,ventilasi,pencahayaan,kepadatan_hunian,
                   kebersihan_rumah,tanggal_pemantauan,catatan,petugas_id)
                  VALUES
                  ('$no_kk','$nama_kepala','$nik_kepala','$alamat','$dusun','$rt','$rw','$kepemilikan','$jenis','$kondisi',
                   '$tinja','$sumber','$kondisi_air','$sampah',$tmp_sampah,$tmp_tutup,$pilah,'$limbah','$lantai','$dinding',
                   '$atap','$ventilasi','$pencahayaan','$kepadatan','$kebersihan','$tgl','$catatan',$petugasSql)");
            }
            if ($ok) {
                if (!headers_sent()) {
                    header('Location: index.php?msg=success');
                    exit;
                }
                $msg = 'Data berhasil disimpan! <a href="index.php">Kembali ke daftar</a>';
                $msgType = 'success';
                echo '<script>location.href="index.php?msg=success";</script>';
            } else {
                global $conn;
                $msg = 'Gagal menyimpan: ' . ($conn->error ? $conn->error : 'unknown');
                $msgType = 'danger';
            }
        }
        $no_kk = trim($_POST['no_kk'] ?? '');
    }
}

$existing = ($no_kk && $hasSanitasi) ? fetchOne("SELECT * FROM sanitasi WHERE no_kk='" . escape($no_kk) . "'") : null;
$keluarga = ($no_kk && $hasKeluarga) ? fetchOne("SELECT * FROM keluarga WHERE no_kk='" . escape($no_kk) . "'") : null;
$e = $existing ?: array();
$k = $keluarga ?: array();

$page_title = 'Input Sanitasi';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-toilet me-2 text-primary"></i>Input / Update Sanitasi</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="index.php">Sanitasi</a></li>
          <li class="breadcrumb-item active">Input</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content"><div class="container-fluid">
<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
<?php endif; ?>
<?php if (!$hasSanitasi): ?>
  <div class="alert alert-warning">Tabel sanitasi belum ada.</div>
<?php endif; ?>

<div class="card"><div class="card-body">
<form method="post" action="">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <h6 class="text-primary border-bottom pb-2">Pilih Keluarga (ketik nama kepala)</h6>
  <div class="row mb-3">
    <div class="col-md-8 position-relative">
      <input type="text" id="cariKeluarga" class="form-control form-control-lg" placeholder="Ketik nama kepala keluarga..." autocomplete="off"
        value="<?= htmlspecialchars($e['nama_kepala'] ?? $k['nama_kepala'] ?? '') ?>">
      <div id="hasilCariKK" class="list-group shadow border" style="position:absolute;left:0;right:0;z-index:2000;max-height:280px;overflow-y:auto;display:none;background:#fff;"></div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3">
      <label class="form-label">No. KK <span class="text-danger">*</span></label>
      <input type="text" name="no_kk" id="f_nokk" class="form-control" required
        value="<?= htmlspecialchars($no_kk ?: ($e['no_kk'] ?? $k['no_kk'] ?? '')) ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Nama Kepala</label>
      <input type="text" name="nama_kepala" id="f_nama_kepala" class="form-control"
        value="<?= htmlspecialchars($e['nama_kepala'] ?? $k['nama_kepala'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">NIK Kepala</label>
      <input type="text" name="nik_kepala" id="f_nik_kepala" class="form-control"
        value="<?= htmlspecialchars($e['nik_kepala'] ?? $k['nik_kepala'] ?? '') ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Dusun</label>
      <input type="text" name="dusun" id="f_dusun" class="form-control"
        value="<?= htmlspecialchars($e['dusun'] ?? $k['dusun'] ?? '') ?>">
    </div>
    <div class="col-md-2 mb-3">
      <label class="form-label">RT</label>
      <input type="text" name="rt" id="f_rt" class="form-control" value="<?= htmlspecialchars($e['rt'] ?? $k['rt'] ?? '') ?>">
    </div>
    <div class="col-md-2 mb-3">
      <label class="form-label">RW</label>
      <input type="text" name="rw" id="f_rw" class="form-control" value="<?= htmlspecialchars($e['rw'] ?? $k['rw'] ?? '') ?>">
    </div>
    <div class="col-md-8 mb-3">
      <label class="form-label">Alamat</label>
      <input type="text" name="alamat" id="f_alamat" class="form-control" value="<?= htmlspecialchars($e['alamat'] ?? $k['alamat'] ?? '') ?>">
    </div>
  </div>

  <h6 class="text-primary border-bottom pb-2 mt-3">Kepemilikan Jamban</h6>
  <div class="row">
    <?php
    $opts = array(
      'kepemilikan_jamban' => array('Jamban Sendiri','Jamban Bersama','Jamban Umum','Tidak Memiliki'),
      'jenis_jamban' => array('Leher Angsa','Cemplung','Plengsengan','Lainnya','Tidak Ada'),
      'kondisi_jamban' => array('Baik','Rusak Ringan','Rusak Berat','Tidak Layak','Tidak Ada'),
      'pembuangan_tinja' => array('Septic Tank','IPAL','Sungai','Kebun/Tanah Terbuka','Lainnya','Tidak Diketahui'),
    );
    $labels = array(
      'kepemilikan_jamban' => 'Kepemilikan',
      'jenis_jamban' => 'Jenis Jamban',
      'kondisi_jamban' => 'Kondisi',
      'pembuangan_tinja' => 'Pembuangan Tinja',
    );
    foreach ($opts as $name => $list): ?>
    <div class="col-md-3 mb-3">
      <label class="form-label"><?= $labels[$name] ?></label>
      <select name="<?= $name ?>" class="form-select">
        <?php foreach ($list as $o): ?>
        <option value="<?= $o ?>" <?= (($e[$name] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endforeach; ?>
  </div>

  <h6 class="text-primary border-bottom pb-2 mt-3">Sumber Air & Sampah</h6>
  <div class="row">
    <div class="col-md-3 mb-3">
      <label class="form-label">Sumber Air</label>
      <select name="sumber_air" class="form-select">
        <?php foreach (array('PDAM','Sumur Gali','Sumur Bor','Mata Air','Sungai','Air Hujan','Air Isi Ulang','Lainnya') as $o): ?>
        <option <?= (($e['sumber_air'] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Kondisi Air</label>
      <select name="kondisi_air" class="form-select">
        <?php foreach (array('Layak','Perlu Perlindungan','Tidak Layak','Tidak Diketahui') as $o): ?>
        <option <?= (($e['kondisi_air'] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Pengelolaan Sampah</label>
      <select name="pengelolaan_sampah" class="form-select">
        <?php foreach (array('Diangkut Petugas','Dibakar','Ditimbun','Bank Sampah','Dibuang ke Sungai','Dibuang Sembarangan','Lainnya') as $o): ?>
        <option <?= (($e['pengelolaan_sampah'] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Saluran Limbah</label>
      <select name="saluran_limbah" class="form-select">
        <?php foreach (array('Tersedia Tertutup','Tersedia Terbuka','Septic/IPAL','Dialirkan ke Tanah','Dialirkan ke Sungai','Tidak Tersedia') as $o): ?>
        <option <?= (($e['saluran_limbah'] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 mb-3"><div class="form-check mt-4">
      <input type="checkbox" name="tempat_sampah_tersedia" value="1" class="form-check-input" id="c1" <?= !empty($e['tempat_sampah_tersedia']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="c1">Tempat Sampah Tersedia</label>
    </div></div>
    <div class="col-md-4 mb-3"><div class="form-check mt-4">
      <input type="checkbox" name="tempat_sampah_tertutup" value="1" class="form-check-input" id="c2" <?= !empty($e['tempat_sampah_tertutup']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="c2">Tempat Sampah Tertutup</label>
    </div></div>
    <div class="col-md-4 mb-3"><div class="form-check mt-4">
      <input type="checkbox" name="pemilahan_sampah" value="1" class="form-check-input" id="c3" <?= !empty($e['pemilahan_sampah']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="c3">Pemilahan Sampah</label>
    </div></div>
  </div>

  <h6 class="text-primary border-bottom pb-2 mt-3">Kondisi Rumah</h6>
  <div class="row">
    <?php
    $rumah = array(
      'lantai' => array('Keramik','Semen','Tanah','Kayu','Lainnya'),
      'dinding' => array('Tembok','Kayu','Bambu','Lainnya'),
      'atap' => array('Genteng','Seng','Asbes','Rumbia','Lainnya'),
      'ventilasi' => array('Baik','Kurang','Tidak Ada'),
      'pencahayaan' => array('Baik','Kurang','Tidak Ada'),
      'kepadatan_hunian' => array('Sesuai','Padat','Sangat Padat'),
      'kebersihan_rumah' => array('Bersih','Cukup','Kurang'),
    );
    foreach ($rumah as $name => $list): ?>
    <div class="col-md-3 mb-3">
      <label class="form-label"><?= ucfirst(str_replace('_', ' ', $name)) ?></label>
      <select name="<?= $name ?>" class="form-select">
        <?php foreach ($list as $o): ?>
        <option <?= (($e[$name] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endforeach; ?>
    <div class="col-md-3 mb-3">
      <label class="form-label">Tgl Pemantauan</label>
      <input type="date" name="tanggal_pemantauan" class="form-control" value="<?= htmlspecialchars($e['tanggal_pemantauan'] ?? date('Y-m-d')) ?>">
    </div>
    <div class="col-md-9 mb-3">
      <label class="form-label">Catatan</label>
      <input type="text" name="catatan" class="form-control" value="<?= htmlspecialchars($e['catatan'] ?? '') ?>">
    </div>
  </div>

  <button type="submit" class="btn btn-primary" <?= !$hasSanitasi ? 'disabled' : '' ?>><i class="fas fa-save me-1"></i>Simpan</button>
  <a href="index.php" class="btn btn-secondary">Batal</a>
</form>
</div></div>
</div></section>

<?php
$extra_js = '
<script>
$(function(){
  var timer=null;
  var $input=$("#cariKeluarga");
  var $hasil=$("#hasilCariKK");
  $input.on("input keyup", function(){
    clearTimeout(timer);
    var q=$(this).val().trim();
    if(q.length<1){$hasil.hide().empty();return;}
    timer=setTimeout(function(){
      $.getJSON("' . APP_URL . '/ajax/cari_penduduk.php",{q:q, mode:"keluarga"})
        .done(function(res){
          $hasil.empty();
          if(!res.data||!res.data.length){
            $hasil.append(\'<div class="list-group-item text-muted small">Tidak ditemukan. Buat dulu di Data Keluarga.</div>\').show();
            return;
          }
          res.data.forEach(function(k){
            var $a=$(\'<a href="#" class="list-group-item list-group-item-action py-2"></a>\');
            $a.html("<strong>"+(k.nama_kepala||"")+"</strong><br><small class=\\"text-muted\\">KK: "+(k.no_kk||"-")+" · "+(k.dusun||"")+"</small>");
            $a.on("click", function(e){
              e.preventDefault();
              $("#f_nokk").val(k.no_kk||"");
              $("#f_nama_kepala").val(k.nama_kepala||"");
              $("#f_nik_kepala").val(k.nik_kepala||"");
              $("#f_dusun").val(k.dusun||"");
              $("#f_rt").val(k.rt||"");
              $("#f_rw").val(k.rw||"");
              $("#f_alamat").val(k.alamat||"");
              $input.val(k.nama_kepala||"");
              $hasil.hide();
            });
            $hasil.append($a);
          });
          $hasil.show();
        })
        .fail(function(xhr){
          $hasil.html(\'<div class="list-group-item text-danger small">Gagal (\'+ (xhr.status||"?") +\').</div>\').show();
        });
    },200);
  });
  $(document).on("click", function(e){
    if(!$(e.target).closest("#cariKeluarga, #hasilCariKK").length) $hasil.hide();
  });
});
</script>
';
include __DIR__ . '/../../includes/footer.php';
