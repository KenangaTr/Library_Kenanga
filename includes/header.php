<?php
/* =================================================================
   includes/header.php
   Komponen: <head>, stylesheet, dan Top Navigation Bar (Navbar)

   Variabel opsional sebelum include:
     $pageTitle (string) — judul tab browser
     $search    (string) — kata kunci pencarian aktif
   ================================================================= */

$pageTitle = $pageTitle ?? 'Library Kenanga';
$search    = $search    ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Google Fonts: Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Tokens warna utama ── */
        :root {
            --clr-base:        #FFF2D0;
            --clr-base-dark:   #FFE0A0;
            --clr-accent:      #FF88BA;
            --clr-accent-dark: #F0609E;
            --clr-accent-soft: #FFDEED;
            --clr-text:        #3D2B1F;
        }

        /* ── Global ── */
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--clr-base);
            color: var(--clr-text);
        }

        /* ── Scrollbar kartu horizontal ── */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ── Navbar ── */
        .site-header {
            background: #fff;
            border-bottom: 2px solid var(--clr-base-dark);
            box-shadow: 0 4px 24px rgba(255,136,186,.10);
        }

        /* ── Logo icon ── */
        .logo-icon {
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
            box-shadow: 0 4px 10px rgba(255,136,186,.35);
        }

        /* ── Search bar ── */
        .search-input {
            background-color: var(--clr-base);
            border: 1.5px solid var(--clr-base-dark);
            color: var(--clr-text);
            transition: border-color .2s, box-shadow .2s, background-color .2s;
        }
        .search-input::placeholder { color: #c4a882; }
        .search-input:hover  { background: #fff; border-color: var(--clr-accent); }
        .search-input:focus  {
            outline: none;
            background: #fff;
            border-color: var(--clr-accent);
            box-shadow: 0 0 0 3px rgba(255,136,186,.20);
        }

        /* ── Nav pills ── */
        .nav-active {
            background-color: var(--clr-accent-soft);
            color: var(--clr-accent-dark);
            font-weight: 700;
        }
        .nav-inactive { color: #b08a9a; }
        .nav-inactive:hover {
            background-color: var(--clr-base);
            color: var(--clr-accent-dark);
        }

        /* ── Kartu buku (section 1 & grid) ── */
        .book-card {
            background: #fff;
            border: 1.5px solid var(--clr-base-dark);
            border-radius: 1.1rem;
            box-shadow: 0 4px 14px rgba(255,136,186,.08);
            transition: transform .25s, box-shadow .25s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(255,136,186,.22);
        }

        /* ── Tombol utama ── */
        .btn-accent {
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
            color: #fff;
            border-radius: 9999px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(255,136,186,.35);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(255,136,186,.45);
        }

        /* ── Filter pills ── */
        .filter-active {
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
            color: #fff;
            box-shadow: 0 4px 10px rgba(255,136,186,.30);
        }
        .filter-inactive {
            background: #fff;
            color: #b08a9a;
            border: 1.5px solid var(--clr-base-dark);
        }
        .filter-inactive:hover {
            border-color: var(--clr-accent);
            color: var(--clr-accent-dark);
        }

        /* ── Badge readers (kacamata) ── */
        .badge-readers {
            background: linear-gradient(135deg, #00C85A, #009E45);
            box-shadow: 0 3px 8px rgba(0,180,76,.35);
        }

        /* ── Judul section ── */
        .section-title { color: var(--clr-text); }

        /* ── Footer ── */
        .site-footer {
            background: #fff;
            border-top: 2px solid var(--clr-base-dark);
        }
    </style>
</head>
<body>

    <!-- =============================================
         TOP NAVIGATION BAR
         ============================================= -->
    <header class="site-header sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">

                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-[22px] font-extrabold tracking-tight flex items-center gap-2.5">
                        <div class="logo-icon w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-book-open text-white text-sm"></i>
                        </div>
                        <span>
                            <span style="color:var(--clr-accent)">Lib</span><span style="color:var(--clr-text)">Ken</span>
                        </span>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl mx-8 hidden md:block">
                    <form action="index.php" method="GET">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-sm" style="color:var(--clr-accent)"></i>
                            </div>
                            <input
                                type="text"
                                name="search"
                                value="<?= htmlspecialchars($search) ?>"
                                class="search-input block w-full pl-11 pr-4 py-3 rounded-full text-sm"
                                placeholder="Cari judul, penulis, atau ISBN...">
                        </div>
                    </form>
                </div>

                <!-- Menu Navigasi -->
                <nav class="flex items-center space-x-2">
                    <a href="index.php"
                       class="flex items-center px-5 py-2.5 rounded-full text-sm transition-colors
                              <?= (!$search && basename($_SERVER['PHP_SELF']) === 'index.php') ? 'nav-active' : 'nav-inactive' ?>">
                        <i class="fa-solid fa-house mr-2 text-xs"></i> Home
                    </a>
                    <a href="index.php?view=all"
                       class="flex items-center px-5 py-2.5 rounded-full text-sm transition-colors
                              <?= (isset($_GET['view']) && $_GET['view'] === 'all') ? 'nav-active' : 'nav-inactive' ?>">
                        <i class="fa-regular fa-file-lines mr-2 text-xs"></i> Collection
                    </a>

                    <!-- Tombol Login Portal -->
                    <a href="admin/login.php"
                       class="flex items-center px-5 py-2.5 rounded-full text-sm font-bold text-white transition-all hover:-translate-y-0.5"
                       style="background:linear-gradient(135deg,var(--clr-accent),var(--clr-accent-dark));box-shadow:0 4px 12px rgba(255,136,186,.35);">
                        <i class="fa-solid fa-right-to-bracket mr-2 text-xs"></i> Portal
                    </a>
                </nav>

            </div>
        </div>
    </header>
    <!-- / END NAVIGATION BAR -->
