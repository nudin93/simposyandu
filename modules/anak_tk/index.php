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
 * SIMPOSYANDU - Anak TK (3-7 tahun, berbasis Kelas)
 * Umur 3-7 diambil dari OpenSID/lokal tapi TIDAK ditampilkan.
 * Anak tampil di daftar hanya jika sudah masuk Kelas (TK A / TK B).
 * Alur kelas: TK A -> TK B -> Lulus.
 */
$page_title = 'Anak TK (3-7 Tahun)';
require_once __DIR__ . '/../../includes/header.php';

$MIN_TAHUN = 3;
$MAX_TAHUN = 7;
$filterKelas = trim($_GET['kelas'] ?? '');
$showAll = isset($_GET['tampil']) && $_GET['tampil'] === 'semua';

$lokal = [];
try {
    $lokal = fetchAll("SELECT *, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_tahun
        FROM `anak_tk`
        ORDER BY kelas ASC, nama_lengkap ASC") ?: [];
} catch (Throwable $e) { $lokal = []; }

$berkelas = [];
$tanpaKelas = 0;
$nA = 0; $nB = 0; $nLulus = 0;
foreach ($lokal as $b) {
    $ub = (int)($b['umur_tahun'] ?? 0);
    if ($ub < $MIN_TAHUN || $ub > $MAX_TAHUN) continue;
    $kls = trim((string)($b['kelas'] ?? ''));
    $lulus = ((int)($b['status_aktif'] ?? 1)) === 0;
    if ($kls === '') { $tanpaKelas++; continue; }
    if ($lulus) { $nLulus++; } elseif ($kls === 'TK A') { $nA++; } elseif ($kls === 'TK B') { $nB++; }
    $berkelas[] = $b;
}
$totalAktif = $nA + $nB;

$rows = [];
foreach ($berkelas as $b) {
    $kls = trim((string)($b['kelas'] ?? ''));
    $lulus = ((int)($b['status_aktif'] ?? 1)) === 0;
    if ($filterKelas === 'TK A' && ($kls !== 'TK A' || $lulus)) continue;
    if ($filterKelas === 'TK B' && ($kls !== 'TK B' || $lulus)) continue;
    if ($filterKelas === 'Lulus' && !$lulus) continue;
    $rows[] = $b;
}

$rowsTanpa = [];
if ($showAll) {
    foreach ($lokal as $b) {
        $ub = (int)($b['umur_tahun'] ?? 0);
        if ($ub < $MIN_TAHUN || $ub > $MAX_TAHUN) continue;
        if (trim((string)($b['kelas'] ?? '')) !== '') continue;
        $rowsTanpa[] = $b;
    }
}

function kelasBadge($kls) {
    $kls = trim((string)$kls);
    if ($kls === 'TK A') return '<span class="badge bg-primary">TK A</span>';
    if ($kls === 'TK B') return '<span class="badge bg-success">TK B</span>';
    return '<span class="badge bg-secondary">Belum ada kelas</span>';
}
$canInput = function_exists('canInput') ? canInput() : true;
$__app = APP_URL;
$__csrf = csrfToken();
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-shapes me-2 text-warning"></i>Anak TK (3-7 Tahun)</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= $__app ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Anak TK</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="alert alert-info border-0 shadow-sm">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div class="flex-grow-1">
        <strong><i class="fas fa-info-circle me-1"></i>Berbasis Kelas:</strong>
        sasaran usia <?= $MIN_TAHUN ?>–<?= $MAX_TAHUN ?> tahun (umur tidak ditampilkan).
        Anak muncul di daftar <strong>setelah guru memasukkan Kelas</strong> berdasarkan nama anak.
        Alur: <strong>TK A &rarr; TK B &rarr; Lulus</strong>.
      </div>
    </div>
  </div>
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-warning"><?= number_format($totalAktif) ?></div>
      <small class="text-muted">Aktif Berkelas</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-primary"><?= number_format($nA) ?></div>
      <small class="text-muted">Kelas TK A</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-success"><?= number_format($nB) ?></div>
      <small class="text-muted">Kelas TK B</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-secondary"><?= number_format($nLulus) ?></div>
      <small class="text-muted">Lulus</small>
    </div></div></div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white">
      <h5 class="mb-0"><i class="fas fa-list me-2 text-warning"></i>Daftar Anak TK</h5>
      <div class="d-flex gap-2 flex-wrap">
        <div class="btn-group btn-group-sm" role="group">
          <a href="index.php" class="btn <?= $filterKelas===''?'btn-warning':'btn-outline-warning' ?>">Semua</a>
          <a href="index.php?kelas=<?= urlencode('TK A') ?>" class="btn <?= $filterKelas==='TK A'?'btn-warning':'btn-outline-warning' ?>">TK A</a>
          <a href="index.php?kelas=<?= urlencode('TK B') ?>" class="btn <?= $filterKelas==='TK B'?'btn-warning':'btn-outline-warning' ?>">TK B</a>
          <a href="index.php?kelas=Lulus" class="btn <?= $filterKelas==='Lulus'?'btn-warning':'btn-outline-warning' ?>">Lulus</a>
        </div>
        <?php if ($canInput): ?>
        <a href="<?= $__app ?>/modules/anak_tk/tambah.php" class="btn btn-outline-warning btn-sm"><i class="fas fa-plus me-1"></i>Tambah Anak</a>
        <?php endif; ?>
      </div>
      <?php if ($canInput && !empty($rows)): ?>
      <div id="bulkBar" class="w-100 mt-2 p-2 border rounded bg-light d-none align-items-center gap-2 flex-wrap">
        <span class="small fw-semibold"><span id="bulkCount">0</span> dipilih</span>
        <?php if ($filterKelas === '' || $filterKelas === 'TK A'): ?>
        <button type="button" id="bulkNaikBtn" class="btn btn-success btn-sm"><i class="fas fa-arrow-up me-1"></i>Naik ke TK B (<span id="bulkNaikN">0</span>)</button>
        <?php endif; ?>
        <?php if ($filterKelas === '' || $filterKelas === 'TK B'): ?>
        <button type="button" id="bulkLulusBtn" class="btn btn-dark btn-sm"><i class="fas fa-graduation-cap me-1"></i>Luluskan (<span id="bulkLulusN">0</span>)</button>
        <?php endif; ?>
        <button type="button" id="bulkHapusBtn" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Hapus (<span id="bulkHapusN">0</span>)</button>
      </div>
      <?php endif; ?>
    </div>
    <div class="card-body"><div class="table-responsive">
      <table class="table table-hover datatable align-middle" style="width:100%">
        <thead class="table-light">
          <tr>
            <th style="width:36px"><input type="checkbox" id="checkAll" title="Centang semua"></th><th>No</th><th>Nama</th><th>L/P</th><th>Kelas</th><th>Sekolah / TK</th><th>NIK</th>
            <th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Belum ada anak<?= $filterKelas ? ' ('.htmlspecialchars($filterKelas).')' : '' ?>. Daftarkan lewat tombol <strong>Tambah Anak</strong> (cari nama, isi kelas).</td></tr>
        <?php else: foreach ($rows as $i => $r): $lulus = ((int)($r['status_aktif'] ?? 1)) === 0; ?>
          <tr>
            <td><input type="checkbox" class="row-check" value="<?= (int)$r['id'] ?>" data-kelas="<?= htmlspecialchars($r['kelas'] ?? '', ENT_QUOTES) ?>" data-lulus="<?= $lulus?1:0 ?>"></td>
            <td><?= $i+1 ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($r['nama_lengkap']) ?></div>
              <?php if (!empty($r['nomor_peserta'])): ?><small class="text-muted"><?= htmlspecialchars($r['nomor_peserta']) ?></small><?php endif; ?>
              <?php if (!empty($r['dusun'])): ?><br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($r['dusun']) ?></small><?php endif; ?>
            </td>
            <td><?php if (($r['jenis_kelamin']??'')==='L'): ?><span class="badge bg-info">L</span><?php elseif (($r['jenis_kelamin']??'')==='P'): ?><span class="badge" style="background:#e91e63">P</span><?php else: ?>-<?php endif; ?></td>
            <td><?= kelasBadge($r['kelas'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['sekolah'] ?: '-') ?></td>
            <td><code class="small"><?= htmlspecialchars($r['nik'] ?: '-') ?></code></td>
            <td><?php if ($lulus): ?><span class="badge bg-dark">Lulus</span><?php else: ?><span class="badge bg-success">Aktif</span><?php endif; ?></td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-sm btn-primary btn-menu-aksi" data-id="<?= (int)$r['id'] ?>" data-nama="<?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>" data-kelas="<?= htmlspecialchars($r['kelas'] ?? '', ENT_QUOTES) ?>" data-lulus="<?= $lulus?1:0 ?>"><i class="fas fa-bolt me-1"></i>Aksi</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <?php if ($showAll && !empty($rowsTanpa)): ?>
  <div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-user-clock me-2 text-secondary"></i>Belum Masuk Kelas (<?= count($rowsTanpa) ?>)</h5></div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle" style="width:100%">
        <thead class="table-light"><tr><th style="width:36px"><input type="checkbox" id="checkAll" title="Centang semua"></th><th>No</th><th>Nama</th><th>L/P</th><th>NIK</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($rowsTanpa as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><div class="fw-semibold"><?= htmlspecialchars($r['nama_lengkap']) ?></div></td>
            <td><?= ($r['jenis_kelamin'] ?? '') === 'L' ? 'L' : 'P' ?></td>
            <td><code class="small"><?= htmlspecialchars($r['nik'] ?: '-') ?></code></td>
            <td><button type="button" class="btn btn-sm btn-success btn-menu-aksi" data-id="<?= (int)$r['id'] ?>" data-nama="<?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>" data-kelas="" data-lulus="0"><i class="fas fa-door-open me-1"></i>Masukkan Kelas</button></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($tanpaKelas > 0 && !$showAll): ?>
  <div class="mt-2"><a href="index.php?tampil=semua" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye me-1"></i>Tampilkan <?= $tanpaKelas ?> anak yang belum masuk kelas</a></div>
  <?php endif; ?>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php // ---- Aksi: Naik Kelas / Lulus / Hapus ---- ?>
<script>
$(function(){
  var APP = "<?= APP_URL ?>";
  var CSRF = "<?= csrfToken() ?>";
  var TYPE = "anak_tk";
  function setKelas(id, aksi, kelas) {
    if (typeof showLoading==="function") showLoading("Menyimpan...");
    $.ajax({ url: APP+"/ajax/kelas_tk.php", type:"POST", dataType:"json",
      data: { id: id, aksi: aksi, kelas: kelas||"", csrf_token: CSRF }
    }).done(function(r){
      if (typeof hideLoading==="function") hideLoading();
      if (!r||!r.success) { Swal.fire("Gagal", (r&&r.message)||"Gagal menyimpan", "error"); return; }
      Swal.fire({icon:"success",title:"Berhasil",text:r.message||"Tersimpan",timer:1500,showConfirmButton:false}).then(function(){ location.reload(); });
    }).fail(function(){ if (typeof hideLoading==="function") hideLoading(); Swal.fire("Error","Koneksi bermasalah","error"); });
  }
  function esc(s){ return $("<div>").text(String(s||"")).html(); }
  function menuAksi(nama, id, kelas, lulus) {
    lulus = String(lulus)==="1";
    var naikBtn = "";
    if (!lulus && kelas==="TK A") naikBtn = '<button type="button" class="btn btn-success" data-aksi="naik"><i class="fas fa-arrow-up me-2"></i>Naik Kelas ke TK B</button>';
    if (!lulus && kelas==="TK B") naikBtn = '<button type="button" class="btn btn-dark" data-aksi="lulus"><i class="fas fa-graduation-cap me-2"></i>Luluskan</button>';
    var setBtns = "";
    var hapusUrl = APP + "/ajax/delete.php?type=" + TYPE + "&id=" + id;
    Swal.fire({
      title: nama || "Anak TK",
      html: '<div class="d-grid gap-2 text-start">' +

        naikBtn + setBtns +
        '<hr class="my-1"><button type="button" class="btn btn-outline-danger btn-hapus-mod" data-url="'+hapusUrl+'" data-nama="'+esc(nama)+'"><i class="fas fa-trash me-2"></i>Hapus</button></div>',
      showConfirmButton: false, showCloseButton: true, width: 400,
      didOpen: function(){
        var root = Swal.getHtmlContainer();
        root.querySelectorAll("[data-aksi]").forEach(function(b){
          b.addEventListener("click", function(){
            var a = this.getAttribute("data-aksi"), k = this.getAttribute("data-kelas")||"";
            if (a==="naik") Swal.fire({title:"Naik Kelas?", html:"<strong>"+esc(nama)+"</strong> naik dari TK A ke <strong>TK B</strong>?", icon:"question", showCancelButton:true, confirmButtonText:"Ya, naikkan", cancelButtonText:"Batal"}).then(function(res){ if(res.isConfirmed) setKelas(id,"naik",""); });
            else if (a==="lulus") Swal.fire({title:"Luluskan?", html:"<strong>"+esc(nama)+"</strong> lulus dari TK B?", icon:"question", showCancelButton:true, confirmButtonText:"Ya, luluskan", cancelButtonText:"Batal"}).then(function(res){ if(res.isConfirmed) setKelas(id,"lulus",""); });
            else setKelas(id,"set",k);
          });
        });
        var h = root.querySelector(".btn-hapus-mod");
        if (h) h.addEventListener("click", function(){
          var u=this.getAttribute("data-url"), n=this.getAttribute("data-nama")||"";
          Swal.close();
          if (typeof confirmDelete==="function") {
            confirmDelete(u,n);
          } else {
            Swal.fire({
              title: "Hapus data?",
              html: "Data <strong>"+esc(n)+"</strong> beserta riwayat pemeriksaannya akan dihapus dan tidak dapat dikembalikan.",
              icon: "warning",
              showCancelButton: true,
              confirmButtonText: "Ya, hapus",
              cancelButtonText: "Batal",
              confirmButtonColor: "#dc3545"
            }).then(function(res){
              if (!res.isConfirmed) return;
              $.ajax({
                url: u, type: "POST", dataType: "json",
                data: { csrf_token: CSRF }
              }).done(function(r){
                if (r && r.success) {
                  Swal.fire({icon:"success", title:"Berhasil", text:r.message||"Data berhasil dihapus", timer:1500, showConfirmButton:false})
                    .then(function(){ location.reload(); });
                } else {
                  Swal.fire("Gagal", (r&&r.message)||"Gagal menghapus data", "error");
                }
              }).fail(function(){
                Swal.fire("Error", "Koneksi bermasalah", "error");
              });
            });
          }
        });
      }
    });
  }
  var $tbl = $("table.datatable");
  function dtApi(){ try { if ($.fn.DataTable && $.fn.dataTable.isDataTable($tbl[0])) return $tbl.DataTable(); } catch(e){} return null; }
  function allChecks(){ var api = dtApi(); if (api) return $(api.rows().nodes()).find(".row-check"); return $(".row-check"); }
  function refreshBulk(){
    var sel = allChecks().filter(":checked");
    var n = sel.length;
    $("#bulkCount").text(n);
    $("#bulkHapusN").text(n);
    var nn = 0, nl = 0;
    sel.each(function(){ var k=$(this).data("kelas")||""; var l=String($(this).data("lulus"))==="1"; if(!l&&k==="TK A")nn++; if(!l&&k==="TK B")nl++; });
    $("#bulkNaikN").text(nn); $("#bulkLulusN").text(nl);
    var bar = $("#bulkBar");
    if (bar.length) { if(n>0){bar.removeClass("d-none").addClass("d-flex");} else {bar.addClass("d-none").removeClass("d-flex");} }
    var ca = $("#checkAll");
    if (ca.length){ var all = allChecks(); ca.prop("checked", all.length>0 && all.filter(":checked").length===all.length); }
  }
  $(document).on("change","#checkAll", function(){ var on=$(this).is(":checked"); allChecks().prop("checked",on); refreshBulk(); });
  $(document).on("change",".row-check", refreshBulk);
  function bulkSend(aksi, tanya, confirmTxt){
    var ids = allChecks().filter(":checked").map(function(){return $(this).val();}).get();
    if (!ids.length){ Swal.fire("Pilih dulu","Centang minimal satu anak.","warning"); return; }
    Swal.fire({title:tanya, html:confirmTxt+" Sebanyak <strong>"+ids.length+"</strong> anak.", icon:"question", showCancelButton:true, confirmButtonText:"Ya, lanjutkan", cancelButtonText:"Batal"}).then(function(res){
      if(!res.isConfirmed) return;
      if (typeof showLoading==="function") showLoading("Menyimpan...");
      $.ajax({ url: APP+"/ajax/kelas_tk.php", type:"POST", dataType:"json", traditional:true, data:{ aksi: aksi, ids: ids, csrf_token: CSRF } })
      .done(function(r){ if (typeof hideLoading==="function") hideLoading(); if(!r||!r.success){Swal.fire("Gagal",(r&&r.message)||"Gagal menyimpan","error");return;} Swal.fire({icon:"success",title:"Berhasil",text:r.message||"Tersimpan",timer:1800,showConfirmButton:false}).then(function(){location.reload();}); })
      .fail(function(){ if (typeof hideLoading==="function") hideLoading(); Swal.fire("Error","Koneksi bermasalah","error"); });
    });
  }
  $(document).on("click","#bulkNaikBtn", function(){ bulkSend("naik_bulk","Naikkan kelas?","Anak TK A terpilih akan <strong>naik ke TK B</strong>."); });
  $(document).on("click","#bulkLulusBtn", function(){ bulkSend("lulus_bulk","Luluskan?","Anak TK B terpilih akan <strong>lulus</strong> dan pindah ke daftar Lulus."); });
  $(document).on("click","#bulkHapusBtn", function(){ bulkSend("hapus_bulk","Hapus data?","Data terpilih beserta riwayat pemeriksaannya akan <strong>dihapus permanen</strong> (untuk koreksi salah input nama)."); });
  $(document).on("click",".btn-menu-aksi", function(e){
    e.preventDefault();
    menuAksi($(this).data("nama")||"Anak TK", $(this).data("id"), $(this).data("kelas")||"", $(this).data("lulus")||0);
  });
});
</script>

