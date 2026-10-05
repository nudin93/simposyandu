<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Versi aplikasi + catatan pembaruan (pengganti config/version.php lama). */
class Simposyandu extends BaseConfig
{
    public string $versi = '26.10.2';

    public function changelog(): array
    {
        return [
            ['versi' => '26.10.2', 'tanggal' => '2026-10-06', 'judul' => 'Penyesuaian instalasi',
             'items' => ['Penyesuaian paket instalasi', 'Perbaikan tampilan informasi']],
            ['versi' => '26.10.1', 'tanggal' => '2026-10-06', 'judul' => 'Penyesuaian versi',
             'items' => ['Penyesuaian nomor versi mengikuti rilis', 'Perbaikan struktur database', 'Perbaikan tampilan informasi']],
        ];
    }
}
