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
 * Tambah Peserta KB - POST diproses sebelum HTML (hindari blank page)
 */
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$msg = '';
$msgType = '';

$hasKb = false;
$chk = @query("SHOW TABLES LIKE 'kb'");
if ($chk && $chk->num_rows > 0) $hasKb = true;

// Auto-create tabel minimal jika belum ada
if (!$hasKb) {
    @query("CREATE TABLE IF NOT EXISTS kb (
      id INT PRIMARY KEY AUTO_INCREMENT,
      nama VARCHAR(150) NOT NULL,
      nik VARCHAR(16) DEFAULT '',
      no_kk VARCHAR(16) DEFAULT '',
      jenis_kelamin ENUM('L','P') DEFAULT 'P',
      tanggal_lahir DATE NULL,
      jenis_kontrasepsi VARCHAR(50) DEFAULT 'Pil',
      tanggal_pelayanan DATE NULL,
      tanggal_mulai DATE NULL,
      tanggal_kontrol DATE NULL,
      petugas_id INT NULL,
      keterangan TEXT,
      status VARCHAR(30) DEFAULT 'Aktif',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $chk = @query("SHOW TABLES LIKE 'kb'");
    if ($chk && $chk->num_rows > 0) $hasKb = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasKb) {
        $msg = 'Tabel KB belum tersedia. Jalankan migrasi_tabel_baru.sql';
        $msgType = 'danger';
    } else {
        $nama = escape(trim($_POST['nama'] ?? ''));
        $nik = escape(trim($_POST['nik'] ?? ''));
        $no_kk = escape(trim($_POST['no_kk'] ?? ''));
        $jk = escape($_POST['jenis_kelamin'] ?? 'P');
        if ($jk !== 'L' && $jk !== 'P') $jk = 'P';

        $tgl_lahir = trim($_POST['tanggal_lahir'] ?? '');
        $jenis = escape($_POST['jenis_kontrasepsi'] ?? 'Pil');
        $tgl_pel = trim($_POST['tanggal_pelayanan'] ?? date('Y-m-d'));
        if ($tgl_pel === '') $tgl_pel = date('Y-m-d');
        $tgl_mulai = trim($_POST['tanggal_mulai'] ?? '');
        $tgl_kontrol = trim($_POST['tanggal_kontrol'] ?? '');
        $ket = escape($_POST['keterangan'] ?? '');
        $status = escape($_POST['status'] ?? 'Aktif');

        $petugas = (int)($_SESSION['kader_id'] ?? 0);
        $petugasSql = $petugas > 0 ? (string)$petugas : 'NULL';

        $tgl_lahir_sql = ($tgl_lahir !== '') ? "'" . escape($tgl_lahir) . "'" : 'NULL';
        $tgl_mulai_sql = ($tgl_mulai !== '') ? "'" . escape($tgl_mulai) . "'" : 'NULL';
        $tgl_kontrol_sql = ($tgl_kontrol !== '') ? "'" . escape($tgl_kontrol) . "'" : 'NULL';
        $tgl_pel_sql = "'" . escape($tgl_pel) . "'";

        if ($nama === '') {
            $msg = 'Nama wajib diisi.';
            $msgType = 'danger';
        } else {
            $sql = "INSERT INTO kb (nama, nik, no_kk, jenis_kelamin, tanggal_lahir, jenis_kontrasepsi, tanggal_pelayanan, tanggal_mulai, tanggal_kontrol, keterangan, status, petugas_id)
                    VALUES ('$nama', '$nik', '$no_kk', '$jk', $tgl_lahir_sql, '$jenis', $tgl_pel_sql, $tgl_mulai_sql, $tgl_kontrol_sql, '$ket', '$status', $petugasSql)";
            $ok = query($sql);
            if ($ok) {
                // Jangan redirect dulu jika headers bermasalah - gunakan JS redirect setelah halaman
                if (!headers_sent()) {
                    header('Location: index.php?msg=success');
                    exit;
                }
                $msg = 'Data berhasil disimpan! <a href="index.php">Klik di sini jika tidak berpindah otomatis</a>';
                $msgType = 'success';
                echo '<script>location.href="index.php?msg=success";</script>';
            } else {
                global $conn;
                $msg = 'Gagal menyimpan: ' . ($conn->error ? $conn->error : 'unknown');
                $msgType = 'danger';
            }
        }
    }
}

$page_title = 'Tambah Peserta KB';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-pills me-2 text-primary"></i>Tambah Peserta KB</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="index.php">KB</a></li>
          <li class="breadcrumb-item active">Tambah</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content"><div class="container-fluid">
<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
<?php endif; ?>
<?php if (!$hasKb): ?>
  <div class="alert alert-warning">Tabel <b>kb</b> belum ada. Jalankan <code>database/migrasi_tabel_baru.sql</code>.</div>
<?php endif; ?>

<div class="card border-primary mb-3">
  <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-search me-2"></i>Pilih dari Data Penduduk</h5></div>
  <div class="card-body">
    <div class="position-relative">
      <input type="text" id="cariPenduduk" class="form-control form-control-lg" placeholder="Ketik nama..." autocomplete="off">
      <div id="hasilCari" class="list-group shadow border" style="position:absolute;left:0;right:0;z-index:2000;max-height:280px;overflow-y:auto;display:none;background:#fff;"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama <span class="text-danger">*</span></label>
          <input type="text" name="nama" id="f_nama" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">NIK</label>
          <input type="text" name="nik" id="f_nik" class="form-control" maxlength="16">
        </div>
        <div class="col-md-3">
          <label class="form-label">No. KK</label>
          <input type="text" name="no_kk" id="f_nokk" class="form-control" maxlength="16">
        </div>
        <div class="col-md-3">
          <label class="form-label">Jenis Kelamin</label>
          <select name="jenis_kelamin" id="f_jk" class="form-select">
            <option value="P">Perempuan</option>
            <option value="L">Laki-laki</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" id="f_tgl" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Jenis Kontrasepsi</label>
          <select name="jenis_kontrasepsi" class="form-select">
            <?php foreach (array('Pil','Suntik','IUD','Implan','Kondom','MOW','MOP','Lainnya') as $o): ?>
            <option><?= $o ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tgl Pelayanan</label>
          <input type="date" name="tanggal_pelayanan" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tgl Mulai</label>
          <input type="date" name="tanggal_mulai" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tgl Kontrol</label>
          <input type="date" name="tanggal_kontrol" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option>Aktif</option>
            <option>Selesai</option>
            <option>Ganti Metode</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-control">
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary" <?= !$hasKb ? 'disabled' : '' ?>><i class="fas fa-save me-1"></i>Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
</div></section>

<?php
$extra_js = '
<script>
$(function(){
  var timer=null, $input=$("#cariPenduduk"), $hasil=$("#hasilCari");
  $input.on("input keyup", function(){
    clearTimeout(timer);
    var q=$(this).val().trim();
    if(q.length<1){$hasil.hide().empty();return;}
    timer=setTimeout(function(){
      $.getJSON("' . APP_URL . '/ajax/cari_penduduk.php",{q:q}).done(function(res){
        $hasil.empty();
        if(!res.data||!res.data.length){
          $hasil.append(\'<div class="list-group-item text-muted small">Tidak ditemukan</div>\').show();
          return;
        }
        res.data.forEach(function(p){
          var $a=$(\'<a href="#" class="list-group-item list-group-item-action"></a>\');
          $a.html("<strong>"+(p.nama||"")+"</strong> <code class=\\"small\\">"+(p.nik||"")+"</code>");
          $a.on("click",function(e){
            e.preventDefault();
            $("#f_nama").val(p.nama||"");
            $("#f_nik").val(p.nik||"");
            $("#f_nokk").val(p.no_kk||"");
            $("#f_jk").val(p.jenis_kelamin||"P");
            $("#f_tgl").val(p.tanggal_lahir||"");
            $input.val(p.nama||"");
            $hasil.hide();
          });
          $hasil.append($a);
        });
        $hasil.show();
      }).fail(function(x){
        $hasil.html(\'<div class="list-group-item text-danger small">Gagal (\'+x.status+\')</div>\').show();
      });
    },200);
  });
  $(document).on("click",function(e){
    if(!$(e.target).closest("#cariPenduduk,#hasilCari").length) $hasil.hide();
  });
});
</script>
';
include __DIR__ . '/../../includes/footer.php';
