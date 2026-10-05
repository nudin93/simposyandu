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

/**
 * Model Balita – relasi ke OpenSID via id_penduduk_opensid + nik
 */
class Balita_model
{
    protected $table = 'balita';

    /** Fallback ke tabel posyandu_balita jika sudah migrasi */
    protected function resolveTable()
    {
        $check = fetchOne("SHOW TABLES LIKE 'posyandu_balita'");
        if ($check) {
            $this->table = 'posyandu_balita';
        }
        return $this->table;
    }

    public function getAll($limit = 500)
    {
        $t = $this->resolveTable();
        return fetchAll("SELECT * FROM `$t` ORDER BY created_at DESC LIMIT " . (int)$limit);
    }

    public function getById($id)
    {
        $t = $this->resolveTable();
        return fetchOne("SELECT * FROM `$t` WHERE id = " . (int)$id);
    }

    public function getByNik($nik)
    {
        $t = $this->resolveTable();
        $nik = escape($nik);
        return fetchOne("SELECT * FROM `$t` WHERE nik = '$nik' LIMIT 1");
    }

    public function insert(array $data)
    {
        $t = $this->resolveTable();
        $cols = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $cols[] = "`" . escape($k) . "`";
            if ($v === null) {
                $vals[] = 'NULL';
            } else {
                $vals[] = "'" . escape($v) . "'";
            }
        }
        $sql = "INSERT INTO `$t` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        if (query($sql)) {
            return lastInsertId();
        }
        return false;
    }

    public function update($id, array $data)
    {
        $t = $this->resolveTable();
        $sets = [];
        foreach ($data as $k => $v) {
            if ($v === null) {
                $sets[] = "`" . escape($k) . "` = NULL";
            } else {
                $sets[] = "`" . escape($k) . "` = '" . escape($v) . "'";
            }
        }
        $sql = "UPDATE `$t` SET " . implode(',', $sets) . " WHERE id = " . (int)$id;
        return (bool)query($sql);
    }

    public function delete($id)
    {
        $t = $this->resolveTable();
        return (bool)query("DELETE FROM `$t` WHERE id = " . (int)$id);
    }

    public function countAktif()
    {
        $t = $this->resolveTable();
        $r = fetchOne("SELECT COUNT(*) AS c FROM `$t` WHERE status = 'aktif' OR status_aktif = 1 OR status IS NULL");
        return (int)($r['c'] ?? 0);
    }
}
