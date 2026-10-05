/**
 * Pencarian balita (vanilla JS) — pola menu Bayi
 * Ketik → fetch → klik nama → terpilih
 */
(function (window) {
  'use strict';

  function esc(s) {
    if (s == null) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function daftarOpenSID(idOpensid, csrf, app, done) {
    var body = new URLSearchParams();
    body.set('id_penduduk_opensid', String(idOpensid));
    body.set('csrf_token', csrf || '');
    fetch((app || '') + '/ajax/daftar_balita_opensid.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: body.toString(),
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        var id = (res && res.id != null) ? res.id : (res && res.data && res.data.id);
        if (res && res.success && id) done(null, parseInt(id, 10), res);
        else done((res && res.message) || 'Gagal mendaftarkan dari OpenSID');
      })
      .catch(function () { done('Koneksi bermasalah'); });
  }

  window.initCariBalita = function (opts) {
    opts = opts || {};
    var inputSel = opts.input || '#cariBalita';
    var hasilSel = opts.hasil || '#hasilCariBalita';
    var hiddenSel = opts.hidden || '#balita_id';
    var infoSel = opts.info || '#infoBalita';

    var input = document.querySelector(inputSel);
    var hasil = document.querySelector(hasilSel);
    var hidden = document.querySelector(hiddenSel);
    var info = document.querySelector(infoSel);
    var csrf = opts.csrf || window.CSRF_TOKEN || '';
    var app = opts.app || window.APP_URL || '';
    var minLen = typeof opts.minLen === 'number' ? opts.minLen : 1;
    var timer = null;
    var lastQ = '';

    if (!input || !hasil) {
      console.warn('[cari-balita] elemen tidak ditemukan:', inputSel, !!input, hasilSel, !!hasil);
      return false;
    }

    // Hindari double-bind
    if (input.getAttribute('data-cari-balita') === '1') {
      console.info('[cari-balita] sudah terinisialisasi');
      return true;
    }
    input.setAttribute('data-cari-balita', '1');

    var wrap = input.closest('.position-relative') || input.parentElement;
    if (wrap) wrap.style.position = 'relative';
    hasil.classList.add('list-group', 'shadow-sm');
    hasil.style.maxHeight = '280px';
    hasil.style.overflow = 'auto';
    hasil.style.display = 'none';
    hasil.style.position = 'absolute';
    hasil.style.zIndex = '1050';
    hasil.style.width = '100%';
    hasil.style.left = '0';
    hasil.style.right = '0';
    hasil.style.background = '#fff';

    function tampilInfo(b) {
      if (!info) return;
      var jk = b.jenis_kelamin === 'L' ? 'Laki-laki' : (b.jenis_kelamin === 'P' ? 'Perempuan' : '-');
      info.classList.remove('d-none');
      info.innerHTML =
        '<div class="alert alert-info mt-2 mb-0">' +
        '<strong>' + esc(b.nama_lengkap || b.nama || '') + '</strong> | ' + jk +
        (b.umur ? ' | Umur: <strong>' + esc(b.umur) + '</strong>' : '') +
        (b.nama_ibu ? ' | Ibu: ' + esc(b.nama_ibu) : '') +
        (b.sumber === 'opensid'
          ? ' | <span class="badge bg-success">OpenSID</span>'
          : ' | <span class="badge bg-primary">Posyandu</span>') +
        '</div>';
    }

    function selesai(b, localId) {
      if (hidden) hidden.value = String(localId);
      input.value = b.nama_lengkap || b.nama || b.text || '';
      hasil.style.display = 'none';
      hasil.innerHTML = '';
      tampilInfo(b);
      if (typeof opts.onSelect === 'function') {
        try { opts.onSelect(b, localId); } catch (e) { console.error(e); }
      }
    }

    function pilih(b) {
      var raw = String(b.id);
      var idLokal = parseInt(b.id_lokal || 0, 10);
      if (/^\d+$/.test(raw) && parseInt(raw, 10) > 0) {
        selesai(b, parseInt(raw, 10));
        return;
      }
      if (idLokal > 0) {
        selesai(b, idLokal);
        return;
      }
      var idOs = b.id_opensid || (raw.indexOf('os:') === 0 ? parseInt(raw.replace('os:', ''), 10) : 0);
      if (!idOs) return;
      hasil.innerHTML = '<div class="list-group-item text-muted">Mendaftarkan dari OpenSID...</div>';
      hasil.style.display = 'block';
      daftarOpenSID(idOs, csrf, app, function (err, newId) {
        if (err) {
          if (window.Swal) Swal.fire('Gagal', err, 'error');
          else alert(err);
          hasil.style.display = 'none';
          return;
        }
        selesai(b, newId);
      });
    }

    function render(items, q) {
      if (!items || !items.length) {
        hasil.innerHTML = '<div class="list-group-item text-muted">Tidak ditemukan untuk "' + esc(q) + '"</div>';
        hasil.style.display = 'block';
        return;
      }
      hasil.innerHTML = items.map(function (b, i) {
        var badge = b.sumber === 'opensid'
          ? '<span class="badge bg-success ms-1">OpenSID</span>'
          : '<span class="badge bg-primary ms-1">Posyandu</span>';
        return (
          '<a href="#" class="list-group-item list-group-item-action py-2" data-idx="' + i + '">' +
          '<strong>' + esc(b.text || b.nama_lengkap || b.nama || '') + '</strong> ' + badge + '<br>' +
          '<small class="text-muted">' + esc(b.sub || '') + '</small></a>'
        );
      }).join('');
      hasil.style.display = 'block';
      // mousedown agar tidak kalah dengan blur/click-outside
      hasil.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('mousedown', function (e) {
          e.preventDefault();
          e.stopPropagation();
          var idx = parseInt(this.getAttribute('data-idx'), 10);
          pilih(items[idx]);
        });
      });
    }

    function cari(q) {
      lastQ = q;
      hasil.innerHTML = '<div class="list-group-item text-muted">Mencari...</div>';
      hasil.style.display = 'block';
      var url = (app || '') + '/ajax/cari_balita.php?q=' + encodeURIComponent(q) + '&limit=50';
      fetch(url, { credentials: 'same-origin' })
        .then(function (r) {
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return r.json();
        })
        .then(function (res) {
          if (q !== lastQ) return; // respons usang
          var items = (res && res.results) ? res.results : ((res && res.data) ? res.data : []);
          render(items, q);
        })
        .catch(function (err) {
          if (q !== lastQ) return;
          console.error('[cari-balita]', err);
          hasil.innerHTML = '<div class="list-group-item text-danger">Gagal memuat data</div>';
          hasil.style.display = 'block';
        });
    }

    function onType() {
      clearTimeout(timer);
      var q = input.value.trim();
      if (hidden) hidden.value = '';
      if (info) {
        info.classList.add('d-none');
        info.innerHTML = '';
      }
      if (q.length < minLen) {
        hasil.style.display = 'none';
        hasil.innerHTML = '';
        return;
      }
      timer = setTimeout(function () { cari(q); }, 180);
    }

    input.addEventListener('input', onType);
    input.addEventListener('keyup', onType);
    input.addEventListener('focus', function () {
      var q = input.value.trim();
      if (q.length >= minLen) cari(q);
    });

    document.addEventListener('click', function (e) {
      if (e.target === input || hasil.contains(e.target)) return;
      hasil.style.display = 'none';
    });

    console.info('[cari-balita] siap pada', inputSel, 'APP_URL=', app);
    return true;
  };

  /** Jalankan init saat DOM siap (aman meski skrip di footer) */
  window.bootCariBalita = function (opts) {
    function run() {
      window.initCariBalita(opts);
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', run);
    } else {
      run();
    }
  };
})(window);
