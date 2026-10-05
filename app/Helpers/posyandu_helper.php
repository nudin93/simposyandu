<?php
/**
 * SIMPOSYANDU CI4 - fungsi murni port dari config/database.php lama.
 * Tidak ada global $conn di sini; query lewat Model.
 */

if (! function_exists('mapSex')) {
    function mapSex($sex) {
        if ($sex == 1 || $sex === '1' || strtoupper((string) $sex) === 'L') return 'L';
        if ($sex == 2 || $sex === '2' || strtoupper((string) $sex) === 'P') return 'P';
        return '';
    }
}

if (! function_exists('mapHubungan')) {
    function mapHubungan($kk_level) {
        $map = [1=>'Kepala Keluarga',2=>'Suami',3=>'Istri',4=>'Anak',5=>'Menantu',6=>'Cucu',7=>'Orang Tua',8=>'Mertua',9=>'Famili Lain',10=>'Pembantu',11=>'Lainnya'];
        return $map[(int) $kk_level] ?? 'Lainnya';
    }
}

if (! function_exists('mapKawin')) {
    function mapKawin($status) {
        $map = [1=>'BELUM KAWIN',2=>'KAWIN',3=>'CERAI HIDUP',4=>'CERAI MATI'];
        return $map[(int) $status] ?? '-';
    }
}

if (! function_exists('formatUmurBalita')) {
    function formatUmurBalita($tanggal_lahir, $umur_bulan = null) {
        if ($umur_bulan === null && $tanggal_lahir) {
            try {
                $d1 = new DateTime($tanggal_lahir);
                $d2 = new DateTime();
                $diff = $d1->diff($d2);
                $umur_bulan = $diff->y * 12 + $diff->m;
            } catch (Exception $e) { return '-'; }
        }
        $umur_bulan = (int) $umur_bulan;
        if ($umur_bulan < 0) return '-';
        if ($umur_bulan < 12) return $umur_bulan . ' bulan';
        $th = intdiv($umur_bulan, 12);
        $bl = $umur_bulan % 12;
        if ($bl === 0) return $th . ' tahun';
        return $th . ' th ' . $bl . ' bln';
    }
}

if (! function_exists('hitungIMT')) {
    function hitungIMT($bb, $tb) {
        if (! $bb || ! $tb || $tb <= 0) return null;
        return round($bb / (($tb / 100) * ($tb / 100)), 2);
    }
}

if (! function_exists('statusIMTDewasa')) {
    function statusIMTDewasa($imt) {
        if ($imt === null) return 'Tidak Diketahui';
        if ($imt < 18.5) return 'Kurus';
        if ($imt < 25) return 'Normal';
        if ($imt < 27) return 'Gemuk';
        return 'Obesitas';
    }
}

if (! function_exists('statusAnemia')) {
    function statusAnemia($hb, $jenis_kelamin = 'P', $hamil = false) {
        if ($hb === null || $hb === '') return 0;
        $hb = (float) $hb;
        if ($hamil) return $hb < 11 ? 1 : 0;
        if ($jenis_kelamin === 'L') return $hb < 13 ? 1 : 0;
        return $hb < 12 ? 1 : 0;
    }
}

if (! function_exists('csv_guard')) {
    function csv_guard($v) {
        if ($v === null) return '';
        $s = (string) $v;
        if ($s !== '' && preg_match('/^[\\s]*[=\\+\\-@\\t\\r\\n\|%]/', $s)) return "'" . $s;
        return $s;
    }
}

if (! function_exists('db_int')) {
    function db_int($v, $def = 0) {
        if ($v === null || $v === '') return $def;
        if (is_int($v)) return $v;
        if (is_string($v) && preg_match('/^-?\\d+$/', trim($v))) return (int) $v;
        if (is_numeric($v)) return (int) $v;
        return $def;
    }
}

if (! function_exists('db_date')) {
    function db_date($v, $def = null) {
        $v = trim((string) $v);
        if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $v)) {
            $p = explode('-', $v);
            if (checkdate((int) $p[1], (int) $p[2], (int) $p[0])) return $v;
        }
        return $def;
    }
}

if (! function_exists('formatTanggal')) {
    function formatTanggal($date) {
        if (! $date) return '-';
        $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $d = explode('-', $date);
        if (count($d) < 3) return $date;
        return $d[2] . ' ' . $bulan[(int) $d[1]] . ' ' . $d[0];
    }
}
