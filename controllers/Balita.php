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
 * Controller Balita – pola MVC ringan ala OpenSID
 * Data penduduk hanya dari OpenSID (tidak input manual nama/NIK)
 */
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../helpers/opensid_helper.php';
require_once __DIR__ . '/../models/Balita_model.php';

class Balita
{
    protected $model;

    public function __construct()
    {
        requireLogin();
        $this->model = new Balita_model();
    }

    public function index()
    {
        $page_title = 'Data Balita';
        $list       = $this->model->getAll();
        $total      = count($list);

        require_once __DIR__ . '/../includes/header.php';
        // Gunakan view lama modul agar UI tetap sama
        // Atau views/balita/index.php jika sudah dibuat
        $data = $list; // kompatibilitas view lama
        if (file_exists(__DIR__ . '/../views/balita/index.php')) {
            require __DIR__ . '/../views/balita/index.php';
        } else {
            // Fallback ke modul lama
            require __DIR__ . '/../modules/balita/index.php';
        }
        require_once __DIR__ . '/../includes/footer.php';
    }

    public function tambah()
    {
        if (!canInput()) {
            header('Location: ' . APP_URL . '/dashboard.php');
            exit;
        }
        $page_title = 'Tambah Balita';
        $mode       = 'tambah';

        require_once __DIR__ . '/../includes/header.php';
        if (file_exists(__DIR__ . '/../views/balita/form.php')) {
            require __DIR__ . '/../views/balita/form.php';
        } else {
            require __DIR__ . '/../modules/balita/tambah.php';
        }
        require_once __DIR__ . '/../includes/footer.php';
    }

    /**
     * Simpan – WAJIB id_penduduk_opensid dari pencarian OpenSID
     */
    public function simpan()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !canInput()) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            exit;
        }

        $id_penduduk = (int)($_POST['id_penduduk_opensid'] ?? 0);
        if ($id_penduduk <= 0) {
            // Izinkan mode manual hanya jika bayi belum punya NIK (status manual)
            $manual = !empty($_POST['status_integrasi']) && $_POST['status_integrasi'] === 'manual';
            if (!$manual) {
                echo json_encode(['success' => false, 'message' => 'Pilih penduduk dari OpenSID terlebih dahulu']);
                exit;
            }
        }

        $payload = [];
        if ($id_penduduk > 0) {
            $p = pendudukUntukForm($id_penduduk);
            if (!$p) {
                echo json_encode(['success' => false, 'message' => 'Data penduduk tidak ditemukan di OpenSID']);
                exit;
            }
            $payload = $p;
        } else {
            // Manual (bayi tanpa NIK)
            $payload = [
                'nama'             => trim($_POST['nama'] ?? ''),
                'jenis_kelamin'    => $_POST['jenis_kelamin'] ?? null,
                'tanggal_lahir'    => $_POST['tanggal_lahir'] ?? null,
                'nama_ibu'         => trim($_POST['nama_ibu'] ?? ''),
                'status_integrasi' => 'manual',
            ];
            if ($payload['nama'] === '') {
                echo json_encode(['success' => false, 'message' => 'Nama wajib diisi untuk data manual']);
                exit;
            }
        }

        $payload['created_by'] = currentUser()['id'] ?? null;
        if (!empty($_POST['nomor_peserta'])) {
            $payload['nomor_peserta'] = trim($_POST['nomor_peserta']);
        }

        $id = $this->model->insert($payload);
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Data balita berhasil disimpan', 'data' => ['id' => $id]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
        }
        exit;
    }
}

// Auto-dispatch jika dipanggil langsung
if (basename($_SERVER['SCRIPT_FILENAME']) === 'Balita.php') {
    $m = $_GET['m'] ?? 'index';
    $c = new Balita();
    if (method_exists($c, $m)) {
        $c->$m();
    } else {
        $c->index();
    }
}
