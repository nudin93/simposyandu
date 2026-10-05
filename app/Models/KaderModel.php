<?php

namespace App\Models;

use CodeIgniter\Model;

class KaderModel extends Model
{
    protected $table = 'kader';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama','nik','jenis_kelamin','alamat','no_hp','jabatan','username','password','foto','role','status'];

    public function findAktifByUsername(string $username): ?array
    {
        $row = $this->where('username', $username)->where('status', 1)->first();
        return $row ?: null;
    }
}
