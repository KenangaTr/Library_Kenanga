<?php
/* =================================================================
   admin/tambah_buku.php — Halaman Tambah Buku Baru (Admin)
   Kolom tabel books: id, title, author, published_year, isbn, stock
   ================================================================= */

session_start();

// ✅ Proteksi halaman
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$errors  = [];
$success = '';

// ================================================================
//  PROSES FORM INSERT (method POST)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitasi input
    $title          = trim($_POST['title']          ?? '');
    $author         = trim($_POST['author']         ?? '');
    $published_year = trim($_POST['published_year'] ?? '');
    $isbn           = trim($_POST['isbn']           ?? '');
    $stock          = trim($_POST['stock']          ?? '0');

    // --- Validasi ---
    if (empty($title))          $errors[] = 'Judul buku wajib diisi.';
    if (empty($author))         $errors[] = 'Nama penulis wajib diisi.';
    if (empty($published_year)) $errors[] = 'Tahun terbit wajib diisi.';
    if (!is_numeric($published_year) || (int)$published_year < 1000 || (int)$published_year > (int)date('Y')) {
        $errors[] = 'Tahun terbit tidak valid (format YYYY, misal: 2001).';
    }
    if (empty($isbn))           $errors[] = 'ISBN wajib diisi.';
    if (!is_numeric($stock) || (int)$stock < 0) {
        $errors[] = 'Stok harus berupa angka dan tidak boleh negatif.';
    }

    // --- Cek duplikasi ISBN ---
    if (empty($errors)) {
        $stmt_cek = $pdo->prepare("SELECT id FROM books WHERE isbn = ? LIMIT 1");
        $stmt_cek->execute([$isbn]);
        if ($stmt_cek->rowCount() > 0) {
            $errors[] = "ISBN <strong>{$isbn}</strong> sudah digunakan oleh buku lain.";
        }
    }

    // --- INSERT ke database jika semua lolos ---
    if (empty($errors)) {
        $stmt_insert = $pdo->prepare(
            "INSERT INTO books (title, author, published_year, isbn, stock)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt_insert->execute([$title, $author, (int)$published_year, $isbn, (int)$stock])) {
            $new_id  = $pdo->lastInsertId();
            $success = true;
            // Redirect ke kelola_produk dengan notifikasi sukses
            header("Location: kelola_produk.php?added=1&title=" . urlencode($title));
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.';
        }
    }
}

// ----------------------------------------------------------------
// Set variabel layout
// ----------------------------------------------------------------
$pageTitle  = 'Tambah Buku';
$activeMenu = 'kelola_produk';
require_once 'includes/sidebar.php';
?>

<!-- ================================================
     KONTEN HALAMAN TAMBAH BUKU
     ================================================ -->

<!-- Breadcrumb + Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-7">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
            <a href="kelola_produk.php" class="hover:text-blue-600 transition-colors">Kelola Produk</a>
            <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
            <span class="text-slate-700 font-medium">Tambah Buku</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Tambah Buku Baru</h1>
        <p class="text-slate-500 text-sm mt-0.5">Isi semua data buku di bawah ini lalu klik Simpan.</p>
    </div>
    <a href="kelola_produk.php"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-xl transition-colors flex-shrink-0">
        <i class="fa-solid fa-arrow-left text-xs"></i> Kembali ke Daftar
    </a>
</div>

<!-- Notifikasi Error -->
<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
    <p class="text-red-700 text-sm font-semibold flex items-center gap-2 mb-2">
        <i class="fa-solid fa-triangle-exclamation"></i> Gagal menyimpan buku baru:
    </p>
    <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5 pl-1">
        <?php foreach ($errors as $err): ?>
            <li><?= $err ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ======================================================
     LAYOUT: Form (2/3) + Tips (1/3)
     ====================================================== -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- FORM UTAMA -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-book-medical text-blue-600 text-sm"></i>
                </div>
                <h2 class="font-bold text-slate-800">Data Buku Baru</h2>
            </div>

            <form method="POST" action="tambah_buku.php" novalidate class="p-6 space-y-5">

                <!-- Judul Buku -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">
                        Judul Buku <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        placeholder="Masukkan judul buku lengkap"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Nama Penulis -->
                <div>
                    <label for="author" class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Penulis <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="author"
                        name="author"
                        value="<?= htmlspecialchars($_POST['author'] ?? '') ?>"
                        placeholder="Contoh: J.K. Rowling"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Tahun Terbit & ISBN -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="published_year" class="block text-sm font-semibold text-slate-700 mb-2">
                            Tahun Terbit <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="published_year"
                            name="published_year"
                            value="<?= htmlspecialchars($_POST['published_year'] ?? '') ?>"
                            placeholder="Contoh: <?= date('Y') ?>"
                            min="1000"
                            max="<?= date('Y') ?>"
                            required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="isbn" class="block text-sm font-semibold text-slate-700 mb-2">
                            ISBN <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="isbn"
                            name="isbn"
                            value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>"
                            placeholder="Contoh: 9780743273565"
                            required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Stok -->
                <div>
                    <label for="stock" class="block text-sm font-semibold text-slate-700 mb-2">
                        Jumlah Stok Awal <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="adjustStock(-1)"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition flex-shrink-0">
                            <i class="fa-solid fa-minus text-sm"></i>
                        </button>
                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>"
                            min="0"
                            required
                            class="flex-1 px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 text-center font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <button type="button" onclick="adjustStock(1)"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition flex-shrink-0">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">Masukkan 0 jika buku belum tersedia di rak.</p>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-slate-100">
                    <button
                        type="submit"
                        class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl text-sm shadow-sm shadow-blue-500/30 hover:-translate-y-0.5 transition-all duration-200 inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Buku
                    </button>
                    <button
                        type="reset"
                        onclick="document.getElementById('stock').value=0"
                        class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left"></i> Reset Form
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- PANEL KANAN: Tips & Info -->
    <div class="xl:col-span-1 space-y-4">

        <!-- Tips Pengisian -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-500 text-sm"></i> Panduan Pengisian
                </h3>
            </div>
            <div class="p-5 space-y-4 text-sm text-slate-600">
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</div>
                    <div>
                        <p class="font-semibold text-slate-700">Judul & Penulis</p>
                        <p class="text-xs text-slate-500 mt-0.5">Masukkan nama lengkap sesuai halaman sampul buku.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</div>
                    <div>
                        <p class="font-semibold text-slate-700">ISBN</p>
                        <p class="text-xs text-slate-500 mt-0.5">Nomor ISBN biasanya tertera di belakang buku (barcode). Terdiri dari 10 atau 13 digit angka.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</div>
                    <div>
                        <p class="font-semibold text-slate-700">Stok</p>
                        <p class="text-xs text-slate-500 mt-0.5">Jumlah eksemplar fisik yang tersedia di perpustakaan saat ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Struktur Tabel books (referensi) -->
        <div class="bg-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-white/10 flex items-center gap-2">
                <i class="fa-solid fa-table text-slate-400 text-xs"></i>
                <p class="text-slate-300 text-xs font-semibold uppercase tracking-wider">Tabel: books</p>
            </div>
            <div class="p-5 font-mono text-xs space-y-1.5">
                <p class="text-slate-500">-- Kolom yang akan diisi:</p>
                <p class="text-emerald-400">title         <span class="text-slate-500">VARCHAR(255)</span></p>
                <p class="text-emerald-400">author        <span class="text-slate-500">VARCHAR(255)</span></p>
                <p class="text-emerald-400">published_year <span class="text-slate-500">INT</span></p>
                <p class="text-emerald-400">isbn          <span class="text-slate-500">VARCHAR(20) UNIQUE</span></p>
                <p class="text-emerald-400">stock         <span class="text-slate-500">INT DEFAULT 0</span></p>
                <p class="text-slate-600 mt-2">-- Auto, tidak perlu diisi:</p>
                <p class="text-slate-500">id            <span class="text-slate-600">AUTO_INCREMENT</span></p>
            </div>
        </div>

    </div>
</div>

<script>
    function adjustStock(delta) {
        const input  = document.getElementById('stock');
        const newVal = Math.max(0, (parseInt(input.value) || 0) + delta);
        input.value  = newVal;
    }
</script>

<?php require_once 'includes/sidebar_close.php'; ?>
