<?php
/* =================================================================
   admin/kelola_produk.php — Halaman Kelola Produk/Buku Admin
   ================================================================= */

// ✅ Proteksi halaman
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

/* ---------------------------------------------------------------
   🔧 HANDLE HAPUS PRODUK (Inline delete via GET)
   Untuk keamanan lebih, gunakan metode POST + token CSRF.
   --------------------------------------------------------------- */
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    // 🔧 Sisipkan query DELETE Anda di sini:
    $stmt_hapus = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt_hapus->execute([$id_hapus]);
    header('Location: kelola_produk.php?deleted=1');
    exit;
}

/* ---------------------------------------------------------------
   🔧 QUERY READ: Ambil semua buku dari database
   Sesuaikan nama tabel dan kolom dengan skema Anda.
   Tambahkan WHERE / ORDER BY / LIMIT sesuai kebutuhan.
   --------------------------------------------------------------- */
$search_admin = trim($_GET['q'] ?? '');

if ($search_admin) {
    $stmt_produk = $pdo->prepare("SELECT * FROM books WHERE title LIKE :q OR author LIKE :q ORDER BY id DESC");
    $stmt_produk->execute([':q' => "%$search_admin%"]);
} else {
    $stmt_produk = $pdo->query("SELECT * FROM books ORDER BY id DESC");
}
$all_books = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------------
// Set variabel layout
// ---------------------------------------------------------------
$pageTitle  = 'Kelola Produk';
$activeMenu = 'kelola_produk';
require_once 'includes/sidebar.php';
?>

<!-- ================================================
     KONTEN HALAMAN KELOLA PRODUK
     ================================================ -->

<!-- Header Konten: Judul + Tombol Tambah -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Kelola Produk</h1>
        <p class="text-slate-500 text-sm mt-0.5">Manajemen data buku perpustakaan.</p>
    </div>
    <a href="tambah_buku.php"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm shadow-blue-500/30 transition-all hover:-translate-y-0.5 flex-shrink-0">
        <i class="fa-solid fa-plus"></i> Tambah Produk Baru
    </a>
</div>

<!-- Notifikasi -->
<?php if (isset($_GET['deleted'])): ?>
<div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-5 py-3.5 mb-5">
    <i class="fa-solid fa-circle-check flex-shrink-0"></i>
    <span>Produk berhasil dihapus.</span>
</div>
<?php endif; ?>

<!-- ------------------------------------------------
     TOOLBAR: Search + Filter
     ------------------------------------------------ -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
        <!-- Search -->
        <form action="kelola_produk.php" method="GET" class="flex gap-2 w-full sm:max-w-sm">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm"></i>
                </div>
                <input
                    type="text"
                    name="q"
                    value="<?= htmlspecialchars($search_admin) ?>"
                    placeholder="Cari judul atau penulis..."
                    class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">Cari</button>
            <?php if ($search_admin): ?>
            <a href="kelola_produk.php" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Info jumlah -->
        <p class="text-xs text-slate-400 flex-shrink-0">
            Menampilkan <span class="font-semibold text-slate-600"><?= count($all_books) ?></span> produk
        </p>
    </div>

    <!-- ------------------------------------------------
         TABEL DATA PRODUK
         ------------------------------------------------ -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-left text-xs uppercase tracking-wider border-b border-slate-100">
                    <th class="px-5 py-4 font-semibold w-10">No</th>
                    <th class="px-5 py-4 font-semibold w-16">Gambar</th>
                    <th class="px-5 py-4 font-semibold">Nama Produk / Judul</th>
                    <th class="px-5 py-4 font-semibold">Penulis / Kategori</th>
                    <th class="px-5 py-4 font-semibold">Tahun</th>
                    <th class="px-5 py-4 font-semibold text-center">Stok</th>
                    <th class="px-5 py-4 font-semibold text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">

                <!-- ⬇️ PHP LOOP START: Tabel Kelola Produk ⬇️ -->
                <!-- Ganti foreach di bawah dengan while($row = mysqli_fetch_assoc($result)) jika menggunakan MySQLi -->
                <?php if (count($all_books) > 0): ?>
                    <?php foreach ($all_books as $no => $row): ?>
                    <tr class="hover:bg-blue-50/40 transition-colors group">

                        <!-- No Urut -->
                        <td class="px-5 py-4 text-slate-400 font-medium"><?= $no + 1 ?></td>

                        <!-- Gambar Cover -->
                        <!-- 🔧 Jika ada kolom 'cover', ganti src dengan: htmlspecialchars($row['cover']) -->
                        <td class="px-5 py-4">
                            <div class="w-10 h-14 bg-gradient-to-br from-slate-200 to-slate-300 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0">
                                <?php if (!empty($row['cover'])): ?>
                                    <img src="../uploads/covers/<?= htmlspecialchars($row['cover']) ?>" alt="cover" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fa-regular fa-image text-slate-400 text-base"></i>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Nama Produk / Judul -->
                        <!-- 🔧 Ganti dengan: <?= htmlspecialchars($row['title']); ?> -->
                        <td class="px-5 py-4">
                            <span class="font-semibold text-slate-800 line-clamp-1 block max-w-[220px]">
                                <?= htmlspecialchars($row['title']) ?>
                            </span>
                            <span class="text-slate-400 text-xs">ISBN: <?= htmlspecialchars($row['isbn'] ?? '-') ?></span>
                        </td>

                        <!-- Penulis / Kategori -->
                        <!-- 🔧 Ganti dengan: <?= htmlspecialchars($row['author']); ?> -->
                        <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($row['author']) ?></td>

                        <!-- Tahun Terbit -->
                        <!-- 🔧 Ganti dengan: <?= htmlspecialchars($row['published_year']); ?> -->
                        <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($row['published_year'] ?? '-') ?></td>

                        <!-- Stok (Badge) -->
                        <!-- 🔧 Ganti dengan: <?= (int)$row['stock']; ?> -->
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold
                                <?= ((int)$row['stock'] > 0) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' ?>">
                                <?= (int)$row['stock'] ?>
                            </span>
                        </td>

                        <!-- Tombol Aksi: Edit & Hapus -->
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Edit -->
                                <!-- 🔧 URL mengarah ke halaman edit admin dengan ID buku: -->
                                <a href="edit_buku.php?id=<?= $row['id'] ?>"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square text-[11px]"></i> Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <!-- 🔧 URL mengarah ke halaman ini dengan parameter hapus=ID: -->
                                <a href="kelola_produk.php?hapus=<?= $row['id'] ?>"
                                   onclick="return confirm('Yakin ingin menghapus buku ini? Tindakan ini tidak dapat dibatalkan.')"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash text-[11px]"></i> Hapus
                                </a>
                            </div>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <!-- Kondisi data kosong -->
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-5xl mb-3"></i>
                            <p class="font-semibold text-base">
                                <?= $search_admin ? 'Produk tidak ditemukan' : 'Belum ada data produk' ?>
                            </p>
                            <p class="text-sm mt-1">
                                <?= $search_admin ? "Coba kata kunci lain." : "Klik \"Tambah Produk Baru\" untuk memulai." ?>
                            </p>
                            <?php if (!$search_admin): ?>
                            <a href="tambah_buku.php" class="mt-5 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                                + Tambah Produk
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <!-- ⬆️ PHP LOOP END ⬆️ -->

            </tbody>
        </table>
    </div>

    <!-- Footer tabel -->
    <?php if (count($all_books) > 0): ?>
    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50 text-xs text-slate-400">
        Total: <span class="font-semibold text-slate-600"><?= count($all_books) ?></span> produk ditemukan.
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/sidebar_close.php'; ?>
