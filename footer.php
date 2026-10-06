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

?>
</div><!-- /.content-wrapper -->
<footer class="main-footer text-center">
  <strong>Copyright &copy; <?= date('Y') ?> <a href="#"><?= $pengaturan['nama_posyandu'] ?? 'Posyandu' ?></a>.</strong>
</footer>
<aside class="control-sidebar control-sidebar-dark"></aside>
</div><!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE 3 -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<script>
/* Cegah AdminLTE Treeview bentrok dengan toggleMenu kustom */
(function() {
  try {
    var nav = document.querySelector('.nav-sidebar[data-widget="treeview"]');
    if (nav) nav.removeAttribute('data-widget');
    // Matikan plugin Treeview jika sudah ter-init
    if (window.jQuery && jQuery.fn.Treeview) {
      jQuery('.nav-sidebar').off('click.lte.treeview');
    }
  } catch (e) {}
})();
</script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.to/flatpickr/dist/l10n/id.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- QRCode.js -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<script>
/* Toggle sidebar ala OpenSID: desktop = mini + konten geser, mobile = dorong konten */
function toggleSidePush() {
  try {
    if (window.innerWidth >= 992) {
      document.body.classList.remove('sidebar-open');
      document.body.classList.toggle('sidebar-collapse');
    } else {
      var isOpen = document.body.classList.toggle('sidebar-open');
      if (isOpen) {
        document.body.classList.remove('sidebar-collapse');
        document.body.classList.remove('sidebar-closed');
      }
    }
  } catch (e) {}
  return false;
}
/* Naik ke desktop: tutup mode dorong mobile */
window.addEventListener('resize', function() {
  try {
    if (window.innerWidth >= 992) document.body.classList.remove('sidebar-open');
  } catch (e) {}
});
/* Klik konten saat mode dorong: tutup sidebar (mobile) */
document.addEventListener('click', function(ev) {
  try {
    if (window.innerWidth < 992 && document.body.classList.contains('sidebar-open')) {
      var sb = ev.target.closest ? ev.target.closest('.main-sidebar') : null;
      var btn = ev.target.closest ? ev.target.closest('[onclick*=\"toggleSidePush\"]') : null;
      if (!sb && !btn) document.body.classList.remove('sidebar-open');
    }
  } catch (e) {}
});
function toggleMenu(el) {
  try {
    if (el && el.preventDefault) { /* noop */ }
    var item = el.closest ? el.closest('li.nav-item') : el.parentElement;
    if (!item) return false;
    var submenu = item.querySelector(':scope > ul.nav-treeview') || item.querySelector('ul.nav-treeview');
    if (!submenu) return false;

    var isOpen = item.classList.contains('menu-open') || submenu.style.display === 'block';

    // Tutup submenu lain (accordion)
    document.querySelectorAll('.nav-sidebar > li.nav-item.menu-open').forEach(function(other) {
      if (other !== item) {
        other.classList.remove('menu-open');
        var s = other.querySelector(':scope > ul.nav-treeview') || other.querySelector('ul.nav-treeview');
        if (s) s.style.display = 'none';
      }
    });

    if (isOpen) {
      item.classList.remove('menu-open');
      submenu.style.display = 'none';
    } else {
      item.classList.add('menu-open');
      submenu.style.display = 'block';
    }
  } catch (e) {
    console.error('toggleMenu', e);
  }
  return false;
}
</script>

<script>
/* Sembunyikan parent submenu jika semua anaknya tidak tampil */
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.nav-sidebar > li.nav-item').forEach(function(item) {
    var sub = item.querySelector(':scope > ul.nav-treeview');
    if (!sub) return;
    var visibleChildren = sub.querySelectorAll(':scope > li.nav-item');
    // Jika tidak ada child li sama sekali (semua di-filter hak akses), sembunyikan parent
    if (visibleChildren.length === 0) {
      item.style.display = 'none';
    }
  });
});
</script>
<!-- Custom JS -->
<script src="<?= APP_URL ?>/assets/js/custom.js?v=20260928"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
