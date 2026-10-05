<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\OpensidModel;

class BalitaApi extends BaseController
{
    public function cari()
    {
        $q = (string) ($this->request->getPost('q') ?? $this->request->getGet('q') ?? '');
        return $this->response->setJSON(['success' => true, 'data' => (new OpensidModel())->cariPenduduk($q)]);
    }

    public function opensid()
    {
        return $this->response->setJSON(['success' => true, 'data' => (new OpensidModel())->getBalita()]);
    }
}
