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
 * SIMPOSYANDU - Helper Hak Akses Menu per User
 * Hak: Lihat (view), Edit, Hapus (delete)
 * Admin selalu bypass (penuh).
 */

if (!function_exists('getMenuDefinitions')) {
    /**
     * Daftar menu yang bisa di-assign hak akses.
     * key = slug unik, label = nama tampilan, group = kelompok di form.
     */
    function getMenuDefinitions() {
        return [
            // UTAMA
            'dashboard'     => ['label' => 'Beranda / Dashboard', 'group' => 'UTAMA'],
            'statistik'     => ['label' => 'Statistik', 'group' => 'UTAMA'],

            // DATA WARGA
            'penduduk'      => ['label' => 'Penduduk', 'group' => 'DATA WARGA'],
            'keluarga'      => ['label' => 'Keluarga', 'group' => 'DATA WARGA'],

            // SIKLUS HIDUP
            'ibu_hamil'             => ['label' => 'Data Ibu Hamil', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_ibu_hamil' => ['label' => 'Pemeriksaan ANC', 'group' => 'SIKLUS HIDUP'],
            'bayi'                  => ['label' => 'Data Bayi', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_bayi'      => ['label' => 'Pemeriksaan Bayi', 'group' => 'SIKLUS HIDUP'],
            'balita'                => ['label' => 'Data Balita', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_balita'    => ['label' => 'Pemeriksaan Gizi Balita', 'group' => 'SIKLUS HIDUP'],
            'imunisasi'             => ['label' => 'Imunisasi', 'group' => 'SIKLUS HIDUP'],
            'vitamin'               => ['label' => 'Vitamin A', 'group' => 'SIKLUS HIDUP'],
            'anak_tk'               => ['label' => 'Data Anak TK', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_anak_tk'   => ['label' => 'Pemeriksaan Anak TK', 'group' => 'SIKLUS HIDUP'],
            'remaja'                => ['label' => 'Data Remaja', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_remaja'    => ['label' => 'Pemeriksaan Remaja', 'group' => 'SIKLUS HIDUP'],
            'lansia'                => ['label' => 'Data Lansia', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_lansia'    => ['label' => 'Pemeriksaan Lansia', 'group' => 'SIKLUS HIDUP'],
            'usia_produktif'        => ['label' => 'Usia Produktif', 'group' => 'SIKLUS HIDUP'],
            'pemeriksaan_dewasa'    => ['label' => 'Pemeriksaan Dewasa', 'group' => 'SIKLUS HIDUP'],
            'kb'                    => ['label' => 'Keluarga Berencana (KB)', 'group' => 'SIKLUS HIDUP'],

            // KEGIATAN
            'kegiatan_posyandu' => ['label' => 'Kegiatan Posyandu', 'group' => 'KEGIATAN'],
            'kunjungan_rumah'   => ['label' => 'Kunjungan Rumah', 'group' => 'KEGIATAN'],
            'jadwal'            => ['label' => 'Jadwal Posyandu', 'group' => 'KEGIATAN'],

            // ARTIKEL
            'artikel'           => ['label' => 'Artikel', 'group' => 'ARTIKEL'],

            // KESEHATAN LINGKUNGAN
            'sanitasi'          => ['label' => 'Sanitasi', 'group' => 'KESEHATAN LINGKUNGAN'],
            'phbs'              => ['label' => 'PHBS', 'group' => 'KESEHATAN LINGKUNGAN'],

            // LAPORAN
            'laporan'           => ['label' => 'Laporan ILP', 'group' => 'LAPORAN'],
            'kartu'             => ['label' => 'Kartu / Cetak', 'group' => 'LAPORAN'],

            // PENGATURAN (biasanya hanya admin)
            'kader'             => ['label' => 'Manajemen Kader/User', 'group' => 'PENGATURAN'],
            'backup'            => ['label' => 'Backup Database', 'group' => 'PENGATURAN'],
            'pengaturan'        => ['label' => 'Pengaturan Aplikasi', 'group' => 'PENGATURAN'],
        ];
    }
}

if (!function_exists('getUserPermissions')) {
    /**
     * Ambil semua permission user (array menu_key => [view,edit,delete])
     * Di-cache di session untuk performa.
     */
    function getUserPermissions($kaderId = null) {
        if ($kaderId === null) {
            $kaderId = $_SESSION['kader_id'] ?? 0;
        }
        $kaderId = (int)$kaderId;
        if ($kaderId <= 0) return [];

        // Cache di session
        $cacheKey = 'perms_' . $kaderId;
        if (isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey])) {
            return $_SESSION[$cacheKey];
        }

        $perms = [];
        try {
            $rows = fetchAll("SELECT menu_key, can_view, can_edit, can_delete FROM kader_permissions WHERE kader_id = $kaderId");
            if ($rows) {
                foreach ($rows as $r) {
                    $perms[$r['menu_key']] = [
                        'view'   => (int)$r['can_view'],
                        'edit'   => (int)$r['can_edit'],
                        'delete' => (int)$r['can_delete'],
                    ];
                }
            }
        } catch (Throwable $e) {
            // Tabel belum ada / error DB → anggap kosong, pakai default role
            $perms = [];
        }
        $_SESSION[$cacheKey] = $perms;
        return $perms;
    }
}

if (!function_exists('clearPermissionCache')) {
    function clearPermissionCache($kaderId = null) {
        if ($kaderId === null) {
            $kaderId = $_SESSION['kader_id'] ?? 0;
        }
        unset($_SESSION['perms_' . (int)$kaderId]);
    }
}

if (!function_exists('getDefaultMenusForRole')) {
    /**
     * Menu default jika kader belum punya baris di kader_permissions.
     * Admin tidak memakai ini (bypass).
     */
    function getDefaultMenusForRole($role = null) {
        $role = $role ?? ($_SESSION['kader_role'] ?? 'kader');
        // Operasional posyandu — tanpa pengaturan/backup/kader
        $ops = [
            'dashboard', 'statistik',
            'penduduk', 'keluarga',
            'ibu_hamil', 'pemeriksaan_ibu_hamil',
            'bayi', 'pemeriksaan_bayi',
            'balita', 'pemeriksaan_balita', 'imunisasi', 'vitamin',
            'anak_tk', 'pemeriksaan_anak_tk',
            'remaja', 'pemeriksaan_remaja',
            'lansia', 'pemeriksaan_lansia',
            'usia_produktif', 'pemeriksaan_dewasa', 'kb',
            'kegiatan_posyandu', 'kunjungan_rumah', 'jadwal',
            'artikel', 'sanitasi', 'phbs',
            'laporan', 'kartu',
        ];
        if ($role === 'kepala_desa') {
            // Kepala desa: lihat laporan & data, tanpa edit pengaturan
            return $ops;
        }
        if ($role === 'bidan') {
            return $ops;
        }
        // kader
        return $ops;
    }
}

if (!function_exists('canViewMenu')) {
    function canViewMenu($menuKey) {
        if (function_exists('isAdmin') && isAdmin()) return true;
        $perms = getUserPermissions();
        // Jika sudah ada permission di DB → ikut checklist admin
        if (!empty($perms)) {
            return !empty($perms[$menuKey]['view']);
        }
        // Belum di-set: pakai default role (supaya menu tidak kosong)
        $defaults = getDefaultMenusForRole();
        return in_array($menuKey, $defaults, true);
    }
}

if (!function_exists('canEditMenu')) {
    function canEditMenu($menuKey) {
        if (function_exists('isAdmin') && isAdmin()) return true;
        $perms = getUserPermissions();
        if (!empty($perms)) {
            return !empty($perms[$menuKey]['edit']);
        }
        // Default: boleh edit menu operasional (bukan pengaturan)
        $defaults = getDefaultMenusForRole();
        $blocked = ['kader', 'backup', 'pengaturan'];
        return in_array($menuKey, $defaults, true) && !in_array($menuKey, $blocked, true);
    }
}

if (!function_exists('canDeleteMenu')) {
    function canDeleteMenu($menuKey) {
        if (function_exists('isAdmin') && isAdmin()) return true;
        $perms = getUserPermissions();
        if (!empty($perms)) {
            return !empty($perms[$menuKey]['delete']);
        }
        return false; // default: tidak boleh hapus
    }
}

if (!function_exists('saveUserPermissions')) {
    /**
     * Simpan permission dari form.
     * $perms = [ 'balita' => ['view'=>1,'edit'=>1,'delete'=>0], ... ]
     */
    function saveUserPermissions($kaderId, array $perms) {
        $kaderId = (int)$kaderId;
        if ($kaderId <= 0) return false;

        // Hapus lama
        query("DELETE FROM kader_permissions WHERE kader_id = $kaderId");

        $defs = getMenuDefinitions();
        foreach ($perms as $key => $p) {
            if (!isset($defs[$key])) continue;
            $v = !empty($p['view']) ? 1 : 0;
            $e = !empty($p['edit']) ? 1 : 0;
            $d = !empty($p['delete']) ? 1 : 0;
            // Jika edit/delete dicentang, otomatis view juga
            if ($e || $d) $v = 1;
            if ($v || $e || $d) {
                $keyEsc = escape($key);
                query("INSERT INTO kader_permissions (kader_id, menu_key, can_view, can_edit, can_delete)
                       VALUES ($kaderId, '$keyEsc', $v, $e, $d)");
            }
        }
        clearPermissionCache($kaderId);
        return true;
    }
}

if (!function_exists('renderPermissionCheckboxes')) {
    /**
     * Render HTML form hak akses (untuk halaman tambah/edit user).
     * Kolom centang selalu terlihat penuh (sticky kanan, tanpa perlu digeser).
     */
    function renderPermissionCheckboxes($existing = []) {
        $defs = getMenuDefinitions();
        $groups = [];
        foreach ($defs as $key => $info) {
            $groups[$info['group']][$key] = $info['label'];
        }

        $html = '<style>
        .perm-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .perm-table { width: 100%; min-width: 0; table-layout: fixed; margin-bottom: 0; }
        .perm-table th, .perm-table td { vertical-align: middle !important; }
        .perm-table .col-menu { width: auto; word-wrap: break-word; overflow-wrap: break-word; }
        .perm-table .col-check {
            width: 72px;
            min-width: 72px;
            max-width: 72px;
            text-align: center;
            position: sticky;
            right: 0;
            background: #fff;
            z-index: 2;
            box-shadow: -4px 0 6px -4px rgba(0,0,0,.12);
        }
        .perm-table thead .col-check { background: #f8f9fa; z-index: 3; }
        .perm-table .col-check.col-lihat { right: 144px; }
        .perm-table .col-check.col-edit  { right: 72px; }
        .perm-table .col-check.col-hapus { right: 0; }
        .perm-table tbody tr:hover .col-check { background: #f1f5f9; }
        .perm-table .table-secondary .col-check { background: #e9ecef !important; box-shadow: none; }
        .perm-table .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin: 0;
            cursor: pointer;
        }
        .perm-table th.col-check { font-size: 0.75rem; padding: 0.5rem 0.25rem !important; white-space: nowrap; }
        .perm-table td.col-check { padding: 0.4rem 0.25rem !important; }
        @media (max-width: 576px) {
            .perm-table .col-check { width: 56px; min-width: 56px; max-width: 56px; }
            .perm-table .col-check.col-lihat { right: 112px; }
            .perm-table .col-check.col-edit  { right: 56px; }
            .perm-table th.col-check { font-size: 0.65rem; }
        }
        </style>';

        $html .= '<div class="card border-primary mt-4">';
        $html .= '<div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">';
        $html .= '<h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Hak Akses Menu</h5>';
        $html .= '<div class="btn-group btn-group-sm">';
        $html .= '<button type="button" class="btn btn-light btn-sm" onclick="checkAllPerms(true)"><i class="fas fa-check-double"></i> Centang Semua</button>';
        $html .= '<button type="button" class="btn btn-outline-light btn-sm" onclick="checkAllPerms(false)"><i class="fas fa-times"></i> Hapus Semua</button>';
        $html .= '</div></div>';
        $html .= '<div class="card-body p-0">';
        $html .= '<div class="perm-table-wrap">';
        $html .= '<table class="table table-bordered table-hover perm-table mb-0">';
        $html .= '<thead class="table-light"><tr>';
        $html .= '<th class="col-menu">Menu</th>';
        $html .= '<th class="col-check col-lihat"><i class="fas fa-eye text-info d-block mb-1"></i>Lihat</th>';
        $html .= '<th class="col-check col-edit"><i class="fas fa-edit text-warning d-block mb-1"></i>Edit</th>';
        $html .= '<th class="col-check col-hapus"><i class="fas fa-trash text-danger d-block mb-1"></i>Hapus</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($groups as $groupName => $items) {
            $html .= '<tr class="table-secondary">';
            $html .= '<td class="fw-bold py-2 col-menu">' . htmlspecialchars($groupName) . '</td>';
            $html .= '<td class="col-check col-lihat"></td>';
            $html .= '<td class="col-check col-edit"></td>';
            $html .= '<td class="col-check col-hapus"></td>';
            $html .= '</tr>';
            foreach ($items as $key => $label) {
                $v = !empty($existing[$key]['view']) ? 'checked' : '';
                $e = !empty($existing[$key]['edit']) ? 'checked' : '';
                $d = !empty($existing[$key]['delete']) ? 'checked' : '';
                $html .= '<tr>';
                $html .= '<td class="ps-3 col-menu">' . htmlspecialchars($label) . '</td>';
                $html .= '<td class="col-check col-lihat"><input type="checkbox" class="form-check-input perm-view" name="perm[' . $key . '][view]" value="1" ' . $v . ' data-key="' . $key . '" title="Lihat"></td>';
                $html .= '<td class="col-check col-edit"><input type="checkbox" class="form-check-input perm-edit" name="perm[' . $key . '][edit]" value="1" ' . $e . ' data-key="' . $key . '" title="Edit"></td>';
                $html .= '<td class="col-check col-hapus"><input type="checkbox" class="form-check-input perm-delete" name="perm[' . $key . '][delete]" value="1" ' . $d . ' data-key="' . $key . '" title="Hapus"></td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></div>';
        $html .= '<div class="p-3 bg-light border-top small text-muted">';
        $html .= '<i class="fas fa-info-circle me-1"></i> Centang <strong>Lihat</strong> agar menu muncul di sidebar. ';
        $html .= 'Centang <strong>Edit</strong> / <strong>Hapus</strong> untuk tombol aksi. ';
        $html .= 'Jika Edit/Hapus dicentang, Lihat otomatis aktif. Admin selalu punya semua hak.';
        $html .= '</div></div></div>';

        $html .= '<script>
        function checkAllPerms(on) {
            document.querySelectorAll(".perm-view, .perm-edit, .perm-delete").forEach(function(el){ el.checked = on; });
        }
        document.querySelectorAll(".perm-edit, .perm-delete").forEach(function(el){
            el.addEventListener("change", function(){
                if (this.checked) {
                    var key = this.getAttribute("data-key");
                    var view = document.querySelector(\'.perm-view[data-key="\'+key+\'"]\');
                    if (view) view.checked = true;
                }
            });
        });
        </script>';

        return $html;
    }
}

