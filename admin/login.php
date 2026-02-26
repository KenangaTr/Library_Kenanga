<?php
/* =================================================================
   admin/login.php — Halaman Login Admin
   ================================================================= */

session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_username']  = $admin['nama_lengkap'] ?? $admin['username'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — Library Kenanga</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --clr-base:        #FFF2D0;
            --clr-base-dark:   #FFE0A0;
            --clr-accent:      #FF88BA;
            --clr-accent-dark: #F0609E;
            --clr-accent-soft: #FFDEED;
            --clr-text:        #3D2B1F;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--clr-base);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow: hidden;
            position: relative;
        }
        /* Dekorasi lingkaran latar */
        body::before {
            content: '';
            position: fixed;
            width: 520px; height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--clr-accent), transparent 70%);
            opacity: .30;
            top: -180px; right: -180px;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--clr-accent-dark), transparent 70%);
            opacity: .25;
            bottom: -160px; left: -160px;
            pointer-events: none;
        }
        .input-field {
            width: 100%;
            background: var(--clr-base);
            border: 1.5px solid var(--clr-base-dark);
            color: var(--clr-text);
            border-radius: .75rem;
            padding: .875rem 1rem .875rem 2.75rem;
            font-size: .875rem;
            font-family: 'Nunito', sans-serif;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        .input-field:hover  { background: #fff; border-color: var(--clr-accent); }
        .input-field:focus  {
            background: #fff;
            border-color: var(--clr-accent);
            box-shadow: 0 0 0 3px rgba(255,136,186,.20);
        }
        .input-field::placeholder { color: #c4a882; }
        .btn-login {
            width: 100%;
            padding: .875rem;
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-accent-dark));
            color: #fff;
            font-weight: 800;
            font-size: .9rem;
            border-radius: .875rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(255,136,186,.40);
            transition: transform .2s, box-shadow .2s;
            font-family: 'Nunito', sans-serif;
            letter-spacing: .02em;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(255,136,186,.50);
        }
        label {
            font-size: .85rem;
            font-weight: 700;
            color: var(--clr-text);
            display: block;
            margin-bottom: .5rem;
        }
    </style>
</head>
<body>

    <div class="w-full max-w-md relative z-10">

        <!-- Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
                 style="background:linear-gradient(135deg,var(--clr-accent),var(--clr-accent-dark));box-shadow:0 8px 24px rgba(255,136,186,.40);">
                <i class="fa-solid fa-book-open text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight" style="color:var(--clr-text);">
                <span style="color:var(--clr-accent);">Lib</span>Ken
            </h1>
            <p class="text-sm mt-1" style="color:#c4a882;">Panel Admin — Library Kenanga</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl p-8"
             style="border:2px solid var(--clr-base-dark);box-shadow:0 16px 48px rgba(255,136,186,.15);">

            <h2 class="text-xl font-extrabold mb-1" style="color:var(--clr-text);">Selamat Datang 👋</h2>
            <p class="text-sm mb-6" style="color:#c4a082;">Masukkan kredensial Anda untuk melanjutkan.</p>

            <!-- Banner sukses dari register -->
            <?php if (isset($_GET['registered'])): ?>
            <div class="flex items-center gap-3 text-sm rounded-xl px-4 py-3 mb-5"
                 style="background:var(--clr-accent-soft);border:1.5px solid var(--clr-accent);color:var(--clr-accent-dark);">
                <i class="fa-solid fa-circle-check flex-shrink-0"></i>
                <span>Akun berhasil dibuat! Silakan login sekarang.</span>
            </div>
            <?php endif; ?>

            <!-- Pesan Error -->
            <?php if ($error): ?>
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-5">
                <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>

                <!-- Username -->
                <div class="mb-5">
                    <label for="username">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-sm" style="color:var(--clr-accent);"></i>
                        </div>
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               placeholder="Masukkan username"
                               required class="input-field">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-7">
                    <label for="password">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-sm" style="color:var(--clr-accent);"></i>
                        </div>
                        <input type="password" id="password" name="password"
                               placeholder="••••••••"
                               required class="input-field" style="padding-right:3rem;">
                        <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center transition-colors"
                                style="color:var(--clr-accent);">
                            <i class="fa-regular fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Login ke Panel Admin
                </button>

            </form>

            <p class="text-center text-sm mt-5" style="color:#c4a082;">
                Belum punya akun?
                <a href="register.php" class="font-bold transition-colors" style="color:var(--clr-accent-dark);">Daftar di sini</a>
            </p>

        </div>

        <p class="text-center text-xs mt-6" style="color:#c4a882;">
            &copy; <?= date('Y') ?> Library Kenanga. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const pwd  = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
</body>
</html>
