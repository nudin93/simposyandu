<?php

namespace App\Controllers;

use App\Models\BalitaModel;
use App\Models\OpensidModel;

/** Port dari controllers/Balita.php + modules/balita/index.php */
class Balita extends BaseController
{
    public function index()
    {
        $opensid = new OpensidModel();
        $balita  = new BalitaModel();

        $listOpensid = $opensid->getBalita(59, 0);
        $lokal = $balita->getLokalAktif();

        $byId = []; $byNik = [];
        foreach ($lokal as $b) {
            $oid = (int) ($b['id_penduduk_opensid'] ?? 0);
            $nik = trim($b['nik_anak'] ?? '');
            if ($oid > 0) $byId[$oid] = $b;
            if ($nik !== '') $byNik[$nik] = $b;
        }

        $rows = [];
        foreach ($listOpensid as $p) {
            $oid = (int) $p['id_penduduk'];
            $nik = trim($p['nik'] ?? '');
            $local = $byId[$oid] ?? ($byNik[$nik] ?? null);
            $rows[] = [
                'nama' => $p['nama'], 'nik' => $nik,
                'jenis_kelamin' => mapSex($p['sex'] ?? null),
                'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
                'umur_bulan' => (int) ($p['umur_bulan'] ?? 0),
                'terdaftar' => $local ? true : false,
            ];
        }

        return view('balita/index_asli');
    }

    public function tambah()
    {
        return view('balita/tambah');
    }

    public function simpan()
    {
        if (! canInput()) return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        $data = [
            'nama_lengkap'  => trim($this->request->getPost('nama_lengkap') ?? ''),
            'nik_anak'      => trim($this->request->getPost('nik') ?? ''),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin') === 'P' ? 'P' : 'L',
            'tanggal_lahir' => db_date($this->request->getPost('tanggal_lahir')),
            'status_aktif'  => 1,
        ];
        if ($data['nama_lengkap'] === '' || ! $data['tanggal_lahir']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama dan tanggal lahir wajib.']);
        }
        $id = model('BalitaModel')->insert($data);
        return $this->response->setJSON(['success' => (bool) $id, 'id' => $id]);
    }

    public function detail($id)
    {
        $row = model('BalitaModel')->find((int) $id);
        if (! $row) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('balita/detail_asli', ['row' => $row]);
    }
}
