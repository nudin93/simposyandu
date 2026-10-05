# SIMPosyandu v26.10.1

SIMPosyandu adalah aplikasi Sistem Informasi Posyandu berbasis PHP dan MySQL yang dirancang untuk membantu pemerintah desa dan kader Posyandu dalam mengelola data kesehatan masyarakat secara digital.

## Instalasi

Dengan composer:
1. composer install
2. Salin .env.example menjadi .env lalu isi database.
3. Import database/schema.sql.
4. php spark serve lalu buka http://localhost:8080

Tanpa composer (hosting):
1. Download simposyandu-ci4-full.zip dari Releases.
2. Upload ke hosting dan extract.
3. Salin env menjadi .env lalu isi database.
4. Import database/schema.sql.

## Teknologi

- PHP 8.2, MySQL, CodeIgniter 4
- Bootstrap, AdminLTE
- Database Terintegrasi OpenSID
