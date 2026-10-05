<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanModel extends Model
{
    protected $table = 'pengaturan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama_aplikasi','nama_posyandu','nama_desa','kecamatan','kabupaten','provinsi','logo','favicon','nomor_kontak','email','warna_tema','dark_mode','recaptcha_site_key','recaptcha_secret_key'];

    public function get(): array
    {
        return $this->first() ?? [];
    }
}
