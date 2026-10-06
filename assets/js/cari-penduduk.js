/**
 * Autocomplete pencarian penduduk / keluarga - live by name
 * Harus dipanggil SETELAH jQuery dimuat
 */
function initCariPenduduk(opts) {
  if (typeof jQuery === 'undefined') {
    console.error('initCariPenduduk: jQuery belum dimuat');
    return;
  }
  opts = opts || {};
  var $ = jQuery;
  var inputSel = opts.input || '#cariPenduduk';
  var hasilSel = opts.hasil || '#hasilCari';
  var url = opts.url || '';
  var mode = opts.mode || 'penduduk';
  var jk = opts.jk || '';
  var onSelect = opts.onSelect || function(){};
  var minLen = opts.minLen != null ? opts.minLen : 1;
  var timer = null;

  var $input = $(inputSel);
  var $hasil = $(hasilSel);
  if (!$input.length) return;

  $input.parent().css('position', 'relative');
  $hasil.css({
    position: 'absolute',
    left: 0,
    right: 0,
    zIndex: 2000,
    maxHeight: '280px',
    overflowY: 'auto',
    background: '#fff'
  });

  $input.off('input.cari keyup.cari').on('input.cari keyup.cari', function() {
    clearTimeout(timer);
    var q = $(this).val().trim();
    if (q.length < minLen) {
      $hasil.hide().empty();
      return;
    }
    timer = setTimeout(function() {
      $.getJSON(url, { q: q, mode: mode, jk: jk })
        .done(function(res) {
          $hasil.empty();
          if (!res.data || !res.data.length) {
            $hasil.append('<div class="list-group-item text-muted small">Tidak ditemukan di OpenSID. Pastikan data warga sudah terdaftar di OpenSID. Untuk bayi tanpa NIK, daftarkan di modul Balita.</div>').show();
            return;
          }
          res.data.forEach(function(p) {
            var label, sub;
            if (mode === 'keluarga') {
              label = '<strong>' + esc(p.nama_kepala) + '</strong>';
              sub = 'KK: ' + esc(p.no_kk) + ' · ' + (p.jml || 0) + ' anggota · ' + esc(p.dusun || '');
            } else {
              label = '<strong>' + esc(p.nama) + '</strong> <code class="ms-1 small">' + esc(p.nik) + '</code>';
              sub = (p.umur != null ? p.umur + ' th · ' : '') + 'KK: ' + esc(p.no_kk || '-') + (p.dusun ? ' · ' + esc(p.dusun) : '');
            }
            var $a = $('<a href="#" class="list-group-item list-group-item-action py-2"></a>');
            $a.html(label + '<br><small class="text-muted">' + sub + '</small>');
            $a.on('click', function(e) {
              e.preventDefault();
              $input.val(mode === 'keluarga' ? (p.nama_kepala || '') : (p.nama || ''));
              $hasil.hide();
              onSelect(p);
            });
            $hasil.append($a);
          });
          $hasil.show();
        })
        .fail(function(xhr) {
          $hasil.html('<div class="list-group-item text-danger small">Gagal memuat (' + (xhr.status || '?') + '). Cek migrasi tabel / login.</div>').show();
        });
    }, 200);
  });

  $(document).off('click.cariPenduduk').on('click.cariPenduduk', function(e) {
    if (!$(e.target).closest(inputSel + ', ' + hasilSel).length) {
      $hasil.hide();
    }
  });

  function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
}
