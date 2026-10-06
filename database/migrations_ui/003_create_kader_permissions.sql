-- Hak akses menu per kader/user
-- Jalankan sekali di database

CREATE TABLE IF NOT EXISTS `kader_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kader_id` int(11) NOT NULL,
  `menu_key` varchar(80) NOT NULL COMMENT 'slug unik menu, mis: balita, laporan',
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kader_menu` (`kader_id`,`menu_key`),
  KEY `kader_id` (`kader_id`),
  KEY `menu_key` (`menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catatan: Admin selalu punya semua hak (bypass di kode).
-- Untuk user lama, jalankan query di bawah jika ingin beri akses default (opsional):
-- INSERT IGNORE INTO kader_permissions (kader_id, menu_key, can_view, can_edit, can_delete)
-- SELECT id, 'dashboard', 1, 0, 0 FROM kader WHERE role != 'admin';
