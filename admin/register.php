<?php
/* =================================================================
   admin/register.php — Halaman Registrasi Admin Baru
   
   ⚠️  PENTING: Halaman ini sebaiknya dihapus atau dilindungi
   dengan proteksi tambahan (misalnya cek IP, atau hanya bisa
   diakses oleh super admin) setelah lingkungan produksi aktif.
   ================================================================= */

session_start();

// Jika sudah login, tidak perlu mendaftar lagi
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

// ✅ Koneksi Database
require_once '../config/database.php';

// Kumpulan pesan: error (array) dan sukses (string)
$errors  = [];
$success = '';

// ========================================================
//  PROSES FORM REGISTRASI
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Sanitasi Input ---
    $nama_lengkap     = trim(htmlspecialchars($_POST['nama_lengkap']     ?? ''));
    $username         = trim(htmlspecialchars($_POST['username']         ?? ''));
    $password         = $_POST['password']         ?? '';   // JANGAN di-trim password
    $konfirmasi_pass  = $_POST['konfirmasi_pass']  ?? '';

    // --- 2. Validasi: Field Wajib Diisi ---
    if (empty($nama_lengkap))    $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($username))        $errors[] = 'Username wajib diisi.';
    if (strlen($username) < 4)   $errors[] = 'Username minimal 4 karakter.';
    if (empty($password))        $errors[] = 'Password wajib diisi.';
    if (strlen($password) < 8)   $errors[] = 'Password minimal 8 karakter.';

    // --- 3. Validasi: Konfirmasi Password ---
    if ($password !== $konfirmasi_pass) {
        $errors[] = 'Password dan konfirmasi password tidak cocok.';
    }

    // --- 4. Cek Duplikasi Username di Database ---
    if (empty($errors)) {
        $stmt_cek = $pdo->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
        $stmt_cek->execute([$username]);

        if ($stmt_cek->rowCount() > 0) {
            $errors[] = "Username <strong>{$username}</strong> sudah digunakan. Pilih username lain.";
        }
    }

    // --- 5. Simpan ke Database jika semua validasi lolos ---
    if (empty($errors)) {

        // 🔒 KEAMANAN: Hash password dengan bcrypt sebelum disimpan
        // password_hash() secara otomatis menggunakan salt acak dan cost factor yg aman.
        // Jangan pernah menyimpan password plain text!
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_insert = $pdo->prepare(
            "INSERT INTO admins (nama_lengkap, username, password, created_at)
             VALUES (?, ?, ?, NOW())"
        );

        if ($stmt_insert->execute([$nama_lengkap, $username, $hashed_password])) {
            // ✅ Berhasil — redirect ke login dengan parameter sukses
            header('Location: login.php?registered=1');
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin — Library Kenanga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-md">

        <!-- Logo / Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 rounded-2xl backdrop-blur-sm mb-4 ring-1 ring-white/20">
                <i class="fa-solid fa-user-shield text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">
                iPusn<i class="fa-solid fa-star text-blue-400 text-[22px] mx-0.5"></i>s
            </h1>
            <p class="text-slate-400 text-sm mt-1">Panel Admin — Library Kenanga</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-2xl shadow-black/30 p-8">

            <h2 class="text-xl font-bold text-slate-800 mb-1">Buat Akun Admin ✨</h2>
            <p class="text-slate-500 text-sm mb-6">Isi data di bawah untuk mendaftar sebagai administrator.</p>

            <!-- ■ Pesan Error (jika ada) -->
            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3.5 mb-5 space-y-1">
                <p class="text-red-700 text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> Pendaftaran gagal:
                </p>
                <ul class="list-disc list-inside text-red-600 text-sm space-y-0.5 pl-1">
                    <?php foreach ($errors as $err): ?>
                        <li><?= $err ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>

                <!-- Input: Nama Lengkap -->
                <div class="mb-4">
                    <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-id-card text-slate-400 text-sm"></i>
                        </div>
                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                            placeholder="Contoh: Budi Santoso"
                            required
                            class="w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-xl text-sm bg-slate-50 placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Input: Username -->
                <div class="mb-4">
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                        Username
                        <span class="text-slate-400 font-normal text-xs ml-1">(min. 4 karakter)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-slate-400 text-sm"></i>
                        </div>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            placeholder="Contoh: budi_admin"
                            required
                            minlength="4"
                            autocomplete="username"
                            class="w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-xl text-sm bg-slate-50 placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Input: Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        Password
                        <span class="text-slate-400 font-normal text-xs ml-1">(min. 8 karakter)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400 text-sm"></i>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            oninput="checkPasswordStrength(this.value)"
                            class="w-full pl-11 pr-11 py-3.5 border border-slate-200 rounded-xl text-sm bg-slate-50 placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <button type="button" onclick="togglePass('password','eye1')"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-regular fa-eye text-sm" id="eye1"></i>
                        </button>
                    </div>
                    <!-- Indikator Kekuatan Password -->
                    <div class="mt-2 flex gap-1.5" id="strength-bars">
                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="bar1"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="bar2"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="bar3"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="bar4"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1" id="strength-text"></p>
                </div>

                <!-- Input: Konfirmasi Password -->
                <div class="mb-7">
                    <label for="konfirmasi_pass" class="block text-sm font-semibold text-slate-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-shield-halved text-slate-400 text-sm"></i>
                        </div>
                        <input
                            type="password"
                            id="konfirmasi_pass"
                            name="konfirmasi_pass"
                            placeholder="Ulangi password Anda"
                            required
                            autocomplete="new-password"
                            oninput="checkMatch()"
                            class="w-full pl-11 pr-11 py-3.5 border border-slate-200 rounded-xl text-sm bg-slate-50 placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <button type="button" onclick="togglePass('konfirmasi_pass','eye2')"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-regular fa-eye text-sm" id="eye2"></i>
                        </button>
                    </div>
                    <p class="text-xs mt-1.5 hidden" id="match-msg"></p>
                </div>

                <!-- Tombol Daftar -->
                <button
                    type="submit"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl text-sm tracking-wide transition-all duration-200 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 hover:-translate-y-0.5">
                    <i class="fa-solid fa-user-plus mr-2"></i> Daftar sebagai Admin
                </button>

            </form>

            <!-- Link ke Login -->
            <p class="text-center text-slate-500 text-sm mt-5">
                Sudah punya akun?
                <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                    Login di sini
                </a>
            </p>

        </div>

        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; <?= date('Y') ?> Library Kenanga. All rights reserved.
        </p>
    </div>

    <script>
        // Toggle show/hide password
        function togglePass(inputId, iconId) {
            const inp  = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            inp.type = inp.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        // Indikator kekuatan password
        function checkPasswordStrength(val) {
            const bars  = ['bar1','bar2','bar3','bar4'];
            const text  = document.getElementById('strength-text');
            let score   = 0;
            if (val.length >= 8)                        score++;
            if (/[A-Z]/.test(val))                      score++;
            if (/[0-9]/.test(val))                      score++;
            if (/[^A-Za-z0-9]/.test(val))              score++;

            const colors = ['','bg-red-400','bg-orange-400','bg-yellow-400','bg-emerald-500'];
            const labels = ['','Lemah','Cukup','Kuat','Sangat Kuat'];
            const tcolors= ['','text-red-500','text-orange-500','text-yellow-600','text-emerald-600'];

            bars.forEach((id, i) => {
                const el = document.getElementById(id);
                el.className = 'h-1 flex-1 rounded-full transition-colors ' +
                    (i < score ? colors[score] : 'bg-slate-200');
            });
            text.textContent   = val.length > 0 ? 'Kekuatan: ' + (labels[score] || 'Lemah') : '';
            text.className     = 'text-xs mt-1 ' + (tcolors[score] || '');
        }

        // Cek kecocokan password real-time
        function checkMatch() {
            const pwd   = document.getElementById('password').value;
            const conf  = document.getElementById('konfirmasi_pass').value;
            const msg   = document.getElementById('match-msg');
            if (conf.length === 0) { msg.classList.add('hidden'); return; }
            msg.classList.remove('hidden');
            if (pwd === conf) {
                msg.textContent  = '✓ Password cocok';
                msg.className    = 'text-xs mt-1.5 text-emerald-600 font-medium';
            } else {
                msg.textContent  = '✗ Password tidak cocok';
                msg.className    = 'text-xs mt-1.5 text-red-500 font-medium';
            }
        }
    </script>
</body>
</html>
