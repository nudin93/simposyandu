<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Pengganti requireLogin() + guard hak menu di includes/header.php */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('kader_id')) {
            return redirect()->to('/login');
        }

        // Guard menu sederhana (detail di permission_helper)
        $path = strtolower(trim($request->getUri()->getPath(), '/'));
        $seg  = explode('/', $path)[0] ?? '';
        $map  = [
            'balita' => 'balita', 'dashboard' => 'dashboard',
            'penduduk' => 'penduduk', 'keluarga' => 'keluarga',
        ];
        if (isset($map[$seg]) && function_exists('canViewMenu')) {
            helper(['permission']);
            if (! canViewMenu($map[$seg])) {
                return redirect()->to('/dashboard')->with('error', 'Tidak punya hak akses.');
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
