<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Read-only ke database opensid (tweb_penduduk dkk).
 * Pakai koneksi 'opensid', bukan default.
 */
class OpensidModel extends Model
{
    protected $DBGroup = 'opensid';
    protected $table = 'tweb_penduduk';
    protected $primaryKey = 'id';

    public function getBalita(int $maxBulan = 59, int $minBulan = 0): array
    {
        $sql = "SELECT p.id AS id_penduduk, p.nik, k.no_kk, p.nama, p.sex,
            p.tempatlahir AS tempat_lahir, p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(MONTH, p.tanggallahir, CURDATE()) AS umur_bulan,
            p.nama_ayah, p.nama_ibu, w.dusun, w.rt, w.rw
            FROM tweb_penduduk p
            LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
            LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
            WHERE p.status_dasar = 1 AND p.tanggallahir IS NOT NULL
            AND TIMESTAMPDIFF(MONTH, p.tanggallahir, CURDATE()) BETWEEN ? AND ?
            ORDER BY p.tanggallahir DESC";
        try {
            return $this->db->query($sql, [$minBulan, $maxBulan])->getResultArray();
        } catch (\Throwable $e) { return []; }
    }

    public function cariPenduduk(string $q, int $limit = 25): array
    {
        $q = trim($q);
        if ($q === '') return [];
        $like = '%' . $q . '%';
        $sql = "SELECT p.id AS id_penduduk, p.nik, p.nama, p.sex FROM tweb_penduduk p
            WHERE p.status_dasar = 1 AND (p.nama LIKE ? OR p.nik LIKE ?) ORDER BY p.nama LIMIT " . (int) $limit;
        try {
            $rows = $this->db->query($sql, [$like, $like])->getResultArray();
            foreach ($rows as &$r) { $r['jenis_kelamin'] = mapSex($r['sex'] ?? null); }
            return $rows;
        } catch (\Throwable $e) { return []; }
    }
}
