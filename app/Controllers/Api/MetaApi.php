<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class MetaApi extends BaseController
{
    public function whatsnew()
    {
        return $this->response->setJSON(['success' => true, 'version' => '26.10.1', 'notes' => []]);
    }
}
