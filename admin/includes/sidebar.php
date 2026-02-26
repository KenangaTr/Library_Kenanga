<?php
/* =================================================================
   admin/includes/sidebar.php
   Layout utama admin: Sidebar (fixed, kiri) + Header (sticky, atas)

   Set sebelum include:
     $pageTitle  (string) — Judul tab
     $activeMenu (string) — 'dashboard' | 'kelola_produk'
   ================================================================= */

$activeMenu = $activeMenu ?? 'dashboard';
$pageTitle  = $pageTitle  ?? 'Admin Panel';
$adminName  = $_SESSION['admin_username'] ?? 'Admin';

function menuClass(string $menu, string $active): string {
    return $menu === $active
        ? 'flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all'
        : 'flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm transition-all';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Admin Library Kenanga</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Tokens Warna Admin ── */
        :root {
            --clr-base:        #FFF2D0;
            --clr-base-dark:   #FFE0A0;
            --clr-accent:      #FF88BA;
            --clr-accent-dark: #F0609E;
            --clr-accent-soft: #FFDEED;
            --clr-text:        #3D2B1F;
            --sidebar-w:       260px;
        }

        /* ── Remap semua Tailwind blue → pink di halaman admin ── */
        .bg-blue-600, .bg-blue-700, .bg-blue-500 {
            background-color: var(--clr-accent) !important;
        }
        .hover\:bg-blue-700:hover, .hover\:bg-blue-800:hover {
            background-color: var(--clr-accent-dark) !important;
        }
        .text-blue-600, .text-blue-700 {
            color: var(--clr-accent-dark) !important;
        }
        .hover\:text-blue-800:hover { color: var(--clr-accent-dark) !important; }
        .bg-blue-50  { background-color: var(--clr-accent-soft) !important; }
        .focus\:ring-blue-500:focus { --tw-ring-color: rgba(255,136,186,.35) !important; }
        .shadow-blue-500\/30 { box-shadow: 0 4px 14px rgba(255,136,186,.30) !important; }
        .ring-blue-500 { --tw-ring-color: rgba(255,136,186,.35) !important; }
        .border-blue-600 { border-color: var(--clr-accent) !important; }
        .bg-emerald-100 { background-color: #D1FAE5 !important; }


        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--clr-base);
            color: var(--clr-text);
        }

        @media (min-width: 1024px) {
            .main-content { margin-left: var(--sidebar-w); }
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            background: #fff;
            border-right: 2px solid var(--clr-base-dark);
            box-shadow: 4px 0 20px rgba(255,136,186,.10);
        }

        /* ── Logo icon sidebar ── */
        .sidebar-logo-icon {
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
            box-shadow: 0 4px 10px rgba(255,136,186,.35);
        }

        /* ── Menu aktif ── */
        .menu-active {
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
            color: #fff;
            box-shadow: 0 4px 12px rgba(255,136,186,.30);
        }
        .menu-active .menu-icon-wrap {
            background: rgba(255,255,255,.25);
        }
        .menu-active i { color: #fff; }

        /* ── Menu tidak aktif ── */
        .menu-inactive {
            color: #b09090;
        }
        .menu-inactive:hover {
            background-color: var(--clr-accent-soft);
            color: var(--clr-accent-dark);
        }
        .menu-inactive:hover i { color: var(--clr-accent-dark); }
        .menu-inactive .menu-icon-wrap { background: var(--clr-base); }
        .menu-inactive i { color: #c4a0a8; }

        /* ── Header admin ── */
        .admin-header {
            background: #fff;
            border-bottom: 2px solid var(--clr-base-dark);
            box-shadow: 0 2px 12px rgba(255,136,186,.08);
        }

        /* ── Avatar header ── */
        .admin-avatar {
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
        }

        /* ── Tombol hamburger ── */
        .hamburger-btn:hover { background-color: var(--clr-accent-soft); color: var(--clr-accent-dark); }

        /* ── Kartu konten ── */
        .admin-card {
            background: #fff;
            border: 1.5px solid var(--clr-base-dark);
            border-radius: 1rem;
            box-shadow: 0 4px 14px rgba(255,136,186,.07);
        }

        /* ── Logout button ── */
        .logout-btn {
            color: var(--clr-accent-dark);
            border-radius: .75rem;
            transition: background .2s, color .2s;
        }
        .logout-btn:hover {
            background: var(--clr-accent-soft);
        }
        .logout-btn .logout-icon {
            background: var(--clr-accent-soft);
        }

        /* ── Bell notif dot ── */
        .notif-dot { background: var(--clr-accent); }
    </style>
</head>
<body>

    <!-- =============================================
         SIDEBAR (Fixed kiri)
         ============================================= -->
    <aside id="sidebar"
           class="admin-sidebar fixed top-0 left-0 h-full w-[260px] flex flex-col z-40 -translate-x-full lg:translate-x-0 transition-transform duration-300">

        <!-- Logo -->
        <div class="px-6 pt-7 pb-6 flex items-center gap-3" style="border-bottom:2px solid var(--clr-base-dark);">
            <div class="sidebar-logo-icon w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-book-open text-white text-sm"></i>
            </div>
            <div>
                <p class="font-extrabold text-[15px] leading-tight" style="color:var(--clr-text);">
                    <span style="color:var(--clr-accent);">Lib</span>Ken
                </p>
                <p class="text-[11px]" style="color:#c4a0a8;">Panel Administrator</p>
            </div>
        </div>

        <!-- Navigasi -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">

            <p class="text-[10px] uppercase tracking-widest font-bold px-4 mb-3" style="color:#d4b0b8;">Menu Utama</p>

            <!-- Dashboard -->
            <a href="admin_dashboard.php"
               class="<?= menuClass('dashboard', $activeMenu) ?> <?= $activeMenu === 'dashboard' ? 'menu-active' : 'menu-inactive' ?>">
                <div class="menu-icon-wrap w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-chart-pie text-[13px]"></i>
                </div>
                Dashboard
            </a>

            <!-- Kelola Produk -->
            <a href="kelola_produk.php"
               class="<?= menuClass('kelola_produk', $activeMenu) ?> <?= $activeMenu === 'kelola_produk' ? 'menu-active' : 'menu-inactive' ?>">
                <div class="menu-icon-wrap w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-box-archive text-[13px]"></i>
                </div>
                Kelola Produk
            </a>

        </nav>

        <!-- Logout -->
        <div class="px-4 pb-6 pt-4" style="border-top:2px solid var(--clr-base-dark);">
            <a href="logout.php"
               onclick="return confirm('Yakin ingin logout?')"
               class="logout-btn flex items-center gap-3 px-4 py-3 font-bold text-sm w-full">
                <div class="logout-icon w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-right-from-bracket text-[13px]" style="color:var(--clr-accent-dark);"></i>
                </div>
                Logout
            </a>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- =============================================
         AREA KONTEN UTAMA (kanan sidebar)
         ============================================= -->
    <div class="main-content min-h-screen flex flex-col">

        <!-- HEADER sticky -->
        <header class="admin-header sticky top-0 z-20">
            <div class="flex items-center justify-between h-16 px-6">

                <!-- Hamburger -->
                <button onclick="toggleSidebar()"
                        class="hamburger-btn lg:hidden p-2 rounded-lg transition-colors"
                        style="color:var(--clr-accent-dark);">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <!-- Breadcrumb -->
                <div class="hidden lg:flex items-center gap-2 text-sm" style="color:#c4a0a8;">
                    <i class="fa-solid fa-house text-xs"></i>
                    <span>/</span>
                    <span class="font-bold" style="color:var(--clr-text);"><?= htmlspecialchars($pageTitle) ?></span>
                </div>

                <!-- Profil Admin -->
                <div class="flex items-center gap-4 ml-auto">
                    <!-- Bell notifikasi -->
                    <button class="relative p-2 rounded-lg transition-colors hamburger-btn">
                        <i class="fa-regular fa-bell text-lg" style="color:var(--clr-accent);"></i>
                        <span class="notif-dot absolute top-1.5 right-1.5 w-2 h-2 rounded-full ring-2 ring-white"></span>
                    </button>

                    <!-- Avatar + Nama -->
                    <div class="flex items-center gap-3 pl-3" style="border-left:2px solid var(--clr-base-dark);">
                        <div class="admin-avatar w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user text-white text-sm"></i>
                        </div>
                        <div class="hidden sm:block leading-tight">
                            <p class="text-xs" style="color:#c4a0a8;">Halo,</p>
                            <p class="text-sm font-extrabold" style="color:var(--clr-text);"><?= htmlspecialchars($adminName) ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- KONTEN HALAMAN -->
        <main class="flex-1 p-6 lg:p-8">
<!-- ↑ Dilanjutkan oleh file halaman masing-masing, ditutup sidebar_close.php -->
