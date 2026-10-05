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

$page_title = 'Detail Balita';
echo $this->include('layouts/header_asli');
$id = (int)($_GET['id'] ?? 0);
$b = fetchOne("SELECT * FROM balita WHERE id=$id");
if (!$b) { echo '<script>window.location="index.php";</script>'; exit; }
$umur = hitungUmur($b['tanggal_lahir']);
$pemeriksaan = fetchAll("SELECT pb.*, k.nama as nama_petugas FROM pemeriksaan_balita pb LEFT JOIN kader k ON pb.petugas_id=k.id WHERE pb.balita_id=$id ORDER BY pb.tanggal_pemeriksaan DESC");
$imunisasi = fetchAll("SELECT i.*, k.nama as nama_petugas FROM imunisasi i LEFT JOIN kader k ON i.petugas_id=k.id WHERE i.balita_id=$id ORDER BY i.tanggal_imunisasi DESC");
$vitamin = fetchAll("SELECT v.*, k.nama as nama_petugas FROM vitamin v LEFT JOIN kader k ON v.petugas_id=k.id WHERE v.balita_id=$id AND v.balita_id IS NOT NULL ORDER BY v.tanggal_pemberian DESC");
$chart_bb = []; $chart_tb = []; $chart_labels = [];
foreach (array_reverse($pemeriksaan) as $p) {
  $chart_labels[] = date('d/m/Y', strtotime($p['tanggal_pemeriksaan']));
  $chart_bb[] = $p['berat_badan']; $chart_tb[] = $p['tinggi_badan'];
}
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-baby me-2"></i>Detail Balita</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="index.php">Balita</a></li>
      <li class="breadcrumb-item active">Detail</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <!-- Profile Card -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-2 text-center mb-3 mb-md-0">
          <?php if ($b['foto_anak']): ?>
          <img src="<?= APP_URL ?>/uploads/balita/<?= $b['foto_anak'] ?>" class="img-fluid rounded-circle" style="width:100px;height:100px;object-fit:cover">
          <?php else: ?>
          <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width:100px;height:100px">
            <i class="fas fa-baby fa-2x text-white"></i>
          </div>
          <?php endif; ?>
        </div>
        <div class="col-md-7">
          <h3 class="fw-bold mb-1"><?= htmlspecialchars($b['nama_lengkap']) ?></h3>
          <p class="mb-1"><span class="badge bg-primary me-2"><?= $b['nomor_peserta'] ?></span>
          <span class="badge <?= $b['jenis_kelamin']=='L'?'bg-info':'bg-pink' ?>"><?= $b['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?></span></p>
          <p class="text-muted mb-0"><i class="fas fa-birthday-cake me-2"></i><?= formatTanggal($b['tanggal_lahir']) ?> &bull; <strong><?= $umur ?></strong></p>
          <p class="text-muted mb-0"><i class="fas fa-users me-2"></i>Orang tua: <?= htmlspecialchars($b['nama_ayah']??'-') ?> / <?= htmlspecialchars($b['nama_ibu']??'-') ?></p>
        </div>
        <div class="col-md-3 text-end">
          <a href="edit.php?id=<?= $id ?>" class="btn btn-warning mb-2 w-100"><i class="fas fa-edit me-2"></i>Edit Data</a>
          <a href="<?= APP_URL ?>/modules/pemeriksaan_balita/tambah.php?balita_id=<?= $id ?>" class="btn btn-success mb-2 w-100"><i class="fas fa-stethoscope me-2"></i>Tambah Pemeriksaan</a>
          <a href="<?= APP_URL ?>/modules/kartu/cetak.php?type=balita&id=<?= $id ?>" class="btn btn-secondary mb-2 w-100" target="_blank"><i class="fas fa-print me-2"></i>Cetak Kartu</a>
          <button type="button" class="btn btn-outline-danger w-100" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=balita&id=<?= (int)$id ?>', '<?= htmlspecialchars($b['nama_lengkap'] ?? 'Balita', ENT_QUOTES) ?>')"><i class="fas fa-trash me-2"></i>Hapus Data</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Status Gizi Terkini -->
  <?php if ($pemeriksaan): $last = $pemeriksaan[0]; ?>
  <div class="row mb-4">
    <div class="col-6 col-md-3"><div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-weight"></i></span><div class="info-box-content"><span class="info-box-text">Berat Badan</span><span class="info-box-number"><?= $last['berat_badan'] ?> kg</span></div></div></div>
    <div class="col-6 col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-ruler-vertical"></i></span><div class="info-box-content"><span class="info-box-text">Tinggi Badan</span><span class="info-box-number"><?= $last['tinggi_badan'] ?> cm</span></div></div></div>
    <div class="col-6 col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-circle"></i></span><div class="info-box-content"><span class="info-box-text">Lingkar Kepala</span><span class="info-box-number"><?= $last['lingkar_kepala'] ?: '-' ?> cm</span></div></div></div>
    <div class="col-6 col-md-3">
      <div class="info-box"><span class="info-box-icon <?= $last['status_gizi']=='Normal'?'bg-success':($last['status_gizi']=='Kurang'?'bg-warning':'bg-danger') ?>"><i class="fas fa-heartbeat"></i></span>
      <div class="info-box-content"><span class="info-box-text">Status Gizi</span><span class="info-box-number"><?= $last['status_gizi'] ?></span></div></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Tabs -->
  <div class="card"><div class="card-body p-0">
    <ul class="nav nav-tabs px-3 pt-3" id="detailTabs">
      <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-info">Info Lengkap</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-pmx">Pemeriksaan (<?= count($pemeriksaan) ?>)</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-imun">Imunisasi (<?= count($imunisasi) ?>)</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-vit">Vitamin (<?= count($vitamin) ?>)</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-grafik">Grafik</a></li>
    </ul>
    <div class="tab-content p-3">
      <div class="tab-pane active" id="tab-info">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-borderless table-sm"><tbody>
              <tr><th width="40%">No KK</th><td><?= $b['no_kk']??'-' ?></td></tr>
              <tr><th>NIK Anak</th><td><?= $b['nik_anak']??'-' ?></td></tr>
              <tr><th>Anak Ke-</th><td><?= $b['anak_ke'] ?></td></tr>
              <tr><th>Gol. Darah</th><td><?= $b['golongan_darah'] ?></td></tr>
              <tr><th>Berat Lahir</th><td><?= $b['berat_lahir'] ?> kg</td></tr>
              <tr><th>Tinggi Lahir</th><td><?= $b['tinggi_lahir'] ?> cm</td></tr>
              <tr><th>Status ASI</th><td><?= $b['status_asi'] ?></td></tr>
              <tr><th>Imunisasi</th><td><?= $b['status_imunisasi'] ?></td></tr>
              <tr><th>BPJS/KIS</th><td><?= $b['bpjs_kis']??'-' ?></td></tr>
            </tbody></table>
          </div>
          <div class="col-md-6">
            <table class="table table-borderless table-sm"><tbody>
              <tr><th width="40%">Alamat</th><td><?= htmlspecialchars($b['alamat_lengkap']??'-') ?></td></tr>
              <tr><th>Desa</th><td><?= $b['desa']??'-' ?></td></tr>
              <tr><th>RT/RW</th><td><?= $b['rt'].'/'.$b['rw'] ?></td></tr>
              <tr><th>Kecamatan</th><td><?= $b['kecamatan']??'-' ?></td></tr>
              <tr><th>Kabupaten</th><td><?= $b['kabupaten']??'-' ?></td></tr>
              <tr><th>No. HP</th><td><?= $b['no_hp']??'-' ?></td></tr>
              <tr><th>Alergi</th><td><?= htmlspecialchars($b['riwayat_alergi']??'-') ?></td></tr>
            </tbody></table>
          </div>
        </div>
      </div>
      <div class="tab-pane" id="tab-pmx">
        <div class="table-responsive"><table class="table table-sm table-hover">
          <thead><tr><th>Tanggal</th><th>BB</th><th>TB</th><th>LK</th><th>Status Gizi</th><th>Stunting</th><th>Petugas</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($pemeriksaan as $p): ?>
          <tr>
            <td><?= formatTanggal($p['tanggal_pemeriksaan']) ?></td>
            <td><?= $p['berat_badan'] ?> kg</td><td><?= $p['tinggi_badan'] ?> cm</td><td><?= $p['lingkar_kepala']??'-' ?> cm</td>
            <td><span class="badge <?= $p['status_gizi']=='Normal'?'bg-success':($p['status_gizi']=='Kurang'?'bg-warning':'bg-danger') ?>"><?= $p['status_gizi'] ?></span></td>
            <td><span class="badge <?= $p['risiko_stunting']=='Tidak'?'bg-success':($p['risiko_stunting']=='Risiko'?'bg-warning':'bg-danger') ?>"><?= $p['risiko_stunting'] ?></span></td>
            <td><?= htmlspecialchars($p['nama_petugas']??'-') ?></td>
            <td><a href="<?= APP_URL ?>/modules/pemeriksaan_balita/detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
      <div class="tab-pane" id="tab-imun">
        <div class="table-responsive"><table class="table table-sm table-hover">
          <thead><tr><th>Tanggal</th><th>Jenis</th><th>Dosis</th><th>Efek Samping</th><th>Petugas</th></tr></thead>
          <tbody>
          <?php foreach ($imunisasi as $im): ?>
          <tr><td><?= formatTanggal($im['tanggal_imunisasi']) ?></td><td><span class="badge bg-success"><?= $im['jenis_imunisasi'] ?></span></td><td><?= $im['dosis']??'-' ?></td><td><?= htmlspecialchars($im['efek_samping']??'-') ?></td><td><?= htmlspecialchars($im['nama_petugas']??'-') ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
      <div class="tab-pane" id="tab-vit">
        <div class="table-responsive"><table class="table table-sm table-hover">
          <thead><tr><th>Tanggal</th><th>Vitamin</th><th>Dosis</th><th>Petugas</th><th>Catatan</th></tr></thead>
          <tbody>
          <?php foreach ($vitamin as $v): ?>
          <tr><td><?= formatTanggal($v['tanggal_pemberian']) ?></td><td><?= htmlspecialchars($v['jenis_vitamin']) ?></td><td><?= $v['dosis']??'-' ?></td><td><?= htmlspecialchars($v['nama_petugas']??'-') ?></td><td><?= htmlspecialchars($v['catatan']??'-') ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
      <div class="tab-pane" id="tab-grafik">
        <div class="row"><div class="col-md-6"><canvas id="chartBB" height="200"></canvas></div><div class="col-md-6"><canvas id="chartTB" height="200"></canvas></div></div>
      </div>
    </div>
  </div></div>
</div></section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode($chart_labels) ?>;
const dataBB = <?= json_encode($chart_bb) ?>;
const dataTB = <?= json_encode($chart_tb) ?>;
new Chart(document.getElementById('chartBB').getContext('2d'), {type:'line',data:{labels,datasets:[{label:'Berat Badan (kg)',data:dataBB,borderColor:'#0d6efd',backgroundColor:'rgba(13,110,253,.1)',tension:0.4,fill:true}]},options:{responsive:true,plugins:{legend:{display:false},title:{display:true,text:'Grafik Berat Badan'}}}});
new Chart(document.getElementById('chartTB').getContext('2d'), {type:'line',data:{labels,datasets:[{label:'Tinggi Badan (cm)',data:dataTB,borderColor:'#198754',backgroundColor:'rgba(25,135,84,.1)',tension:0.4,fill:true}]},options:{responsive:true,plugins:{legend:{display:false},title:{display:true,text:'Grafik Tinggi Badan'}}}});
</script>
<?php echo $this->include('layouts/footer_asli'); ?>
