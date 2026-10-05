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

require_once __DIR__ . '/../../config/init.php';
requireLogin();

$no_kk = trim($_GET['no_kk'] ?? '');
$msg = ''; $msgType = '';

$hasPhbs = false;
$chk = @query("SHOW TABLES LIKE 'phbs'");
if ($chk && $chk->num_rows > 0) $hasPhbs = true;
$hasKeluarga = false;
$chk2 = @query("SHOW TABLES LIKE 'keluarga'");
if ($chk2 && $chk2->num_rows > 0) $hasKeluarga = true;

$existing = ($no_kk && $hasPhbs) ? fetchOne("SELECT * FROM phbs WHERE no_kk='".escape($no_kk)."'") : null;
$keluarga = ($no_kk && $hasKeluarga) ? fetchOne("SELECT * FROM keluarga WHERE no_kk='".escape($no_kk)."'") : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$hasPhbs) {
    $msg = 'Tabel PHBS belum ada. Jalankan migrasi_tabel_baru.sql';
    $msgType = 'danger';
  } else {
    $no_kk = escape(trim($_POST['no_kk'] ?? ''));
    $nama = escape($_POST['nama_kepala'] ?? '');
    $dusun = escape($_POST['dusun'] ?? '');
    $rt = escape($_POST['rt'] ?? '');
    $rw = escape($_POST['rw'] ?? '');
    $fields = array('cuci_tangan','air_bersih','menggunakan_jamban','tidak_bab_sembarangan','pengelolaan_sampah','pengelolaan_limbah','bebas_asap_rokok','kebersihan_lingkungan');
    $vals = array(); $skor = 0;
    foreach ($fields as $f) {
      $v = $_POST[$f] ?? 'Tidak Diketahui';
      if (!in_array($v, array('Ya','Tidak','Tidak Diketahui'))) $v = 'Tidak Diketahui';
      $vals[$f] = escape($v);
      if ($v === 'Ya') $skor++;
    }
    $tgl = trim($_POST['tanggal_pemantauan'] ?? date('Y-m-d'));
    if ($tgl === '') $tgl = date('Y-m-d');
    $tgl = escape($tgl);
    $catatan = escape($_POST['catatan'] ?? '');
    $petugas = (int)($_SESSION['kader_id'] ?? 0);
    $petugasSql = $petugas > 0 ? $petugas : 'NULL';

    if ($no_kk === '') {
      $msg = 'No. KK wajib diisi';
      $msgType = 'danger';
    } else {
      $cek = fetchOne("SELECT id FROM phbs WHERE no_kk='$no_kk'");
      if ($cek) {
        $ok = query("UPDATE phbs SET nama_kepala='$nama',dusun='$dusun',rt='$rt',rw='$rw',
          cuci_tangan='{$vals['cuci_tangan']}',air_bersih='{$vals['air_bersih']}',menggunakan_jamban='{$vals['menggunakan_jamban']}',
          tidak_bab_sembarangan='{$vals['tidak_bab_sembarangan']}',pengelolaan_sampah='{$vals['pengelolaan_sampah']}',
          pengelolaan_limbah='{$vals['pengelolaan_limbah']}',bebas_asap_rokok='{$vals['bebas_asap_rokok']}',
          kebersihan_lingkungan='{$vals['kebersihan_lingkungan']}',skor_phbs=$skor,tanggal_pemantauan='$tgl',
          catatan='$catatan',petugas_id=$petugasSql WHERE no_kk='$no_kk'");
      } else {
        $ok = query("INSERT INTO phbs (no_kk,nama_kepala,dusun,rt,rw,cuci_tangan,air_bersih,menggunakan_jamban,tidak_bab_sembarangan,pengelolaan_sampah,pengelolaan_limbah,bebas_asap_rokok,kebersihan_lingkungan,skor_phbs,tanggal_pemantauan,catatan,petugas_id)
          VALUES ('$no_kk','$nama','$dusun','$rt','$rw','{$vals['cuci_tangan']}','{$vals['air_bersih']}','{$vals['menggunakan_jamban']}','{$vals['tidak_bab_sembarangan']}','{$vals['pengelolaan_sampah']}','{$vals['pengelolaan_limbah']}','{$vals['bebas_asap_rokok']}','{$vals['kebersihan_lingkungan']}',$skor,'$tgl','$catatan',$petugasSql)");
      }
      if ($ok) {
        header('Location: index.php?msg=success');
        exit;
      }
      global $conn;
      $msg = 'Gagal: ' . ($conn->error ?: 'unknown');
      $msgType = 'danger';
    }
    $no_kk_raw = trim($_POST['no_kk'] ?? '');
    $existing = $hasPhbs ? fetchOne("SELECT * FROM phbs WHERE no_kk='".escape($no_kk_raw)."'") : null;
    $keluarga = $hasKeluarga ? fetchOne("SELECT * FROM keluarga WHERE no_kk='".escape($no_kk_raw)."'") : null;
    $no_kk = $no_kk_raw;
  }
}

$e = $existing ?: array();
$k = $keluarga ?: array();
$opts = array('Ya','Tidak','Tidak Diketahui');
$indikator = array(
  'cuci_tangan' => 'Cuci Tangan Pakai Sabun',
  'air_bersih' => 'Air Bersih Tersedia',
  'menggunakan_jamban' => 'Menggunakan Jamban',
  'tidak_bab_sembarangan' => 'Tidak BAB Sembarangan',
  'pengelolaan_sampah' => 'Pengelolaan Sampah Baik',
  'pengelolaan_limbah' => 'Pengelolaan Air Limbah',
  'bebas_asap_rokok' => 'Rumah Bebas Asap Rokok',
  'kebersihan_lingkungan' => 'Kebersihan Lingkungan',
);
$page_title = 'Input PHBS';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-hands-wash me-2 text-primary"></i>Input / Update PHBS</h1></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
<?php if ($msg): ?><div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if (!$hasPhbs): ?><div class="alert alert-warning">Tabel <b>phbs</b> belum ada. Jalankan migrasi_tabel_baru.sql</div><?php endif; ?>

<div class="card"><div class="card-body">
<form method="post">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <div class="row mb-3">
    <div class="col-md-8 position-relative">
      <label class="form-label">Cari Nama Kepala Keluarga</label>
      <input type="text" id="cariKeluarga" class="form-control form-control-lg" placeholder="Ketik nama..." autocomplete="off"
        value="<?= htmlspecialchars($e['nama_kepala'] ?? $k['nama_kepala'] ?? '') ?>">
      <div id="hasilCariKK" class="list-group shadow border" style="position:absolute;left:0;right:0;z-index:2000;max-height:250px;overflow-y:auto;display:none;background:#fff;"></div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">No. KK *</label>
      <input type="text" name="no_kk" id="f_nokk" class="form-control" required value="<?= htmlspecialchars($no_kk ?: ($e['no_kk'] ?? $k['no_kk'] ?? '')) ?>"></div>
    <div class="col-md-3 mb-3"><label class="form-label">Nama Kepala</label>
      <input type="text" name="nama_kepala" id="f_nama_kepala" class="form-control" value="<?= htmlspecialchars($e['nama_kepala'] ?? $k['nama_kepala'] ?? '') ?>"></div>
    <div class="col-md-2 mb-3"><label class="form-label">Dusun</label>
      <input type="text" name="dusun" id="f_dusun" class="form-control" value="<?= htmlspecialchars($e['dusun'] ?? $k['dusun'] ?? '') ?>"></div>
    <div class="col-md-2 mb-3"><label class="form-label">RT</label>
      <input type="text" name="rt" id="f_rt" class="form-control" value="<?= htmlspecialchars($e['rt'] ?? $k['rt'] ?? '') ?>"></div>
    <div class="col-md-2 mb-3"><label class="form-label">RW</label>
      <input type="text" name="rw" id="f_rw" class="form-control" value="<?= htmlspecialchars($e['rw'] ?? $k['rw'] ?? '') ?>"></div>
  </div>
  <h6 class="text-primary border-bottom pb-2">Indikator PHBS</h6>
  <div class="row">
    <?php foreach ($indikator as $key => $label): ?>
    <div class="col-md-3 mb-3">
      <label class="form-label"><?= $label ?></label>
      <select name="<?= $key ?>" class="form-select">
        <?php foreach ($opts as $o): ?>
        <option <?= (($e[$key] ?? '') === $o) ? 'selected' : '' ?>><?= $o ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endforeach; ?>
    <div class="col-md-3 mb-3"><label class="form-label">Tgl Pemantauan</label>
      <input type="date" name="tanggal_pemantauan" class="form-control" value="<?= htmlspecialchars($e['tanggal_pemantauan'] ?? date('Y-m-d')) ?>"></div>
    <div class="col-md-9 mb-3"><label class="form-label">Catatan</label>
      <input type="text" name="catatan" class="form-control" value="<?= htmlspecialchars($e['catatan'] ?? '') ?>"></div>
  </div>
  <button type="submit" class="btn btn-primary" <?= !$hasPhbs ? 'disabled' : '' ?>><i class="fas fa-save me-1"></i>Simpan</button>
  <a href="index.php" class="btn btn-secondary">Batal</a>
</form>
</div></div>
</div></section>
<?php
$extra_js = '
<script>
$(function(){
  var timer=null,$input=$("#cariKeluarga"),$hasil=$("#hasilCariKK");
  $input.on("input keyup",function(){
    clearTimeout(timer);
    var q=$(this).val().trim();
    if(q.length<1){$hasil.hide().empty();return;}
    timer=setTimeout(function(){
      $.getJSON("' . APP_URL . '/ajax/cari_penduduk.php",{q:q,mode:"keluarga"}).done(function(res){
        $hasil.empty();
        if(!res.data||!res.data.length){$hasil.append(\'<div class="list-group-item text-muted small">Tidak ditemukan</div>\').show();return;}
        res.data.forEach(function(k){
          var $a=$(\'<a href="#" class="list-group-item list-group-item-action"></a>\');
          $a.html("<strong>"+(k.nama_kepala||"")+"</strong><br><small>KK: "+(k.no_kk||"-")+"</small>");
          $a.on("click",function(e){e.preventDefault();
            $("#f_nokk").val(k.no_kk||"");$("#f_nama_kepala").val(k.nama_kepala||"");
            $("#f_dusun").val(k.dusun||"");$("#f_rt").val(k.rt||"");$("#f_rw").val(k.rw||"");
            $input.val(k.nama_kepala||"");$hasil.hide();
          });
          $hasil.append($a);
        });
        $hasil.show();
      }).fail(function(x){$hasil.html(\'<div class="list-group-item text-danger">Gagal (\'+x.status+\')</div>\').show();});
    },200);
  });
  $(document).on("click",function(e){if(!$(e.target).closest("#cariKeluarga,#hasilCariKK").length)$hasil.hide();});
});
</script>
';
include __DIR__ . '/../../includes/footer.php';
