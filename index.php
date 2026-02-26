<?php
/* ============================================================
   index.php — Halaman Utama Library Kenanga
   ============================================================ */
require_once 'config/database.php';

// --- Query: Buku Terbaru (Section 1) ---
$stmt_recent = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 10");
$recent_books = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// --- Query: Buku Populer (Section 3) ---
$stmt_popular = $pdo->query("SELECT * FROM books ORDER BY id ASC LIMIT 8");
$popular_books = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);

// --- Query: Our Collection (Section 4) ---
$stmt_collection = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 4");
$collection_books = $stmt_collection->fetchAll(PDO::FETCH_ASSOC);

// --- Handle Search ---
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt_search = $pdo->prepare("SELECT * FROM books WHERE title LIKE :s OR author LIKE :s OR isbn LIKE :s ORDER BY id DESC");
    $stmt_search->execute([':s' => "%$search%"]);
    $recent_books  = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    $popular_books = $recent_books;
}

/* Helper: Cover buku – placeholder dinamis berdasarkan warna tema */
function getBookCover(array $row): string {
    // Jika ada kolom cover:
    // return !empty($row['cover']) ? 'uploads/covers/' . htmlspecialchars($row['cover']) : '';
    $colors = ['be185d','f0609e','ff88ba','c4497a','7c3aed','b45309','0f766e','1d4ed8'];
    $c = $colors[$row['id'] % count($colors)];
    return "https://placehold.co/300x400/{$c}/ffffff?text=" . urlencode(mb_substr($row['title'], 0, 15));
}
?>
<?php require_once 'includes/header.php'; ?>

    <main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Banner Hasil Pencarian -->
        <?php if ($search): ?>
        <div class="mb-8 flex items-center justify-between rounded-2xl px-6 py-4"
             style="background:var(--clr-accent-soft); border:1.5px solid var(--clr-accent);">
            <p class="text-sm font-medium" style="color:var(--clr-accent-dark);">
                Hasil pencarian: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                (<?= count($recent_books) ?> buku ditemukan)
            </p>
            <a href="index.php" class="text-sm font-bold underline" style="color:var(--clr-accent-dark);">Reset</a>
        </div>
        <?php endif; ?>

        <!-- ============================================
             SECTION 1: Based on your recent reads
             ============================================ -->
        <section class="mb-14">
            <h2 class="text-[22px] font-extrabold section-title mb-6">
                <?= $search ? '🔍 Hasil Pencarian' : '📖 Buku Terbaru' ?>
            </h2>

            <?php if (count($recent_books) > 0): ?>
            <div class="flex overflow-x-auto gap-5 pb-4 hide-scrollbar snap-x">

                <?php foreach ($recent_books as $row): ?>
                <div class="flex-none w-[190px] snap-start">
                    <div class="book-card p-3.5 h-full flex flex-col cursor-pointer">
                        <!-- Cover -->
                        <div class="aspect-[3/4] rounded-xl overflow-hidden mb-3.5 flex-shrink-0"
                             style="background:var(--clr-base);">
                            <img src="<?= getBookCover($row) ?>"
                                 alt="<?= htmlspecialchars($row['title']) ?>"
                                 class="w-full h-full object-cover cover-zoom">
                        </div>
                        <div class="flex flex-col flex-grow px-0.5">
                            <h3 class="font-bold text-[15px] line-clamp-2 leading-[1.3] mb-1.5 section-title">
                                <?= htmlspecialchars($row['title']) ?>
                            </h3>
                            <p class="text-[13px] mt-auto" style="color:var(--clr-accent);">
                                <?= htmlspecialchars($row['author']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
            <?php else: ?>
            <div class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border-2 border-dashed"
                 style="border-color:var(--clr-base-dark); background:#fff;">
                <i class="fa-regular fa-bookmark text-5xl mb-4" style="color:var(--clr-accent-soft);"></i>
                <p class="font-bold text-lg section-title">Belum ada data buku</p>
                <p class="text-sm mt-1 mb-5" style="color:#b08a9a;">Mulai tambahkan buku pertama ke koleksi Anda.</p>
                <a href="admin/tambah_buku.php" class="btn-accent px-6 py-2.5 text-sm">+ Tambah Buku</a>
            </div>
            <?php endif; ?>
        </section>


        <!-- ============================================
             SECTION 2: Filter Pills
             ============================================ -->
        <?php if (!$search): ?>
        <section class="mb-8">
            <div class="flex flex-wrap gap-3">
                <button class="filter-active px-7 py-2.5 rounded-full font-bold text-sm">Most Read</button>
                <button class="filter-inactive px-7 py-2.5 rounded-full font-semibold text-sm transition-colors">Trending</button>
                <button class="filter-inactive px-7 py-2.5 rounded-full font-semibold text-sm transition-colors">Newest</button>
                <button class="filter-inactive px-7 py-2.5 rounded-full font-semibold text-sm transition-colors">Recommended</button>
            </div>
        </section>


        <!-- ============================================
             SECTION 3: Main Book Grid (Populer)
             ============================================ -->
        <section class="mb-14">
            <?php if (count($popular_books) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-10">

                <?php foreach ($popular_books as $i => $row): ?>
                <div class="group cursor-pointer">
                    <!-- Cover + Badge Stok -->
                    <div class="relative aspect-[3/4] rounded-xl overflow-hidden mb-3.5 shadow-sm group-hover:shadow-md transition-all duration-300">
                        <img src="<?= getBookCover($row) ?>"
                             alt="<?= htmlspecialchars($row['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <!-- Badge stok -->
                        <div class="absolute top-3 right-0 text-white text-[11px] font-bold pl-3.5 pr-3 py-1.5 rounded-l-full flex items-center shadow-md badge-readers">
                            <i class="fa-solid fa-glasses mr-1.5 text-[10px]"></i>
                            <?= number_format((int)($row['stock'] ?? 0)) ?>
                        </div>
                    </div>
                    <!-- Info -->
                    <div class="px-1 mt-1">
                        <p class="text-[12px] mb-1.5 font-semibold" style="color:var(--clr-accent);">
                            <?= htmlspecialchars($row['author']) ?>
                        </p>
                        <div class="flex justify-between items-start gap-3">
                            <h3 class="font-extrabold text-[16px] leading-[1.3] line-clamp-2 section-title">
                                <?= htmlspecialchars($row['title']) ?>
                            </h3>
                            <div class="flex items-center text-[12px] font-bold flex-shrink-0 mt-0.5" style="color:var(--clr-text);">
                                <i class="fa-solid fa-star text-yellow-400 text-[10px] mr-1"></i> 5.0
                            </div>
                        </div>
                        <p class="text-[11px] mt-1" style="color:#b08a9a;"><?= htmlspecialchars($row['published_year'] ?? '') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
            <?php else: ?>
            <div class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border-2 border-dashed"
                 style="border-color:var(--clr-base-dark); background:#fff;">
                <i class="fa-regular fa-face-meh text-5xl mb-4" style="color:var(--clr-accent-soft);"></i>
                <p class="font-bold text-lg section-title">Belum ada buku populer</p>
            </div>
            <?php endif; ?>
        </section>


        <!-- ============================================
             SECTION 4: Our Collection
             ============================================ -->
        <section class="mb-12 pt-8" style="border-top:2px solid var(--clr-base-dark);">
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-[22px] font-extrabold section-title">Our Collection</h2>
                <a href="index.php" class="text-[14px] font-bold transition-colors"
                   style="color:var(--clr-accent-dark);">Browse all books →</a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($collection_books as $row): ?>
                <div class="aspect-[3/4] rounded-xl overflow-hidden relative group cursor-pointer shadow-sm block"
                     style="border:1.5px solid var(--clr-base-dark);">
                    <img src="<?= getBookCover($row) ?>"
                         alt="<?= htmlspecialchars($row['title']) ?>"
                         class="w-full h-full object-cover cover-zoom group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3"
                         style="background:linear-gradient(to top, rgba(61,43,31,.7) 0%, transparent 100%);">
                        <p class="text-white text-[13px] font-bold line-clamp-2">
                            <?= htmlspecialchars($row['title']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (count($collection_books) === 0): ?>
                <div class="col-span-4 py-10 text-center" style="color:#b08a9a;">Belum ada koleksi buku.</div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; // end !$search ?>

    </main>

    <!-- Footer -->
    <footer class="site-footer mt-6">
        <div class="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center text-sm" style="color:#b08a9a;">
            <span>📚 Library Kenanga &copy; <?= date('Y') ?></span>
            <a href="admin/login.php" class="font-bold transition-colors" style="color:var(--clr-accent-dark);">Panel Admin →</a>
        </div>
    </footer>

</body>
</html>
