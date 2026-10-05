<?php

namespace App\Controllers;

/** Port ringkas dari dashboard.php (hitung total per tabel). */
class Dashboard extends BaseController
{
    public function index()
    {
        if (session()->get('kader_role') === 'kader') {
            return redirect()->to('/kader-home');
        }
        $db = \Config\Database::connect();
        $count = function (string $table, string $where = '') use ($db): int {
            try {
                if (! $db->tableExists($table)) return 0;
                $b = $db->table($table);
                if ($where !== '') $b->where($where, null, false);
                return (int) $b->countAllResults();
            } catch (\Throwable $e) { return 0; }
        };
        return view('dashboard/index_asli', [
            'total_balita'  => $count('balita'),
            'total_bayi'    => $count('bayi'),
            'total_bumil'   => $count('ibu_hamil'),
            'total_lansia'  => $count('lansia'),
            'total_kader'   => $count('kader'),
            'pengaturan'    => model('PengaturanModel')->get(),
        ]);
    }

    public function kader()
    {
        return view('dashboard/kader', ['pengaturan' => model('PengaturanModel')->get()]);
    }
}
