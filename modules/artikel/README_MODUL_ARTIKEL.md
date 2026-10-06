# Modul Artikel Kader — SIMPOSYANDU

## Instalasi (tanpa menghapus kode lama)

1. Pastikan folder modul sudah ada di `modules/artikel/` beserta file PHP-nya.
2. Pastikan file AJAX ada di `ajax/save_artikel.php`, `ajax/update_artikel.php`, `ajax/delete_artikel.php`.
3. Pastikan folder `uploads/artikel/` ada dan writable (chmod 755/775).
4. Jalankan migrasi database:

```bash
mysql -u USER -p NAMA_DATABASE < database/migrasi_artikel.sql
```

5. Perubahan yang sudah diterapkan pada file lama (minimal):
   - `includes/sidebar.php` — menu Artikel
   - `dashboard.php` — blok Artikel Terbaru Posyandu
   - `config/database.php` — deteksi path APP_URL untuk folder `artikel`

## Fitur

- Kader/Bidan: buat draft, kirim untuk publikasi, edit/hapus milik sendiri (belum diterbitkan)
- Admin: lihat semua, setujui / tolak artikel menunggu, edit/hapus bebas
- Dashboard menampilkan artikel status `diterbitkan`
- Upload gambar: JPG/JPEG/PNG, max 2 MB → `uploads/artikel/`

## Status artikel

| Status       | Keterangan                          |
|--------------|-------------------------------------|
| draft        | Disimpan penulis, belum dikirim     |
| menunggu     | Menunggu persetujuan admin          |
| diterbitkan  | Tampil di beranda & detail publik   |
| ditolak      | Ditolak admin, bisa diedit ulang    |
