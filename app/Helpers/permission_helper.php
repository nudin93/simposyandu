<?php
/**
 * CI4 port dari helpers/permission_helper.php.
 * Session via session(), DB via model, bukan $_SESSION langsung.
 */

if (! function_exists('getMenuDefinitions')) {
    function getMenuDefinitions() {
        return [
            'dashboard' => ['label' => 'Beranda / Dashboard', 'group' => 'UTAMA'],
            'statistik' => ['label' => 'Statistik', 'group' => 'UTAMA'],
            'penduduk' => ['label' => 'Penduduk', 'group' => 'DATA WARGA'],
            'keluarga' => ['label' => 'Keluarga', 'group' => 'DATA WARGA'],
            'ibu_hamil' => ['label' => 'Data Ibu Hamil', 'group' => 'SIKLUS HIDUP'],
            'bayi' => ['label' => 'Data Bayi', 'group' => 'SIKLUS HIDUP'],
            'balita' => ['label' => 'Data Balita', 'group' => 'SIKLUS HIDUP'],
            'lansia' => ['label' => 'Data Lansia', 'group' => 'SIKLUS HIDUP'],
            'imunisasi' => ['label' => 'Imunisasi', 'group' => 'SIKLUS HIDUP'],
            'laporan' => ['label' => 'Laporan ILP', 'group' => 'LAPORAN'],
            'kader' => ['label' => 'Manajemen Kader/User', 'group' => 'PENGATURAN'],
            'pengaturan' => ['label' => 'Pengaturan Aplikasi', 'group' => 'PENGATURAN'],
        ];
    }
}

if (! function_exists('isAdmin')) {
    function isAdmin() { return session()->get('kader_role') === 'admin'; }
}
if (! function_exists('canInput')) {
    function canInput() { return in_array(session()->get('kader_role'), ['admin','bidan','kader'], true); }
}
if (! function_exists('getUserPermissions')) {
    function getUserPermissions($kaderId = null) {
        $kaderId = (int) ($kaderId ?? session()->get('kader_id'));
        if ($kaderId <= 0) return [];
        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('kader_permissions')) return [];
            $rows = $db->table('kader_permissions')->where('kader_id', $kaderId)->get()->getResultArray();
            $out = [];
            foreach ($rows as $r) {
                $out[$r['menu_key']] = ['view'=>(int)$r['can_view'],'edit'=>(int)$r['can_edit'],'delete'=>(int)$r['can_delete']];
            }
            return $out;
        } catch (\Throwable $e) { return []; }
    }
}
if (! function_exists('canViewMenu')) {
    function canViewMenu($menuKey) {
        if (isAdmin()) return true;
        $perms = getUserPermissions();
        if (! empty($perms)) return ! empty($perms[$menuKey]['view']);
        return in_array($menuKey, ['dashboard','balita','penduduk','laporan'], true);
    }
}
