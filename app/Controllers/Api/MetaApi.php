<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class MetaApi extends BaseController
{
    public function whatsnew()
    {
        $cfg = config('Simposyandu');
        return $this->response->setJSON(['success' => true, 'version' => $cfg->versi, 'notes' => $cfg->changelog()]);
    }
}
