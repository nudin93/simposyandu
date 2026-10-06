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

class Ibu_hamil_model
{
    protected $table = 'ibu_hamil';

    protected function resolveTable()
    {
        $check = fetchOne("SHOW TABLES LIKE 'posyandu_ibu_hamil'");
        if ($check) {
            $this->table = 'posyandu_ibu_hamil';
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

    public function insert(array $data)
    {
        $t = $this->resolveTable();
        $cols = $vals = [];
        foreach ($data as $k => $v) {
            $cols[] = '`' . escape($k) . '`';
            $vals[] = $v === null ? 'NULL' : "'" . escape($v) . "'";
        }
        $sql = "INSERT INTO `$t` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        return query($sql) ? lastInsertId() : false;
    }

    public function countAktif()
    {
        $t = $this->resolveTable();
        $r = fetchOne("SELECT COUNT(*) AS c FROM `$t` WHERE status = 'aktif' OR status IS NULL");
        return (int)($r['c'] ?? 0);
    }
}
