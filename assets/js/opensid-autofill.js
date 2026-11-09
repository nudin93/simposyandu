/* ============================================================
 * SIMPOSYANDU - OpenSID Autofill Helper (global)
 * ------------------------------------------------------------
 * Dipakai SEMUA form tambah yang punya pencarian penduduk
 * (balita, bayi, ibu_hamil, lansia, kb, remaja, usia_produktif,
 * anak_tk, pemeriksaan_*). Tidak mengubah tampilan apa pun.
 *
 * Masalah yang diperbaiki:
 *  1. tanggal_lahir dari OpenSID kadang berformat datetime
 *     ("YYYY-MM-DD HH:MM:SS"), DD/MM/YYYY, atau YYYYMMDD
 *     -> input type="date" menolaknya sehingga kosong/salah.
 *  2. Nama pekerjaan & pendidikan di OpenSID memakai kode
 *     baku (huruf kapital), mis. "MENGURUS RUMAH TANGGA",
 *     "PETANI/PEKEBUN", "TAMAT SD / SEDERAJAT", sedangkan
 *     pilihan (select) di form memakai label pendek
 *     ("Ibu Rumah Tangga", "Petani", "SD", ...).
 *     .val() langsung gagal -> kolom kosong / tidak cocok.
 *
 * Prinsip: data yang terisi HARUS COCOK dengan OpenSID.
 *  - Samakan dulu tanpa peduli kapital/spasi.
 *  - Kalau ada padanan jelas di tabel MAP -> pakai label form.
 *  - Kalau tidak ada padanan -> tambahkan nilai ASLI OpenSID
 *    sebagai option baru lalu pilih (select2-compatible),
 *    supaya yang tersimpan = persis data OpenSID.
 * ============================================================ */
(function (window) {
  'use strict';

  /** Normalisasi tanggal apa pun -> "YYYY-MM-DD" (atau '' bila invalid). */
  function normTgl(v) {
    if (v === null || v === undefined) return '';
    var s = String(v).trim();
    if (!s || s === '0000-00-00' || s === '0000-00-00 00:00:00') return '';
    var m;
    // YYYY-MM-DD... (termasuk datetime) -> ambil 10 karakter pertama
    m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) {
      var y = +m[1], mo = +m[2], d = +m[3];
      if (mo >= 1 && mo <= 12 && d >= 1 && d <= 31) return m[1] + '-' + m[2] + '-' + m[3];
      return '';
    }
    // DD/MM/YYYY atau DD-MM-YYYY
    m = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
    if (m) {
      var dd = String(m[1]).padStart(2, '0'), mm = String(m[2]).padStart(2, '0'), yy = m[3];
      return yy + '-' + mm + '-' + dd;
    }
    // YYYYMMDD
    m = s.match(/^(\d{4})(\d{2})(\d{2})$/);
    if (m) return m[1] + '-' + m[2] + '-' + m[3];
    try {
      var dt = new Date(s);
      if (!isNaN(dt.getTime())) {
        return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
      }
    } catch (e) {}
    return '';
  }

  function key(s) {
    return String(s === null || s === undefined ? '' : s).trim().toUpperCase().replace(/\s+/g, ' ');
  }

  /* ---- Padanan PEKERJAAN OpenSID (tweb_penduduk_pekerjaan) -> label form ---- */
  var PEKERJAAN_MAP = {
    'MENGURUS RUMAH TANGGA': 'Ibu Rumah Tangga',
    'IBU RUMAH TANGGA': 'Ibu Rumah Tangga',
    'PEGAWAI NEGERI SIPIL (PNS)': 'PNS',
    'PEGAWAI NEGERI SIPIL': 'PNS',
    'PNS': 'PNS',
    'TENTARA NASIONAL INDONESIA (TNI)': 'TNI/Polri',
    'TNI': 'TNI/Polri',
    'KEPOLISIAN RI (POLRI)': 'TNI/Polri',
    'POLRI': 'TNI/Polri',
    'TNI/POLRI': 'TNI/Polri',
    'PETANI/PEKEBUN': 'Petani',
    'PETANI': 'Petani',
    'NELAYAN/PERIKANAN': 'Nelayan',
    'NELAYAN': 'Nelayan',
    'PERDAGANGAN': 'Pedagang',
    'PEDAGANG': 'Pedagang',
    'KARYAWAN SWASTA': 'Karyawan Swasta',
    'WIRASWASTA': 'Wiraswasta',
    'WIRAUSAHA': 'Wiraswasta',
    'GURU': 'Guru',
    'DOKTER': 'Dokter/Tenaga Medis',
    'BIDAN': 'Dokter/Tenaga Medis',
    'PERAWAT': 'Dokter/Tenaga Medis',
    'APOTEKER': 'Dokter/Tenaga Medis',
    'TENAGA MEDIS': 'Dokter/Tenaga Medis',
    'DOKTER/TENAGA MEDIS': 'Dokter/Tenaga Medis',
    'LAINNYA': 'Lainnya',
    'LAIN-LAIN': 'Lainnya',
    'BELUM/TIDAK BEKERJA': 'Lainnya',
    'TIDAK BEKERJA': 'Lainnya',
    'PELAJAR/MAHASISWA': 'Lainnya',
    'PELAJAR': 'Lainnya',
    'MAHASISWA': 'Lainnya',
    'PENSIUNAN': 'Lainnya'
  };

  /* ---- Padanan PENDIDIKAN OpenSID (tweb_penduduk_pendidikan_kk) -> label form ---- */
  var PENDIDIKAN_MAP = {
    'TAMAT SD / SEDERAJAT': 'SD',
    'TAMAT SD/SEDERAJAT': 'SD',
    'SD': 'SD',
    'SD/SEDERAJAT': 'SD',
    'BELUM TAMAT SD/SEDERAJAT': 'SD',
    'SLTP/SEDERAJAT': 'SMP',
    'SLTP': 'SMP',
    'SMP': 'SMP',
    'SMP/SEDERAJAT': 'SMP',
    'SLTA / SEDERAJAT': 'SMA/SMK',
    'SLTA/SEDERAJAT': 'SMA/SMK',
    'SLTA': 'SMA/SMK',
    'SMA/SMK': 'SMA/SMK',
    'SMA': 'SMA/SMK',
    'SMK': 'SMA/SMK',
    'AKADEMI/ DIPLOMA III/S. MUDA': 'D3',
    'AKADEMI/DIPLOMA III/S.MUDA': 'D3',
    'DIPLOMA III': 'D3',
    'DIPLOMA I / II': 'D3',
    'D3': 'D3',
    'DIPLOMA IV/ STRATA I': 'S1',
    'DIPLOMA IV/STRATA I': 'S1',
    'STRATA I': 'S1',
    'S1': 'S1',
    'STRATA II': 'S2/S3',
    'STRATA III': 'S2/S3',
    'S2/S3': 'S2/S3',
    'S2': 'S2/S3',
    'S3': 'S2/S3'
  };

  /**
   * Isi <select> dari nilai OpenSID.
   * @param {jQuery|string|Element} sel  select target
   * @param {string} val  nilai asli dari OpenSID
   * @param {Object} map  tabel padanan (PEKERJAAN / PENDIDIKAN)
   * @returns {string} nilai akhir yang dipasang ('' bila kosong)
   */
  function setSelect(sel, val, map) {
    var raw = (val === null || val === undefined) ? '' : String(val).trim();
    if (!raw) return '';
    var $sel;
    if (window.jQuery && sel instanceof window.jQuery) $sel = sel;
    else if (window.jQuery) $sel = window.jQuery(sel);
    else $sel = null;
    var k = key(raw);

    // 1) Cocok persis (tak peduli kapital/spasi) pada value ATAU teks option
    var matched = '';
    var el = null;
    if ($sel && $sel.length) el = $sel[0];
    else if (sel && sel.tagName === 'SELECT') el = sel;
    if (el) {
      for (var i = 0; i < el.options.length; i++) {
        var op = el.options[i];
        if (key(op.value) === k || key(op.text) === k) { matched = op.value || op.text; break; }
      }
    }
    // 2) Padanan baku OpenSID -> label form
    if (!matched && map) {
      var label = map[k] || '';
      if (label && el) {
        for (var j = 0; j < el.options.length; j++) {
          var op2 = el.options[j];
          if (key(op2.value) === key(label) || key(op2.text) === key(label)) { matched = op2.value || op2.text; break; }
        }
        if (!matched) matched = label;
      } else if (label) {
        matched = label;
      }
    }
    // 3) Tidak ada padanan -> pakai NILAI ASLI OpenSID (tambah option bila perlu)
    if (!matched) matched = raw;

    if ($sel && $sel.length) {
      var exists = false;
      $sel.find('option').each(function () {
        if (key(this.value) === key(matched) || key($(this).text()) === key(matched)) { exists = true; return false; }
      });
      if (!exists) {
        var opt = new Option(matched, matched, false, false);
        $sel.append(opt);
      }
      $sel.val(matched);
      if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger('change');
      else $sel.trigger('change');
    } else if (el) {
      var ex2 = false;
      for (var n = 0; n < el.options.length; n++) {
        if (key(el.options[n].value) === key(matched) || key(el.options[n].text) === key(matched)) { ex2 = true; break; }
      }
      if (!ex2) el.add(new Option(matched, matched));
      el.value = matched;
      if ('dispatchEvent' in el) el.dispatchEvent(new Event('change', { bubbles: true }));
    }
    return matched;
  }

  function setPekerjaan(sel, val) { return setSelect(sel, val, PEKERJAAN_MAP); }
  function setPendidikan(sel, val) { return setSelect(sel, val, PENDIDIKAN_MAP); }

  window.OpenSID_normTgl = normTgl;
  window.OpenSID_setSelect = setSelect;
  window.OpenSID_setPekerjaan = setPekerjaan;
  window.OpenSID_setPendidikan = setPendidikan;
  window.OPENSID_PEKERJAAN_MAP = PEKERJAAN_MAP;
  window.OPENSID_PENDIDIKAN_MAP = PENDIDIKAN_MAP;
})(window);
