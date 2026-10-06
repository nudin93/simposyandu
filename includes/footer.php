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
<script>
/* Popup "Yang Baru" - tampil otomatis tiap versi baru (bandingkan localStorage vs APP_VERSION) */
(function(){
  var CUR = "<?= defined('APP_VERSION') ? APP_VERSION : '1.0.13' ?>";
  var KEY = "simposyandu_seen_ver";
  var URL = "<?= APP_URL ?>/ajax/whatsnew.php";
  function seen(){ try { return window.localStorage.getItem(KEY) || ""; } catch(e){ return ""; } }
  function save(v){ try { window.localStorage.setItem(KEY, v || CUR); } catch(e){} }
  function esc(s){ var d = document.createElement("div"); d.textContent = String(s == null ? "" : s); return d.innerHTML; }
  function htmlFor(notes){
    var h = '<div class="text-start">';
    notes.forEach(function(n){
      h += '<div class="border rounded p-2 mb-2"><div class="d-flex justify-content-between align-items-center gap-2"><strong>v' + esc(n.versi) + '</strong><small class="text-muted">' + esc(n.tanggal || "") + '</small></div>';
      if (n.judul) h += '<div class="fw-semibold mt-1">' + esc(n.judul) + '</div>';
      if (n.items && n.items.length) { h += '<ul class="mb-0 mt-1 ps-3 small">'; n.items.forEach(function(it){ h += '<li>' + esc(it) + '</li>'; }); h += '</ul>'; }
      h += '</div>';
    });
    return h + '</div>';
  }
  function markDot(){ var b = document.getElementById("wnDot"); if (b) b.style.display = (seen() === CUR) ? "none" : "inline"; }
  function show(notes){
    if (!window.Swal) { save(CUR); markDot(); return; }
    Swal.fire({ title: "Yang Baru di v" + CUR, html: htmlFor(notes), width: 560, confirmButtonText: "Mengerti", showCloseButton: true, footer: '<span class="small text-muted">Bisa dibuka lagi via tombol "Yang Baru"</span>' }).then(function(){ save(CUR); markDot(); });
  }
  window.showWhatsNew = function(){
    fetch(URL + "?seen=" + encodeURIComponent(seen() || "0"), { credentials: "same-origin" }).then(function(r){ return r.json(); }).then(function(j){
      if (j && j.success && (j.notes || []).length) { show(j.notes); if (j.version) save(j.version); }
      else if (window.Swal) { Swal.fire("Sudah terbaru", "Anda memakai versi " + CUR, "success"); save(CUR); }
      markDot();
    }).catch(function(){});
  };
  document.addEventListener("DOMContentLoaded", function(){
    markDot();
    if (seen() === CUR) return;
    fetch(URL + "?seen=" + encodeURIComponent(seen() || "0"), { credentials: "same-origin" }).then(function(r){ return r.json(); }).then(function(j){
      if (j && j.success && (j.notes || []).length) show(j.notes);
      else save(CUR);
      markDot();
    }).catch(function(){});
  });
})();
</script>

<script>
<script>
/* Lonceng pembaruan: klik -> modal TENGAH (SweetAlert), badge jumlah versi baru */
(function(){
  var CUR3 = "<?= defined('APP_VERSION') ? APP_VERSION : '1.0.13' ?>";
  var KEY3 = "simposyandu_seen_ver";
  var URL3 = "<?= APP_URL ?>/ajax/whatsnew.php";
  function seen3(){ try { return window.localStorage.getItem(KEY3) || ""; } catch(e){ return ""; } }
  function save3(v){ try { window.localStorage.setItem(KEY3, v || CUR3); } catch(e){} }
  function esc3(s){ var d = document.createElement("div"); d.textContent = String(s == null ? "" : s); return d.innerHTML; }
  function badge(n){
    var b = document.getElementById("bellBadge");
    if (!b) return;
    if (n > 0) { b.style.display = "inline-block"; b.textContent = n > 9 ? "9+" : n; }
    else b.style.display = "none";
    var d = document.getElementById("wnDot"); if (d) d.style.display = (seen3() === CUR3) ? "none" : "inline";
  }
  function loadNotes(cb){
    fetch(URL3 + "?seen=" + encodeURIComponent(seen3() || "0"), { credentials: "same-origin" })
      .then(function(r){ return r.json(); }).then(function(j){ cb(j && j.success ? j : null); }).catch(function(){ cb(null); });
  }
  function htmlLatest(n){
    var h = '<div class="text-start"><div class="fw-semibold mb-1">Tersedia <span class="badge bg-warning text-dark">v' + esc3(n.versi) + '</span> <small class="text-muted">' + esc3(n.tanggal || "") + '</small></div>';
    h += '<div class="border rounded p-2">';
    if (n.judul) h += '<div class="fw-semibold">' + esc3(n.judul) + '</div>';
    if (n.items && n.items.length) { h += '<ul class="mb-0 ps-3 small">'; n.items.forEach(function(it){ h += '<li>' + esc3(it) + '</li>'; }); h += '</ul>'; }
    return h + '</div></div>';
  }
  window.openBellModal = function(){
    loadNotes(function(j){
      if (!window.Swal) return;
      if (!j || !(j.notes || []).length) { Swal.fire("Sudah terbaru", "Anda memakai versi " + CUR3, "success"); if (j && j.version) save3(j.version); badge(0); return; }
      var n = j.notes[0];
      Swal.fire({
        title: "\uD83D\uDD14 Perbarui Sekarang ke v" + n.versi,
        html: htmlLatest(n) + ((j.notes.length > 1) ? '<div class="text-muted small mt-1">+ ' + (j.notes.length - 1) + ' versi lain, lihat di "Semua Perubahan".</div>' : ''),
        width: 560, showCloseButton: true, showDenyButton: true, confirmButtonText: "Perbarui Sekarang", denyButtonText: "Semua Perubahan", denyButtonColor: "#6c757d"
      }).then(function(r){
        if (r.isConfirmed) {
          Swal.fire({ title: "Cara Perbarui", html: '<div class="text-start small">1. Backup folder <strong>simposyandu</strong> (Compress di File Manager).<br>2. Upload ZIP paket timpa ke DALAM <strong>public_html/simposyandu/</strong>.<br>3. Extract &gt; Overwrite, pastikan tidak jadi folder ganda.<br>4. Buka index.php + <strong>Ctrl+Shift+R</strong>, lalu login ulang.</div>', icon: "info", confirmButtonText: "Mengerti", width: 520 });
        } else if (r.isDenied && window.showWhatsNew) { window.showWhatsNew(); }
        save3(j.version || CUR3); badge(0);
      });
    });
  };
  document.addEventListener("click", function(ev){
    var b = ev.target && ev.target.closest ? ev.target.closest("#bellBtn") : null;
    if (!b) return;
    ev.preventDefault();
    window.openBellModal();
  });
  document.addEventListener("DOMContentLoaded", function(){ loadNotes(function(j){ badge(j ? (j.notes || []).length : 0); }); });
  window.refreshBell = function(){ loadNotes(function(j){ badge(j ? (j.notes || []).length : 0); }); };
})();
</script>

<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
