# Migrasi SIMPOSYANDU ke CodeIgniter 4

Branch ini fondasi CI4. Main tetap PHP native sampai migrasi selesai.

## Instalasi
1. composer install
2. cp .env.example .env lalu isi DB simposyandu + opensid
3. php spark migrate (bila sudah ada migrasi) / import database/schema.sql lama
4. php spark serve lalu buka http://localhost:8080

## Yang sudah dimigrasi
- Auth (login/logout) dari login.php
- Dashboard ringkas dari dashboard.php
- Balita list/tambah/simpan/detail dari controllers/Balita.php + modules/balita
- API: /api/balita/cari, /api/balita/opensid, /api/penduduk/cari, /api/whatsnew
- Helper murni: posyandu_helper (map, IMT, umur, csv)
- Dual database: default + opensid

## Pola per modul berikutnya
Controller tipis -> Model Query Builder -> View terima $data.
Jangan query langsung di View. Ajax lama jadi Api Controller return JSON.

## TODO 31 modul
Ibu hamil, bayi, lansia, remaja, usia produktif, imunisasi, vitamin, anak TK,
pemeriksaan_* (8), kb, kegiatan, kunjungan, jadwal, artikel, sanitasi, phbs,
laporan, kartu, kader, backup, pengaturan, penduduk, keluarga, statistik, analitik.

## TODO 44 ajax
Petakan satu per satu ke Api controller, mis. save_balita -> BalitaApi::simpan.
