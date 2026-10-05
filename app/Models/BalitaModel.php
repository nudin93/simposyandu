<?php

namespace App\Models;

use CodeIgniter\Model;

/** Port dari models/Balita_model.php (mysqli) ke Query Builder. */
class BalitaModel extends Model
{
    protected $table            = 'balita';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_penduduk_opensid','status_integrasi','nomor_peserta','nik_anak','no_kk','nama_lengkap','jenis_kelamin','tanggal_lahir','nama_ayah','nama_ibu','dusun','rt','rw','status_aktif'];
    protected $useTimestamps    = false;

    public function getLokalAktif(int $limit = 500): array
    {
        return $this->select('*, TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) AS umur_bulan')
            ->groupStart()->where('status_aktif', 1)->orWhere('status_aktif IS NULL', null, false)->groupEnd()
            ->orderBy('tanggal_lahir', 'DESC')->findAll($limit);
    }
}
