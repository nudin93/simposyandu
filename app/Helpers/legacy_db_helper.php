<?php
/**
 * Kompatibilitas: view asli memanggil fetchAll/fetchOne/query/escape/opensid_*.
 * Implementasi via Koneksi CI4, HTML tidak diubah.
 */
if (! function_exists('legacy_db')) {
    function legacy_db() { return \Config\Database::connect(); }
}
if (! function_exists('legacy_opensid_db')) {
    function legacy_opensid_db() {
        try { return \Config\Database::connect('opensid'); }
        catch (\Throwable $e) { return null; }
    }
}
if (! function_exists('query')) {
    function query($sql) {
        try { return legacy_db()->query($sql); }
        catch (\Throwable $e) { log_message('error', 'DB: '.$e->getMessage()); return false; }
    }
}
if (! function_exists('fetchAll')) {
    function fetchAll($sql): array {
        $r = query($sql);
        if (! $r) return [];
        try { return $r->getResultArray(); } catch (\Throwable $e) { return []; }
    }
}
if (! function_exists('fetchOne')) {
    function fetchOne($sql) {
        $rows = fetchAll($sql);
        return $rows[0] ?? null;
    }
}
if (! function_exists('escape')) {
    function escape($s): string {
        try { return legacy_db()->escapeString((string) $s); }
        catch (\Throwable $e) { return addslashes((string) $s); }
    }
}
if (! function_exists('opensid_available')) {
    function opensid_available(): bool {
        $db = legacy_opensid_db();
        if (! $db) return false;
        try { $db->query('SELECT 1'); return true; }
        catch (\Throwable $e) { return false; }
    }
}
if (! function_exists('opensid_query')) {
    function opensid_query($sql) {
        $db = legacy_opensid_db();
        if (! $db) return false;
        try { return $db->query($sql); } catch (\Throwable $e) { return false; }
    }
}
if (! function_exists('opensid_fetchAll')) {
    function opensid_fetchAll($sql): array {
        $r = opensid_query($sql);
        if (! $r) return [];
        try { return $r->getResultArray(); } catch (\Throwable $e) { return []; }
    }
}
if (! function_exists('opensid_fetchOne')) {
    function opensid_fetchOne($sql) {
        $rows = opensid_fetchAll($sql);
        return $rows[0] ?? null;
    }
}
if (! function_exists('opensid_escape')) {
    function opensid_escape($s): string {
        $db = legacy_opensid_db();
        if (! $db) return addslashes((string) $s);
        try { return $db->escapeString((string) $s); } catch (\Throwable $e) { return addslashes((string) $s); }
    }
}
if (! function_exists('getPengaturan')) {
    function getPengaturan(): array {
        try {
            $row = legacy_db()->table('pengaturan')->get()->getRowArray();
            return $row ?? [];
        } catch (\Throwable $e) { return []; }
    }
}
if (! function_exists('currentUser')) {
    function currentUser() {
        if (! session()->get('kader_id')) return null;
        return [
            'id' => session()->get('kader_id'),
            'nama' => session()->get('kader_nama'),
            'username' => session()->get('kader_username'),
            'role' => session()->get('kader_role'),
            'foto' => session()->get('kader_foto') ?? '',
        ];
    }
}
if (! function_exists('isLoggedIn')) {
    function isLoggedIn(): bool { return (bool) session()->get('kader_id'); }
}
if (! function_exists('csrfToken')) {
    function csrfToken(): string { return csrf_hash(); }
}
if (! defined('APP_URL')) {
    define('APP_URL', rtrim(base_url(), '/'));
}
// Port fungsi OpenSID berat dari database.php lama (tanpa ubah logika)
if (! function_exists('getBalitaOpenSID')) {
    function getBalitaOpenSID($max_bulan = 59, $min_bulan = 0) {
        if (! opensid_available()) return [];
        $max_bulan = (int) $max_bulan; $min_bulan = (int) $min_bulan;
        $sql = "SELECT p.id AS id_penduduk, p.nik, k.no_kk, p.nama, p.sex,
            p.tempatlahir AS tempat_lahir, p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(MONTH, p.tanggallahir, CURDATE()) AS umur_bulan,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur_tahun,
            p.nama_ayah, p.ayah_nik AS nik_ayah, p.nama_ibu, p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp, p.alamat_sekarang AS alamat, w.dusun, w.rt, w.rw
            FROM tweb_penduduk p
            LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
            LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
            WHERE p.status_dasar = 1 AND p.tanggallahir IS NOT NULL
            AND TIMESTAMPDIFF(MONTH, p.tanggallahir, CURDATE()) BETWEEN $min_bulan AND $max_bulan
            ORDER BY p.tanggallahir DESC, p.nama ASC";
        $rows = opensid_fetchAll($sql);
        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'id_penduduk' => $r['id_penduduk'], 'nik' => $r['nik'] ?? '', 'no_kk' => $r['no_kk'] ?? '',
                'nama' => $r['nama'] ?? '', 'jenis_kelamin' => mapSex($r['sex'] ?? null),
                'tempat_lahir' => $r['tempat_lahir'] ?? '', 'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
                'umur_bulan' => (int)($r['umur_bulan'] ?? 0), 'umur_tahun' => (int)($r['umur_tahun'] ?? 0),
                'nama_ayah' => $r['nama_ayah'] ?? '', 'nik_ayah' => $r['nik_ayah'] ?? '',
                'nama_ibu' => $r['nama_ibu'] ?? '', 'nik_ibu' => $r['nik_ibu'] ?? '',
                'no_hp' => $r['no_hp'] ?? '', 'alamat' => $r['alamat'] ?? '',
                'dusun' => $r['dusun'] ?? '', 'rt' => $r['rt'] ?? '', 'rw' => $r['rw'] ?? '',
                'sumber' => 'opensid',
            ];
        }
        return $data;
    }
}
if (! function_exists('getPendudukOpenSIDById')) {
    function getPendudukOpenSIDById($id) {
        if (! opensid_available() || ! $id) return null;
        $id = (int) $id;
        $sql = "SELECT p.id AS id_penduduk, p.nik, k.no_kk, p.nama, p.sex,
            p.tempatlahir AS tempat_lahir, p.tanggallahir AS tanggal_lahir,
            p.nama_ayah, p.nama_ibu, p.telepon AS no_hp, p.alamat_sekarang AS alamat,
            w.dusun, w.rt, w.rw FROM tweb_penduduk p
            LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
            LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
            WHERE p.id = $id AND p.status_dasar = 1 LIMIT 1";
        $r = opensid_fetchOne($sql);
        if (! $r) return null;
        $r['jenis_kelamin'] = mapSex($r['sex'] ?? null);
        return $r;
    }
}
