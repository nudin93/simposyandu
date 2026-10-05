<?php
/**
 * POSYANDU DIGITAL DESA
 * Versi: 1.0.0
 * Dikembangkan oleh: Zainudin Larau
 *
 * Tujuan:
 * Aplikasi digital untuk membantu pendataan,
 * pelayanan, pemantauan, dan pelaporan kegiatan Posyandu Desa.
 */
/**
 * ==========================================================
 * SIMPOSYANDU - Sistem Informasi Posyandu Terintegrasi
 * ==========================================================
 * Pengembang  : Zainudin Larau
 * Tahun       : 2026
 * ==========================================================
 */

require_once __DIR__ . '/../config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

try {
    // CSRF
    $csrf = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!verifyCsrf($csrf)) {
        jsonResponse(false, 'Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.');
    }

    // Hanya admin yang boleh menambah user/kader
    if (!function_exists('isAdmin') || !isAdmin()) {
        jsonResponse(false, 'Akses ditolak. Hanya Administrator yang dapat menambah user kader.');
    }

    $nama     = trim((string)($_POST['nama'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role     = trim((string)($_POST['role'] ?? 'kader'));
    $nik      = trim((string)($_POST['nik'] ?? ''));
    $jk       = trim((string)($_POST['jenis_kelamin'] ?? 'P'));
    $no_hp    = trim((string)($_POST['no_hp'] ?? ''));
    $jabatan  = trim((string)($_POST['jabatan'] ?? 'Kader'));
    $alamat   = trim((string)($_POST['alamat'] ?? ''));

    $allowedRoles = ['admin', 'kader', 'bidan', 'kepala_desa'];
    if (!in_array($role, $allowedRoles, true)) {
        $role = 'kader';
    }
    if ($jk !== 'L' && $jk !== 'P') {
        $jk = 'P';
    }

    if ($nama === '' || $username === '' || $password === '') {
        jsonResponse(false, 'Nama, username, dan password wajib diisi.');
    }
    if (strlen($password) < 6) {
        jsonResponse(false, 'Password minimal 6 karakter.');
    }
    if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
        jsonResponse(false, 'NIK harus 16 digit angka.');
    }

    $usernameEsc = escape($username);
    if (numRows("SELECT id FROM kader WHERE username='$usernameEsc'") > 0) {
        jsonResponse(false, 'Username sudah digunakan. Pilih username lain.');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $up = uploadFile($_FILES['foto'], 'kader');
        if (!empty($up['success'])) {
            $foto = $up['filename'];
        } elseif (!empty($up['message'])) {
            // Upload gagal tidak membatalkan simpan data — hanya foto kosong
        }
    }

    $sql = "INSERT INTO kader (nama, nik, jenis_kelamin, no_hp, jabatan, alamat, username, password, role, foto, status)
            VALUES (
              '" . escape($nama) . "',
              '" . escape($nik) . "',
              '" . escape($jk) . "',
              '" . escape($no_hp) . "',
              '" . escape($jabatan) . "',
              '" . escape($alamat) . "',
              '" . escape($username) . "',
              '" . escape($hash) . "',
              '" . escape($role) . "',
              '" . escape($foto) . "',
              1
            )";

    if (!query($sql)) {
        jsonResponse(false, 'Gagal menyimpan data ke database. Periksa koneksi atau struktur tabel kader.');
    }

    $kaderId = 0;
    if (function_exists('lastInsertId')) {
        $kaderId = (int) lastInsertId();
    }

    // Hak akses menu (bukan admin)
    if ($role !== 'admin' && $kaderId > 0 && function_exists('saveUserPermissions')) {
        try {
            $permInput = $_POST['perm'] ?? [];
            $perms = [];
            if (is_array($permInput)) {
                foreach ($permInput as $key => $p) {
                    if (!is_array($p)) continue;
                    $perms[$key] = [
                        'view'   => !empty($p['view']) ? 1 : 0,
                        'edit'   => !empty($p['edit']) ? 1 : 0,
                        'delete' => !empty($p['delete']) ? 1 : 0,
                    ];
                }
            }
            saveUserPermissions($kaderId, $perms);
        } catch (Throwable $e) {
            // Jangan gagalkan simpan kader hanya karena permission
        }
    }

    jsonResponse(true, 'Data kader berhasil disimpan!');
} catch (Throwable $e) {
    jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage());
}
