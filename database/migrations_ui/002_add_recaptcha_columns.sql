-- Tambah kolom Google reCAPTCHA di tabel pengaturan
-- Bisa dijalankan manual di phpMyAdmin jika auto-migrate gagal

ALTER TABLE `pengaturan`
  ADD COLUMN IF NOT EXISTS `recaptcha_site_key` VARCHAR(255) DEFAULT '' AFTER `dark_mode`,
  ADD COLUMN IF NOT EXISTS `recaptcha_secret_key` VARCHAR(255) DEFAULT '' AFTER `recaptcha_site_key`;

-- Untuk MySQL/MariaDB lama yang tidak support IF NOT EXISTS:
-- ALTER TABLE `pengaturan` ADD COLUMN `recaptcha_site_key` VARCHAR(255) DEFAULT '';
-- ALTER TABLE `pengaturan` ADD COLUMN `recaptcha_secret_key` VARCHAR(255) DEFAULT '';
