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

$pengaturan = $pengaturan ?? getPengaturan();
$current = basename($_SERVER['PHP_SELF']);
$folder = basename(dirname($_SERVER['PHP_SELF']));

$openData     = in_array($folder, ['penduduk','keluarga']);
$openBumil    = in_array($folder, ['ibu_hamil','pemeriksaan_ibu_hamil']);
$openBayi     = in_array($folder, ['bayi','pemeriksaan_bayi']);
$openBalita   = in_array($folder, ['balita','pemeriksaan_balita','imunisasi','vitamin']);
$openRemaja   = in_array($folder, ['remaja','pemeriksaan_remaja']);
$openTK       = in_array($folder, ['anak_tk','pemeriksaan_anak_tk']);
$openUP       = in_array($folder, ['usia_produktif','pemeriksaan_dewasa']);
$openLansia   = in_array($folder, ['lansia','pemeriksaan_lansia']);
$openKB       = ($folder === 'kb');
$openKegiatan = in_array($folder, ['kegiatan_posyandu','kunjungan_rumah','jadwal']);
$openSanitasi = in_array($folder, ['sanitasi','phbs']);
$openLaporan  = ($folder === 'laporan');
$openStat     = in_array($folder, ['statistik', 'analitik'], true);
$openArtikel  = ($folder === 'artikel');

$opensidOk = function_exists('opensid_available') ? opensid_available() : false;
$appName = htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU');
$u = function_exists('currentUser') ? currentUser() : [];
$isKD = function_exists('isKepalaDesa') && isKepalaDesa();
$canIn = function_exists('canInput') ? canInput() : true;
$canMg = function_exists('canManage') ? canManage() : (function_exists('isAdmin') && isAdmin());

function navActive($cond) { return $cond ? 'active' : ''; }
function menuOpen($cond) { return $cond ? 'menu-open' : ''; }

/** Cek hak lihat menu (admin selalu true) */
function cv($key) {
    if (!function_exists('canViewMenu')) return true;
    return canViewMenu($key);
}
?>
<?php
$__logo_url = '';
$__logo_file = trim($pengaturan['logo'] ?? '');
if ($__logo_file !== '' && file_exists(FCPATH . 'uploads/settings/' . $__logo_file)) {
    $__logo_url = APP_URL . '/uploads/settings/' . rawurlencode($__logo_file);
}
$__nama_pos = htmlspecialchars($pengaturan['nama_posyandu'] ?? 'Posyandu');
$__u_nama = htmlspecialchars($u['nama'] ?? ($user['nama'] ?? 'Kader'));
$__u_role = htmlspecialchars(ucwords(str_replace('_', ' ', $u['role'] ?? ($user['role'] ?? ''))));
$__u_foto = $u['foto'] ?? ($user['foto'] ?? '');
$__avatar_svg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='32' fill='%23ccfbf1'/><circle cx='32' cy='24' r='11' fill='%230f766e'/><path d='M10 56c4-12 12-17 22-17s18 5 22 17' fill='%230f766e'/></svg>";
$__u_foto_url = $__u_foto ? APP_URL . '/uploads/kader/' . rawurlencode($__u_foto) : $__avatar_svg;
?>
<aside class="main-sidebar sidebar-light-primary elevation-1 opensid-sidebar">
  <a href="<?= APP_URL ?>/dashboard.php" class="brand-link">
    <?php if ($__logo_url): ?>
      <img src="<?= $__logo_url ?>" alt="Logo" class="brand-image">
    <?php else: ?>
      <span class="brand-icon"><i class="fas fa-clinic-medical"></i></span>
    <?php endif; ?>
    <span class="brand-text"><strong><?= $appName ?></strong><small><?= $__nama_pos ?></small></span>
  </a>
  <div class="sidebar">
    <div class="user-panel d-flex align-items-center">
      <div class="image">
        <img src="<?= htmlspecialchars($__u_foto_url) ?>" alt="Foto" onerror="this.onerror=null;this.src='<?= $__avatar_svg ?>'">
        <span class="online-dot <?= $opensidOk ? 'on' : 'off' ?>" title="<?= $opensidOk ? 'OpenSID terhubung' : 'OpenSID terputus' ?>"></span>
      </div>
      <div class="info">
        <a href="<?= APP_URL ?>/modules/kader/profile.php" class="d-block"><?= $__u_nama ?></a>
        <small><?= $__u_role ?></small>
      </div>
    </div>
    <div class="sidebar-search">
      <div class="input-group">
        <input type="text" id="sideSearchInput" class="form-control form-control-sm" placeholder="Cari menu..." onkeyup="filterSideMenu(this.value)">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
      </div>
    </div>
    <nav class="mt-1">
      <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" role="menu" data-accordion="false">

        <!-- UTAMA -->
        <li class="nav-header">UTAMA</li>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/dashboard.php" class="nav-link <?= navActive($current==='dashboard.php') ?>">
            <i class="nav-icon fas fa-home"></i>
            <p>Beranda</p>
          </a>
        </li>
        <?php if (in_array(($u['role'] ?? ''), ['kader','admin'], true)): ?>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/kader_home.php" class="nav-link">
            <i class="nav-icon fas fa-mobile-alt"></i>
            <p>Beranda Kader</p>
          </a>
        </li>
        <?php endif; ?>
        <?php if (cv('statistik')): ?>
        <li class="nav-item <?= menuOpen($openStat) ?>">
          <a href="#" class="nav-link <?= navActive($openStat) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-chart-pie"></i>
            <p>Statistik & Analitik <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openStat ? 'display:block' : 'display:none' ?>">
            <li class="nav-item">
              <a href="<?= APP_URL ?>/modules/statistik/index.php" class="nav-link <?= navActive($folder==='statistik') ?>">
                <i class="nav-icon far fa-circle"></i><p>Statistik Posyandu</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= APP_URL ?>/modules/analitik/prediktif.php" class="nav-link <?= navActive($folder==='analitik') ?>">
                <i class="nav-icon far fa-circle"></i><p>Analitik Prediktif PTM</p>
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

        <!-- DATA WARGA (OpenSID) -->
        <?php if (cv('penduduk') || cv('keluarga')): ?>
        <li class="nav-header">DATA WARGA</li>
        <li class="nav-item <?= menuOpen($openData) ?>">
          <a href="#" class="nav-link <?= navActive($openData) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-users"></i>
            <p>Kependudukan <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openData ? 'display:block' : 'display:none' ?>">
            <?php if (cv('penduduk')): ?><li class="nav-item">
              <a href="<?= APP_URL ?>/modules/penduduk/index.php" class="nav-link <?= navActive($folder==='penduduk') ?>">
                <i class="nav-icon far fa-circle"></i>
                <p>Penduduk</p>
              </a>
            </li><?php endif; ?>
            <?php if (cv('keluarga')): ?><li class="nav-item">
              <a href="<?= APP_URL ?>/modules/keluarga/index.php" class="nav-link <?= navActive($folder==='keluarga') ?>">
                <i class="nav-icon far fa-circle"></i>
                <p>Keluarga</p>
              </a>
            </li><?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- SIKLUS HIDUP ILP -->
        <li class="nav-header">SIKLUS HIDUP (ILP)</li>

        <li class="nav-item <?= menuOpen($openBumil) ?>">
          <a href="#" class="nav-link <?= navActive($openBumil) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-female"></i>
            <p>Ibu Hamil <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openBumil ? 'display:block' : 'display:none' ?>">
            <?php if (cv('ibu_hamil')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/ibu_hamil/index.php" class="nav-link <?= navActive($folder==='ibu_hamil') ?>"><i class="nav-icon far fa-circle"></i><p>Data Ibu Hamil</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_ibu_hamil')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_ibu_hamil/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_ibu_hamil') ?>"><i class="nav-icon far fa-circle"></i><p>Pemeriksaan ANC</p></a></li><?php endif; ?>
          </ul>
        </li>

        <li class="nav-item <?= menuOpen($openBayi) ?>">
          <a href="#" class="nav-link <?= navActive($openBayi) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-baby"></i>
            <p>Bayi (0–12 bln) <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openBayi ? 'display:block' : 'display:none' ?>">
            <?php if (cv('bayi')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/bayi/index.php" class="nav-link <?= navActive($folder==='bayi') ?>"><i class="nav-icon far fa-circle"></i><p>Data Bayi</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_bayi')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_bayi/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_bayi') ?>"><i class="nav-icon far fa-circle"></i><p>Pemeriksaan Bayi</p></a></li><?php endif; ?>
          </ul>
        </li>

        <li class="nav-item <?= menuOpen($openBalita) ?>">
          <a href="#" class="nav-link <?= navActive($openBalita) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-child"></i>
            <p>Balita <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openBalita ? 'display:block' : 'display:none' ?>">
            <?php if (cv('balita')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/balita/index.php" class="nav-link <?= navActive($folder==='balita') ?>"><i class="nav-icon far fa-circle"></i><p>Data Balita</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_balita')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_balita/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_balita') ?>"><i class="nav-icon far fa-circle"></i><p>Pemeriksaan Gizi</p></a></li><?php endif; ?>
            <?php if (cv('imunisasi')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/imunisasi/index.php" class="nav-link <?= navActive($folder==='imunisasi') ?>"><i class="nav-icon far fa-circle"></i><p>Imunisasi</p></a></li><?php endif; ?>
            <?php if (cv('vitamin')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/vitamin/index.php" class="nav-link <?= navActive($folder==='vitamin') ?>"><i class="nav-icon far fa-circle"></i><p>Vitamin A</p></a></li><?php endif; ?>
          </ul>
        </li>

        <li class="nav-item <?= menuOpen($openTK) ?>">
          <a href="#" class="nav-link <?= navActive($openTK) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-shapes"></i>
            <p>Anak TK (3-7 thn) <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openTK ? 'display:block' : 'display:none' ?>">
            <?php if (cv('anak_tk')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/anak_tk/index.php" class="nav-link <?= navActive($folder==='anak_tk') ?>"><i class="nav-icon far fa-circle"></i><p>Data Anak TK</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_anak_tk')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_anak_tk/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_anak_tk') ?>"><i class="nav-icon far fa-circle"></i><p>Pemeriksaan</p></a></li><?php endif; ?>
          </ul>
        </li>

        <li class="nav-item <?= menuOpen($openRemaja) ?>">
          <a href="#" class="nav-link <?= navActive($openRemaja) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Anak Sekolah & Remaja <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openRemaja ? 'display:block' : 'display:none' ?>">
            <?php if (cv('remaja')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/remaja/index.php" class="nav-link <?= navActive($folder==='remaja') ?>"><i class="nav-icon far fa-circle"></i><p>Data Remaja</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_remaja')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_remaja/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_remaja') ?>"><i class="nav-icon far fa-circle"></i><p>Pemeriksaan</p></a></li><?php endif; ?>
          </ul>
        </li>

        <li class="nav-item <?= menuOpen($openUP) ?>">
          <a href="#" class="nav-link <?= navActive($openUP) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-user-tie"></i>
            <p>Usia Produktif <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openUP ? 'display:block' : 'display:none' ?>">
            <?php if (cv('usia_produktif')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/usia_produktif/index.php" class="nav-link <?= navActive($folder==='usia_produktif') ?>"><i class="nav-icon far fa-circle"></i><p>Data Usia Produktif</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_dewasa')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_dewasa') ?>"><i class="nav-icon far fa-circle"></i><p>Skrining PTM</p></a></li><?php endif; ?>
          </ul>
        </li>

        <li class="nav-item <?= menuOpen($openLansia) ?>">
          <a href="#" class="nav-link <?= navActive($openLansia) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-blind"></i>
            <p>Lansia <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openLansia ? 'display:block' : 'display:none' ?>">
            <?php if (cv('lansia')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/lansia/index.php" class="nav-link <?= navActive($folder==='lansia') ?>"><i class="nav-icon far fa-circle"></i><p>Data Lansia</p></a></li><?php endif; ?>
            <?php if (cv('pemeriksaan_lansia')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_lansia/index.php" class="nav-link <?= navActive($folder==='pemeriksaan_lansia') ?>"><i class="nav-icon far fa-circle"></i><p>Pemeriksaan Lansia</p></a></li><?php endif; ?>
          </ul>
        </li>

        <?php if (cv('kb')): ?><li class="nav-item">
          <a href="<?= APP_URL ?>/modules/kb/index.php" class="nav-link <?= navActive($openKB) ?>">
            <i class="nav-icon fas fa-pills"></i>
            <p>Keluarga Berencana</p>
          </a>
        </li><?php endif; ?>

        <!-- KEGIATAN -->
        <li class="nav-header">KEGIATAN POSYANDU</li>
        <li class="nav-item <?= menuOpen($openKegiatan) ?>">
          <a href="#" class="nav-link <?= navActive($openKegiatan) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>Kegiatan <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openKegiatan ? 'display:block' : 'display:none' ?>">
            <?php if (cv('kegiatan_posyandu')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/kegiatan_posyandu/index.php" class="nav-link <?= navActive($folder==='kegiatan_posyandu') ?>"><i class="nav-icon far fa-circle"></i><p>Kegiatan Posyandu</p></a></li><?php endif; ?>
            <?php if (cv('kunjungan_rumah')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/kunjungan_rumah/index.php" class="nav-link <?= navActive($folder==='kunjungan_rumah') ?>"><i class="nav-icon far fa-circle"></i><p>Kunjungan Rumah</p></a></li><?php endif; ?>
            <?php if (cv('jadwal')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/jadwal/index.php" class="nav-link <?= navActive($folder==='jadwal') ?>"><i class="nav-icon far fa-circle"></i><p>Jadwal Posyandu</p></a></li><?php endif; ?>
          </ul>
        </li>

        <!-- ARTIKEL -->
        <li class="nav-header">ARTIKEL</li>
        <li class="nav-item <?= menuOpen($openArtikel) ?>">
          <a href="#" class="nav-link <?= navActive($openArtikel) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-newspaper"></i>
            <p>Artikel <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openArtikel ? 'display:block' : 'display:none' ?>">
            <li class="nav-item">
              <a href="<?= APP_URL ?>/modules/artikel/index.php" class="nav-link <?= navActive($folder==='artikel' && $current==='index.php') ?>">
                <i class="nav-icon far fa-circle"></i><p>Semua Artikel</p>
              </a>
            </li>
            <?php if ($canIn): ?>
            <li class="nav-item">
              <a href="<?= APP_URL ?>/modules/artikel/tambah.php" class="nav-link <?= navActive($folder==='artikel' && $current==='tambah.php') ?>">
                <i class="nav-icon far fa-circle"></i><p>Tambah Artikel</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($canMg): ?>
            <li class="nav-item">
              <a href="<?= APP_URL ?>/modules/artikel/persetujuan.php" class="nav-link <?= navActive($folder==='artikel' && $current==='persetujuan.php') ?>">
                <i class="nav-icon far fa-circle"></i><p>Persetujuan Artikel</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>

        <!-- LINGKUNGAN -->
        <li class="nav-header">KESEHATAN LINGKUNGAN</li>
        <li class="nav-item <?= menuOpen($openSanitasi) ?>">
          <a href="#" class="nav-link <?= navActive($openSanitasi) ?>" onclick="return toggleMenu(this)">
            <i class="nav-icon fas fa-hand-holding-water"></i>
            <p>Sanitasi & PHBS <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview" style="<?= $openSanitasi ? 'display:block' : 'display:none' ?>">
            <?php if (cv('sanitasi')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/sanitasi/index.php" class="nav-link <?= navActive($folder==='sanitasi') ?>"><i class="nav-icon far fa-circle"></i><p>Sanitasi</p></a></li><?php endif; ?>
            <?php if (cv('phbs')): ?><li class="nav-item"><a href="<?= APP_URL ?>/modules/phbs/index.php" class="nav-link <?= navActive($folder==='phbs') ?>"><i class="nav-icon far fa-circle"></i><p>PHBS</p></a></li><?php endif; ?>
          </ul>
        </li>

        <!-- LAPORAN -->
        <?php if (cv('laporan') || cv('kartu')): ?>
        <li class="nav-header">LAPORAN</li>
        <?php if (cv('laporan')): ?>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/modules/laporan/index.php" class="nav-link <?= navActive($openLaporan) ?>">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Laporan ILP</p>
          </a>
        </li>
        <?php endif; ?>
        <?php if (!$isKD && cv('kartu')): ?>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/modules/kartu/index.php" class="nav-link <?= navActive($folder==='kartu') ?>">
            <i class="nav-icon fas fa-id-card"></i>
            <p>Cetak Kartu</p>
          </a>
        </li>
        <?php endif; ?>
        <?php endif; /* end laporan group */ ?>

        <!-- PENGATURAN -->
        <?php if ($canMg || cv('kader') || cv('backup') || cv('pengaturan')): ?>
        <li class="nav-header">PENGATURAN</li>
        <?php if ($canMg || cv('kader')): ?>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/modules/kader/index.php" class="nav-link <?= navActive($folder==='kader') ?>">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Pengguna / Kader</p>
          </a>
        </li>
        <?php endif; ?>
        <?php if ($canMg || cv('backup')): ?>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/modules/backup/index.php" class="nav-link <?= navActive($folder==='backup') ?>">
            <i class="nav-icon fas fa-database"></i>
            <p>Backup Database</p>
          </a>
        </li>
        <?php endif; ?>
        <?php if ($canMg || cv('pengaturan')): ?>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/modules/pengaturan/index.php" class="nav-link <?= navActive($folder==='pengaturan') ?>">
            <i class="nav-icon fas fa-cog"></i>
            <p>Pengaturan Aplikasi</p>
          </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>

        <li class="nav-header">STATUS</li>
        <li class="nav-item">
          <span class="nav-link" style="cursor:default;">
            <?php if ($opensidOk): ?>
            <i class="nav-icon fas fa-link text-success"></i>
            <p class="text-success" style="font-size:0.8rem;">OpenSID terhubung</p>
            <?php else: ?>
            <i class="nav-icon fas fa-unlink text-danger"></i>
            <p class="text-danger" style="font-size:0.8rem;">OpenSID terputus</p>
            <?php endif; ?>
          </span>
        </li>
        <li class="nav-item">
          <a href="<?= APP_URL ?>/logout.php" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Keluar</p>
          </a>
        </li>

        <li class="nav-item sidebar-bottom-spacer" aria-hidden="true" style="height:80px;pointer-events:none;"></li>
      </ul>
    </nav>
    <div class="sidebar-foot">
      <small>SIMPOSYANDU · ILP · v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.12' ?></small>
      <button type="button" onclick="if(window.showWhatsNew)window.showWhatsNew();return false;" class="btn btn-warning btn-sm py-0 px-2 mt-1" style="font-size:.7rem;line-height:1.6;">Yang Baru <span id="wnDot" style="display:none;">&#9679;</span></button>
      <small class="<?= $opensidOk ? 'text-success' : 'text-danger' ?>"><?= $opensidOk ? '● OpenSID' : '○ OpenSID' ?></small>
    </div>
  </div>
</aside>
<script>
function filterSideMenu(q) {
  q = (q || '').toLowerCase().trim();
  document.querySelectorAll('.nav-sidebar .nav-treeview > li.nav-item').forEach(function(li){
    li.style.display = '';
  });
  if (!q) return;
  document.querySelectorAll('.nav-sidebar li.nav-item').forEach(function(li){
    var link = li.querySelector(':scope > a.nav-link p, :scope > a.nav-link');
    var sub = li.querySelector(':scope > ul.nav-treeview');
    if (sub) {
      var anyVisible = false;
      sub.querySelectorAll(':scope > li.nav-item').forEach(function(child){
        var t = (child.textContent || '').toLowerCase();
        var show = t.indexOf(q) !== -1;
        child.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
      });
      li.style.display = anyVisible ? '' : 'none';
      if (anyVisible) { li.classList.add('menu-open'); sub.style.display = 'block'; }
    } else if (link) {
      var t = (li.textContent || '').toLowerCase();
      if (li.classList.contains('sidebar-bottom-spacer')) return;
      li.style.display = t.indexOf(q) !== -1 ? '' : 'none';
    }
  });
}
</script>
