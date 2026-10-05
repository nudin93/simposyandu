<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\OpensidModel;

class PendudukApi extends BaseController
{
    public function cari()
    {
        $q = (string) ($this->request->getGet('q') ?? '');
        return $this->response->setJSON(['success' => true, 'data' => (new OpensidModel())->cariPenduduk($q)]);
    }
}
