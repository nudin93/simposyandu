/* SIMPOSYANDU - Custom JavaScript */

/* ===== Toggle submenu sidebar ===== */
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

$(document).ready(function() {
  initSelect2();
  initDatepicker();
  initDataTable();
  initDraftSave();
});

/* ===== Mobile sidebar: kunci scroll halaman, sidebar tetap scroll ===== */
(function() {
  var scrollY = 0;
  function isMobile() {
    return window.matchMedia('(max-width: 991.98px)').matches;
  }
  function lockBody() {
    if (!isMobile()) return;
    scrollY = window.scrollY || window.pageYOffset || 0;
    document.body.classList.add('sidebar-open');
    document.body.style.top = '-' + scrollY + 'px';
  }
  function unlockBody() {
    document.body.classList.remove('sidebar-open');
    document.body.style.top = '';
    window.scrollTo(0, scrollY || 0);
  }
  function syncSidebarState() {
    if (!isMobile()) {
      document.body.classList.remove('sidebar-open');
      document.body.style.top = '';
      return;
    }
    // AdminLTE: tanpa sidebar-collapse = terbuka di mobile setelah pushmenu
    var collapsed = document.body.classList.contains('sidebar-collapse');
    var open = document.body.classList.contains('sidebar-open');
    // Di mobile AdminLTE toggle class sidebar-open
    if (open) {
      // already locked via class; ensure top offset
      if (!document.body.style.top) {
        scrollY = window.scrollY || 0;
        document.body.style.top = '-' + scrollY + 'px';
      }
    }
  }
  $(document).on('click', '[data-widget="pushmenu"]', function() {
    setTimeout(function() {
      if (!isMobile()) return;
      if (document.body.classList.contains('sidebar-open')) {
        lockBody();
      } else {
        unlockBody();
      }
    }, 50);
  });
  // Klik overlay (pseudo) — pakai click di content saat open
  // PENTING: jangan tutup jika klik tombol garis-3 (toggleSidePush / pushmenu)
  $(document).on('click', function(e) {
    if (!isMobile() || !document.body.classList.contains('sidebar-open')) return;
    var side = document.querySelector('.main-sidebar');
    if (side && side.contains(e.target)) return;
    if ($(e.target).closest('[data-widget="pushmenu"]').length) return;
    if ($(e.target).closest('[onclick*="toggleSidePush"]').length) return;
    if ($(e.target).closest('#btn-toggle-sidebar').length) return;
    // tutup via AdminLTE API bila ada
    if (typeof $ !== 'undefined' && typeof $.fn.PushMenu !== 'undefined') {
      $('[data-widget="pushmenu"]').PushMenu('collapse');
    }
    document.body.classList.remove('sidebar-open');
    document.body.classList.add('sidebar-collapse');
    unlockBody();
  });
  // Cegah touch move di background saat sidebar open
  document.addEventListener('touchmove', function(e) {
    if (!isMobile() || !document.body.classList.contains('sidebar-open')) return;
    var side = document.querySelector('.main-sidebar');
    if (side && side.contains(e.target)) return; // izinkan scroll di sidebar
    e.preventDefault();
  }, { passive: false });
  $(window).on('resize', function() {
    if (!isMobile()) {
      unlockBody();
      document.body.classList.remove('sidebar-open');
      document.body.style.top = '';
    }
  });
  // Saat load di desktop pastikan tidak ada class overlay mobile
  if (!isMobile()) {
    document.body.classList.remove('sidebar-open');
    document.body.style.top = '';
  }
})();



function initSelect2() {
  if (typeof $.fn.select2 === 'undefined') return;
  $('select.select2').select2({
    theme: 'bootstrap-5',
    width: '100%',
    language: { noResults: function() { return 'Tidak ditemukan'; } }
  });
}

function initDatepicker() {
  if (typeof flatpickr === 'undefined') return;
  flatpickr('.datepicker', {
    locale: 'id',
    dateFormat: 'Y-m-d',
    allowInput: true,
    disableMobile: false
  });
  flatpickr('.datepicker-display', {
    locale: 'id',
    dateFormat: 'd/m/Y',
    altInput: true,
    altFormat: 'd F Y',
    allowInput: true
  });
}

function initDataTable() {
  if (typeof $.fn.DataTable === "undefined" || !$(".datatable").length) return;
  try {
    if ($.fn.dataTable && $.fn.dataTable.ext) {
      $.fn.dataTable.ext.errMode = "none";
    }
  } catch (e) {}
  $(".datatable").each(function() {
    var $t = $(this);
    if (!$t.find("thead").length) return;
    $t.find("tbody tr").each(function() {
      if ($(this).find("td[colspan]").length) $(this).remove();
    });
    var colCount = $t.find("thead tr:first th, thead tr:first td").length;
    if (!colCount) return;
    $t.find("tbody tr").each(function() {
      var $cells = $(this).children("td,th");
      while ($cells.length > colCount) {
        $cells.last().remove();
        $cells = $(this).children("td,th");
      }
      while ($cells.length < colCount) {
        $(this).append("<td></td>");
        $cells = $(this).children("td,th");
      }
    });
    if ($.fn.DataTable.isDataTable($t)) {
      try { $t.DataTable().clear().destroy(); } catch (e) {
        try { $t.DataTable().destroy(); } catch (e2) {}
      }
      $t.removeClass("dataTable no-footer");
      $t.find("thead th").removeClass("sorting sorting_asc sorting_desc");
    }
    try {
      $t.DataTable({
        responsive: false,
        autoWidth: false,
        retrieve: true,
        language: {
          emptyTable: "Tidak ada data",
          zeroRecords: "Data tidak ditemukan",
          search: "Cari:",
          lengthMenu: "Tampil _MENU_ data",
          info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
          infoEmpty: "Tidak ada data",
          infoFiltered: "(disaring dari _MAX_ data)",
          paginate: { first: "Awal", last: "Akhir", next: "›", previous: "‹" }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [],
        columnDefs: [
          { orderable: false, targets: -1 }
        ],
        dom: "<\"row\"<\"col-sm-12 col-md-6\"l><\"col-sm-12 col-md-6\"f>>" +
             "<\"row\"<\"col-sm-12\"tr>>" +
             "<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>"
      });
    } catch (err) {
      console.warn("DataTable init skipped:", err);
    }
  });
}

function initDraftSave() {
  var form = $('form[data-draft]');
  if (!form.length) return;
  var key = 'draft_' + window.location.pathname;
  var saved = localStorage.getItem(key);
  if (saved) {
    try {
      var data = JSON.parse(saved);
      $.each(data, function(name, val) {
        var el = form.find('[name="'+name+'"]');
        if (el.is(':checkbox') || el.is(':radio')) {
          el.filter('[value="'+val+'"]').prop('checked', true);
        } else {
          el.val(val).trigger('change');
        }
      });
    } catch(e) {}
  }
  setInterval(function() {
    var formData = {};
    form.find('input,select,textarea').each(function() {
      var name = $(this).attr('name');
      if (name && $(this).attr('type') !== 'file' && $(this).attr('type') !== 'hidden') {
        formData[name] = $(this).val();
      }
    });
    localStorage.setItem(key, JSON.stringify(formData));
  }, 30000);
  form.on('submit', function() { localStorage.removeItem(key); });
}

function showToast(message, type) {
  type = type || 'success';
  var colors = { success: '#198754', danger: '#dc3545', info: '#0dcaf0', warning: '#ffc107' };
  var icons = { success: 'fa-check-circle', danger: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-circle' };
  var id = 'toast_' + Date.now();
  var html = '<div id="'+id+'" class="toast align-items-center text-white border-0 show" style="background:'+(colors[type]||colors.success)+';border-radius:10px;min-width:250px">' +
    '<div class="d-flex"><div class="toast-body"><i class="fas '+(icons[type]||icons.success)+' me-2"></i>'+message+'</div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
  var container = $('#toast-container');
  if (!container.length) {
    $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:99999"></div>');
    container = $('#toast-container');
  }
  container.append(html);
  setTimeout(function() { $('#'+id).remove(); }, 4000);
}


/* ===== Notifikasi sukses/gagal seragam (SweetAlert) ===== */
function showSuccess(message, redirectUrl) {
  message = message || 'Data berhasil disimpan!';
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: message,
      confirmButtonColor: '#0d6efd',
      heightAuto: false,
      timer: redirectUrl ? 1800 : undefined,
      showConfirmButton: !redirectUrl,
      timerProgressBar: !!redirectUrl
    }).then(function() {
      if (redirectUrl) window.location.href = redirectUrl;
    });
  } else {
    alert(message);
    if (redirectUrl) window.location.href = redirectUrl;
  }
}

function showError(message) {
  message = message || 'Terjadi kesalahan';
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: 'error', title: 'Gagal!', text: message, confirmButtonColor: '#dc3545', heightAuto: false });
  } else {
    alert(message);
  }
}

/** Submit form via fetch + SweetAlert (untuk form AJAX) */
function ajaxSubmitForm(form, options) {
  options = options || {};
  var redirect = options.redirect || null;
  var btn = form.querySelector('[type=submit]');
  if (btn) { btn.disabled = true; }
  return fetch(form.action, { method: 'POST', body: new FormData(form) })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (btn) btn.disabled = false;
      if (res.success) {
        showSuccess(res.message || 'Data berhasil disimpan!', redirect || options.redirectOnSuccess);
      } else {
        showError(res.message || 'Gagal menyimpan data');
      }
      return res;
    })
    .catch(function() {
      if (btn) btn.disabled = false;
      showError('Koneksi gagal. Coba lagi.');
    });
}

/* ===== HAPUS DATA - Popup konfirmasi ===== */
function confirmDelete(url, nama) {
  nama = nama || 'ini';

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      title: 'Hapus Data?',
      html: 'Data <b>' + nama + '</b> akan dihapus permanen!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      heightAuto: false,
      customClass: { container: 'swal-on-top' }
    }).then(function(result) {
      if (result.isConfirmed) {
        doDelete(url);
      }
    });
  } else {
    if (window.confirm('Hapus data "' + nama + '"?\nData akan dihapus permanen.')) {
      doDelete(url);
    }
  }
}

function doDelete(url) {
  showLoading('Menghapus data...');
  // Ambil CSRF token dari berbagai sumber yang mungkin tersedia
  var token = (typeof CSRF !== 'undefined' && CSRF) ? CSRF :
              (typeof window.CSRF !== 'undefined' && window.CSRF) ? window.CSRF :
              (typeof window.CSRF_TOKEN !== 'undefined' && window.CSRF_TOKEN) ? window.CSRF_TOKEN :
              (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '') ||
              (document.querySelector('input[name="csrf_token"]') ? document.querySelector('input[name="csrf_token"]').value : '');
  $.ajax({
    url: url,
    type: 'POST',
    dataType: 'json',
    data: { _method: 'DELETE', csrf_token: token },
    success: function(res) {
      hideLoading();
      if (res && res.success) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message || 'Data berhasil dihapus',
            timer: 1500,
            showConfirmButton: false,
            heightAuto: false
          }).then(function() { location.reload(); });
        } else {
          alert(res.message || 'Data berhasil dihapus');
          location.reload();
        }
      } else {
        var msg = (res && res.message) ? res.message : 'Terjadi kesalahan';
        if (typeof Swal !== 'undefined') Swal.fire('Gagal!', msg, 'error');
        else alert('Gagal: ' + msg);
      }
    },
    error: function(xhr) {
      hideLoading();
      var msg = 'Koneksi bermasalah';
      try {
        var r = JSON.parse(xhr.responseText);
        if (r && r.message) msg = r.message;
      } catch(e) {}
      if (typeof Swal !== 'undefined') Swal.fire('Error!', msg, 'error');
      else alert('Error: ' + msg);
    }
  });
}

function showLoading(msg) {
  var ov = document.getElementById('loading-overlay');
  if (!ov) {
    ov = document.createElement('div');
    ov.id = 'loading-overlay';
    ov.className = 'loading-overlay';
    ov.innerHTML = '<div class="spinner-border spinner-border-lg text-white"></div><span></span>';
    document.body.appendChild(ov);
  }
  var span = ov.querySelector('span');
  if (span) span.textContent = msg || 'Memproses...';
  ov.classList.add('show');
}

function hideLoading() {
  var ov = document.getElementById('loading-overlay');
  if (ov) ov.classList.remove('show');
}

function hitungUmur(tglLahir, targetId) {
  if (!tglLahir) return '';
  var lahir = new Date(tglLahir);
  var now = new Date();
  var years = now.getFullYear() - lahir.getFullYear();
  var months = now.getMonth() - lahir.getMonth();
  var days = now.getDate() - lahir.getDate();
  if (days < 0) { months--; days += 30; }
  if (months < 0) { years--; months += 12; }
  var result = '';
  if (years > 0) result += years + ' Tahun ';
  if (months > 0) result += months + ' Bulan ';
  if (years === 0 && months === 0) result = days + ' Hari';
  if (targetId) $(targetId).val(result.trim());
  return result.trim();
}

function hitungHPL(hpht) {
  if (!hpht) return '';
  var d = new Date(hpht);
  d.setDate(d.getDate() + 280);
  return d.toISOString().split('T')[0];
}

function hitungUsiaKandungan(hpht, tglPeriksa) {
  if (!hpht) return 0;
  var d = new Date(hpht);
  var target = tglPeriksa ? new Date(tglPeriksa) : new Date();
  if (isNaN(d.getTime()) || isNaN(target.getTime())) return 0;
  var diff = Math.floor((target - d) / (1000 * 60 * 60 * 24));
  if (diff < 0) return 0;
  return Math.floor(diff / 7);
}

function hitungIMT(bb, tb) {
  if (!bb || !tb || tb <= 0) return 0;
  return (bb / ((tb/100) * (tb/100))).toFixed(2);
}

// NIK validation
$(document).on('input', 'input[name*=nik], input[name*=NIK]', function() {
  var val = $(this).val().replace(/\D/g,'');
  $(this).val(val);
  if (val.length > 0 && val.length !== 16) {
    $(this).addClass('is-invalid').removeClass('is-valid');
  } else if (val.length === 16) {
    $(this).addClass('is-valid').removeClass('is-invalid');
  }
});

// Form AJAX submit
$(document).on('submit', 'form.form-ajax', function(e) {
  e.preventDefault();
  var form = $(this);
  var url = form.attr('action') || window.location.href;
  showLoading('Menyimpan...');
  $.ajax({
    url: url,
    type: 'POST',
    data: new FormData(this),
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(res) {
      hideLoading();
      if (res && res.success) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message || 'Data tersimpan', timer: 2000, showConfirmButton: false, heightAuto: false })
            .then(function() { if (res.redirect) window.location.href = res.redirect; else location.reload(); });
        } else {
          alert(res.message || 'Data tersimpan');
          if (res.redirect) window.location.href = res.redirect; else location.reload();
        }
      } else {
        var msg = (res && res.message) ? res.message : 'Terjadi kesalahan';
        if (typeof Swal !== 'undefined') Swal.fire('Gagal!', msg, 'error');
        else alert('Gagal: ' + msg);
      }
    },
    error: function(xhr) {
      hideLoading();
      var msg = 'Koneksi bermasalah';
      try { var r = JSON.parse(xhr.responseText); if (r.message) msg = r.message; } catch(e) {}
      if (typeof Swal !== 'undefined') Swal.fire('Error!', msg, 'error');
      else alert('Error: ' + msg);
    }
  });
});
