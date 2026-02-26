<?php
/* =================================================================
   admin/admin_dashboard.php — Halaman Dashboard Admin
   ================================================================= */

// ✅ Proteksi halaman: cek apakah sudah login
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

/* ---------------------------------------------------------------
   🔧 QUERY STATISTIK — Sisipkan query COUNT Anda di sini
   ---------------------------------------------------------------
   Ganti nilai dummy di bawah dengan hasil query nyata.
   Contoh PDO:
     $total_buku      = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
     $total_kategori  = $pdo->query("SELECT COUNT(DISTINCT author) FROM books")->fetchColumn();
     $buku_tersedia   = $pdo->query("SELECT COUNT(*) FROM books WHERE stock > 0")->fetchColumn();
     $buku_habis      = $pdo->query("SELECT COUNT(*) FROM books WHERE stock = 0")->fetchColumn();
   --------------------------------------------------------------- */
$total_buku     = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$buku_tersedia  = $pdo->query("SELECT COUNT(*) FROM books WHERE stock > 0")->fetchColumn();
$buku_habis     = $pdo->query("SELECT COUNT(*) FROM books WHERE stock = 0")->fetchColumn();
$total_penulis  = $pdo->query("SELECT COUNT(DISTINCT author) FROM books")->fetchColumn();

// ---------------------------------------------------------------
// Set variabel layout sebelum include sidebar
// ---------------------------------------------------------------
$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
require_once 'includes/sidebar.php';
?>

<!-- ================================================
     KONTEN DASHBOARD
     ================================================ -->

<!-- Judul Halaman -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-slate-500 text-sm mt-1">Selamat datang kembali, ringkasan data perpustakaan Anda.</p>
</div>

<!-- ------------------------------------------------
     SUMMARY CARDS (Grid 4 kolom)
     ------------------------------------------------ -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">

    <!-- Card 1: Total Buku -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Buku</p>
                <!-- 🔧 Echo nilai dari query COUNT: -->
                <p class="text-3xl font-bold text-slate-900"><?= number_format($total_buku) ?></p>
                <p class="text-xs text-slate-400 mt-1.5">entri di database</p>
            </div>
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-book text-blue-600 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Card 2: Buku Tersedia (stock > 0) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Buku Tersedia</p>
                <!-- 🔧 Echo nilai dari query COUNT stock > 0: -->
                <p class="text-3xl font-bold text-slate-900"><?= number_format($buku_tersedia) ?></p>
                <p class="text-xs text-emerald-500 mt-1.5 font-medium">&#9679; Stok masih ada</p>
            </div>
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Card 3: Stok Habis -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Stok Habis</p>
                <!-- 🔧 Echo nilai dari query COUNT stock = 0: -->
                <p class="text-3xl font-bold text-slate-900"><?= number_format($buku_habis) ?></p>
                <p class="text-xs text-red-400 mt-1.5 font-medium">&#9679; Perlu restok</p>
            </div>
            <div class="w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Penulis Unik -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Penulis</p>
                <!-- 🔧 Echo nilai dari query COUNT DISTINCT author: -->
                <p class="text-3xl font-bold text-slate-900"><?= number_format($total_penulis) ?></p>
                <p class="text-xs text-slate-400 mt-1.5">penulis unik</p>
            </div>
            <div class="w-11 h-11 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-feather-pointed text-violet-600 text-lg"></i>
            </div>
        </div>
    </div>

</div>

<!-- ------------------------------------------------
     TABEL BUKU TERBARU (Preview 5 buku terakhir)
     ------------------------------------------------ -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <h2 class="font-bold text-slate-800 text-base">Buku Terbaru Ditambahkan</h2>
        <a href="kelola_produk.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">
            Lihat Semua <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
        </a>
    </div>

    <?php
    /* ---------------------------------------------------------------
       🔧 QUERY TABEL BUKU TERBARU
       Ganti dengan query Anda jika perlu kolom yang berbeda:
         $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 5");
         $recent_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
       --------------------------------------------------------------- */
    $stmt_dash = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 5");
    $dash_books = $stmt_dash->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-left text-xs uppercase tracking-wider">
                    <th class="px-6 py-3.5 font-semibold">Judul</th>
                    <th class="px-6 py-3.5 font-semibold">Penulis</th>
                    <th class="px-6 py-3.5 font-semibold">Tahun</th>
                    <th class="px-6 py-3.5 font-semibold text-center">Stok</th>
                    <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">

                <!-- ⬇️ PHP LOOP START: Tabel Buku Dashboard ⬇️ -->
                <?php if (count($dash_books) > 0): ?>
                    <?php foreach ($dash_books as $row): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-800 max-w-[220px] truncate"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($row['author']) ?></td>
                        <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($row['published_year'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                <?= ($row['stock'] > 0) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' ?>">
                                <?= (int)$row['stock'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="edit_buku.php?id=<?= $row['id'] ?>"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                            Belum ada data buku.
                        </td>
                    </tr>
                <?php endif; ?>
                <!-- ⬆️ PHP LOOP END ⬆️ -->

            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/sidebar_close.php'; ?>
