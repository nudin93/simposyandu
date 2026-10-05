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
 *
 * Pengembang  : Zainudin Larau
 * Tahun       : 2026
 *
 * Deskripsi:
 * Aplikasi ini dikembangkan untuk membantu pengelolaan
 * data Posyandu secara digital, meliputi data balita,
 * ibu hamil, pemeriksaan kesehatan, pertumbuhan anak,
 * statistik, dan pelayanan masyarakat.
 *
 * Ketentuan:
 * Aplikasi ini merupakan karya pengembang dan
 * TIDAK DIPERJUALBELIKAN.
 *
 * Setiap penggunaan, penggandaan, perubahan kode,
 * atau pendistribusian aplikasi wajib mendapatkan
 * izin dari pemilik dan pengembang.
 *
 * Hak Cipta:
 * © 2026 Zainudin Larau
 * Semua Hak Dilindungi.
 *
 * ==========================================================
 */

/**
 * Menu Helper - Dynamic menu dari tabel menus
 * Fallback ke hardcode jika tabel belum ada.
 */

function getMenusFromDb() {
    static $menus = null;
    if ($menus !== null) return $menus;

    try {
        $rows = fetchAll("SELECT * FROM menus WHERE is_active = 1 ORDER BY order_num ASC, id ASC");
        if (empty($rows)) {
            $menus = [];
            return $menus;
        }

        // Build tree
        $byParent = [];
        foreach ($rows as $row) {
            $pid = (int)($row['parent_id'] ?? 0);
            $byParent[$pid][] = $row;
        }

        $menus = $byParent;
        return $menus;
    } catch (Throwable $e) {
        $menus = [];
        return $menus;
    }
}

function renderMenuTree($parentId = 0, $level = 0) {
    $all = getMenusFromDb();
    if (empty($all) || empty($all[$parentId])) return '';

    $html = '';
    $current = basename($_SERVER['PHP_SELF'] ?? '');
    $folder = basename(dirname($_SERVER['PHP_SELF'] ?? ''));

    foreach ($all[$parentId] as $item) {
        if (!empty($item['is_header'])) {
            $html .= '<li class="nav-header">' . htmlspecialchars($item['title']) . '</li>';
            continue;
        }

        $children = $all[(int)$item['id']] ?? [];
        $hasChild = !empty($children);
        $url = $item['url'] ?? '#';
        if ($url !== '#' && strpos($url, 'http') !== 0) {
            $url = APP_URL . '/' . ltrim($url, '/');
        }

        $isActive = false;
        if ($url !== '#' && (strpos($_SERVER['REQUEST_URI'] ?? '', $item['url']) !== false || $current === basename($item['url']))) {
            $isActive = true;
        }
        // Cek child active
        if ($hasChild) {
            foreach ($children as $ch) {
                if (strpos($_SERVER['REQUEST_URI'] ?? '', $ch['url'] ?? '') !== false) {
                    $isActive = true;
                    break;
                }
            }
        }

        $activeClass = $isActive ? 'active' : '';
        $openClass = ($hasChild && $isActive) ? 'menu-open' : '';
        $display = ($hasChild && $isActive) ? 'display:block' : 'display:none';

        $html .= '<li class="nav-item ' . $openClass . '">';
        if ($hasChild) {
            $html .= '<a href="#" class="nav-link ' . $activeClass . '" onclick="return toggleMenu(this)">';
            $html .= '<i class="nav-icon ' . htmlspecialchars($item['icon'] ?? 'fas fa-circle') . '"></i>';
            $html .= '<p>' . htmlspecialchars($item['title']) . ' <i class="right fas fa-angle-left"></i></p>';
            $html .= '</a>';
            $html .= '<ul class="nav nav-treeview" style="' . $display . '">';
            $html .= renderMenuTree((int)$item['id'], $level + 1);
            $html .= '</ul>';
        } else {
            $html .= '<a href="' . htmlspecialchars($url) . '" class="nav-link ' . $activeClass . '">';
            $html .= '<i class="nav-icon ' . htmlspecialchars($item['icon'] ?? 'far fa-circle') . '"></i>';
            $html .= '<p>' . htmlspecialchars($item['title']) . '</p>';
            $html .= '</a>';
        }
        $html .= '</li>';
    }
    return $html;
}

function hasDynamicMenus() {
    $m = getMenusFromDb();
    return !empty($m);
}
