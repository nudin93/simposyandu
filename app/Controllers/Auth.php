<?php

namespace App\Controllers;

/** Port dari login.php + logout.php */
class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('kader_id')) return redirect()->to('/dashboard');
        $pengaturan = model('PengaturanModel')->get();
        return view('auth/login_asli', [
            'pengaturan' => $pengaturan,
            'siteKey'    => env('recaptcha.siteKey', $pengaturan['recaptcha_site_key'] ?? ''),
        ]);
    }

    public function attempt()
    {
        $username = trim($this->request->getPost('username') ?? '');
        $password = (string) ($this->request->getPost('password') ?? '');
        if ($username === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Nama pengguna dan kata sandi wajib diisi!');
        }
        $user = model('KaderModel')->findAktifByUsername($username);
        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Nama pengguna atau kata sandi salah!');
        }
        session()->set([
            'kader_id'       => $user['id'],
            'kader_nama'     => $user['nama'],
            'kader_username' => $user['username'],
            'kader_role'     => $user['role'],
            'kader_foto'     => $user['foto'] ?? '',
        ]);
        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
