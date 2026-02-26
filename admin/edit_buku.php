<?php
/* =================================================================
   admin/edit_buku.php — Halaman Edit Data Buku (Admin)
   Kolom tabel books: id, title, author, published_year, isbn, stock
   ================================================================= */

session_start();

// ✅ Proteksi halaman: hanya admin yang sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// ----------------------------------------------------------------
// AMBIL ID DARI URL — pastikan valid dan merupakan angka
// ----------------------------------------------------------------
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: kelola_produk.php?error=invalid_id');
    exit;
}

// ----------------------------------------------------------------
// AMBIL DATA BUKU SAAT INI DARI DATABASE
// ----------------------------------------------------------------
$stmt_get = $pdo->prepare("SELECT * FROM books WHERE id = ? LIMIT 1");
$stmt_get->execute([$id]);
$buku = $stmt_get->fetch(PDO::FETCH_ASSOC);

// Jika buku dengan ID ini tidak ditemukan, redirect
if (!$buku) {
    header('Location: kelola_produk.php?error=not_found');
    exit;
}

// ----------------------------------------------------------------
// PROSES FORM UPDATE (method POST)
// ----------------------------------------------------------------
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitasi input
    $title          = trim($_POST['title']          ?? '');
    $author         = trim($_POST['author']         ?? '');
    $published_year = trim($_POST['published_year'] ?? '');
    $isbn           = trim($_POST['isbn']           ?? '');
    $stock          = trim($_POST['stock']          ?? '');

    // --- Validasi Input ---
    if (empty($title))              $errors[] = 'Judul buku wajib diisi.';
    if (empty($author))             $errors[] = 'Nama penulis wajib diisi.';
    if (empty($published_year))     $errors[] = 'Tahun terbit wajib diisi.';
    if (!is_numeric($published_year) || $published_year < 1000 || $published_year > (int)date('Y')) {
        $errors[] = 'Tahun terbit tidak valid (format: YYYY, misal 2001).';
    }
    if (empty($isbn))               $errors[] = 'ISBN wajib diisi.';
    if (!is_numeric($stock) || (int)$stock < 0) {
        $errors[] = 'Stok harus berupa angka dan tidak boleh negatif.';
    }

    // --- Cek duplikasi ISBN (kecuali untuk buku ini sendiri) ---
    if (empty($errors)) {
        $stmt_cek = $pdo->prepare("SELECT id FROM books WHERE isbn = ? AND id != ? LIMIT 1");
        $stmt_cek->execute([$isbn, $id]);
        if ($stmt_cek->rowCount() > 0) {
            $errors[] = "ISBN <strong>{$isbn}</strong> sudah digunakan oleh buku lain.";
        }
    }

    // --- Jalankan UPDATE jika semua validasi lolos ---
    if (empty($errors)) {
        $stmt_update = $pdo->prepare(
            "UPDATE books
             SET title = ?, author = ?, published_year = ?, isbn = ?, stock = ?
             WHERE id = ?"
        );

        if ($stmt_update->execute([$title, $author, (int)$published_year, $isbn, (int)$stock, $id])) {
            // Update data lokal agar form langsung menampilkan nilai terbaru
            $buku = array_merge($buku, [
                'title'          => $title,
                'author'         => $author,
                'published_year' => $published_year,
                'isbn'           => $isbn,
                'stock'          => $stock,
            ]);
            $success = 'Data buku berhasil diperbarui!';
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.';
        }
    }
}

// ----------------------------------------------------------------
// Set variabel layout sebelum include sidebar
// ----------------------------------------------------------------
$pageTitle  = 'Edit Buku';
$activeMenu = 'kelola_produk';  // Sidebar tetap highlight menu Kelola Produk
require_once 'includes/sidebar.php';
?>

<!-- ================================================
     KONTEN HALAMAN EDIT BUKU
     ================================================ -->

<!-- Breadcrumb Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-7">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
            <a href="kelola_produk.php" class="hover:text-blue-600 transition-colors">Kelola Produk</a>
            <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
            <span class="text-slate-700 font-medium">Edit Buku</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Edit Data Buku</h1>
        <p class="text-slate-500 text-sm mt-0.5">ID Buku: <span class="font-mono font-semibold text-slate-700">#<?= $id ?></span></p>
    </div>
    <a href="kelola_produk.php"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-xl transition-colors flex-shrink-0">
        <i class="fa-solid fa-arrow-left text-xs"></i> Kembali ke Daftar
    </a>
</div>

<!-- Notifikasi Sukses -->
<?php if ($success): ?>
<div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl px-5 py-4 mb-6 animate-pulse-once">
    <i class="fa-solid fa-circle-check text-lg flex-shrink-0 mt-0.5"></i>
    <div>
        <p class="font-semibold"><?= $success ?></p>
        <a href="kelola_produk.php" class="underline text-emerald-600 hover:text-emerald-800 text-xs mt-0.5 inline-block">
            Kembali ke daftar produk →
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Notifikasi Error -->
<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
    <p class="text-red-700 text-sm font-semibold flex items-center gap-2 mb-2">
        <i class="fa-solid fa-triangle-exclamation"></i> Gagal menyimpan perubahan:
    </p>
    <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5 pl-1">
        <?php foreach ($errors as $err): ?>
            <li><?= $err ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ======================================================
     KARTU FORM EDIT
     ====================================================== -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- FORM (2/3 lebar di layar besar) -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-pen-to-square text-blue-600 text-sm"></i>
                </div>
                <h2 class="font-bold text-slate-800">Form Edit Buku</h2>
            </div>

            <form method="POST" action="edit_buku.php?id=<?= $id ?>" novalidate class="p-6 space-y-5">

                <!-- Judul Buku -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">
                        Judul Buku <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($buku['title']) ?>"
                        placeholder="Masukkan judul buku"
                        required
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
                        value="<?= htmlspecialchars($buku['author']) ?>"
                        placeholder="Masukkan nama penulis"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Tahun Terbit & ISBN (2 kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label for="published_year" class="block text-sm font-semibold text-slate-700 mb-2">
                            Tahun Terbit <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="published_year"
                            name="published_year"
                            value="<?= htmlspecialchars($buku['published_year']) ?>"
                            placeholder="Contoh: 2020"
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
                            value="<?= htmlspecialchars($buku['isbn']) ?>"
                            placeholder="Contoh: 9780743273565"
                            required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                </div>

                <!-- Stok -->
                <div>
                    <label for="stock" class="block text-sm font-semibold text-slate-700 mb-2">
                        Jumlah Stok <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <!-- Tombol kurang -->
                        <button type="button" onclick="adjustStock(-1)"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-lg flex items-center justify-center transition flex-shrink-0">
                            <i class="fa-solid fa-minus text-sm"></i>
                        </button>
                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            value="<?= (int)$buku['stock'] ?>"
                            min="0"
                            required
                            class="flex-1 px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 text-center font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <!-- Tombol tambah -->
                        <button type="button" onclick="adjustStock(1)"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-lg flex items-center justify-center transition flex-shrink-0">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">Stok minimal adalah 0.</p>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-slate-100">
                    <button
                        type="submit"
                        class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl text-sm tracking-wide transition-all duration-200 shadow-sm shadow-blue-500/30 hover:-translate-y-0.5 inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                    <a href="kelola_produk.php"
                       class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm inline-flex items-center justify-center gap-2 transition-colors">
                        <i class="fa-solid fa-xmark"></i> Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- PANEL INFO BUKU (1/3 lebar di layar besar) -->
    <div class="xl:col-span-1 space-y-4">

        <!-- Kartu Preview Data Asli -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-database text-slate-400 text-xs"></i>
                    Data Tersimpan Saat Ini
                </h3>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Judul</p>
                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($buku['title']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Penulis</p>
                    <p class="text-slate-700"><?= htmlspecialchars($buku['author']) ?></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Tahun</p>
                        <p class="text-slate-700"><?= htmlspecialchars($buku['published_year']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Stok</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                            <?= ((int)$buku['stock'] > 0) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' ?>">
                            <?= (int)$buku['stock'] ?> buku
                        </span>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">ISBN</p>
                    <p class="font-mono text-xs text-slate-600 break-all"><?= htmlspecialchars($buku['isbn']) ?></p>
                </div>
            </div>
        </div>

        <!-- Kartu Zona Bahaya: Hapus Buku -->
        <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-red-50 bg-red-50/50">
                <h3 class="font-bold text-red-600 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                    Zona Berbahaya
                </h3>
            </div>
            <div class="p-5">
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Menghapus buku ini akan menghapus datanya secara permanen dari database dan <strong>tidak dapat dibatalkan</strong>.
                </p>
                <a href="kelola_produk.php?hapus=<?= $id ?>"
                   onclick="return confirm('⚠️ Anda yakin ingin menghapus buku ini secara permanen?\n\nTindakan ini tidak dapat dibatalkan!')"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-xl transition-colors">
                    <i class="fa-solid fa-trash-can text-xs"></i> Hapus Buku Ini
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    // Tombol +/- untuk stok
    function adjustStock(delta) {
        const input = document.getElementById('stock');
        const current = parseInt(input.value) || 0;
        const newVal  = Math.max(0, current + delta);
        input.value   = newVal;
    }
</script>

<?php require_once 'includes/sidebar_close.php'; ?>
