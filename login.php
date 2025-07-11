<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem SPK SAW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .login-bg {
            min-height: 100vh;
            background: linear-gradient(120deg, #0F0F0F 0%, #00FF33 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #000000;
            border-radius: 28px;
            box-shadow: 0 8px 32px rgba(76, 98, 134, 0.12);
            display: flex;
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            min-height: 480px;
        }
        .login-left, .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 36px;
        }
        .login-right {
            background: linear-gradient(120deg, #000000 0%, #0F0F0F 100%);
            align-items: center;
            justify-content: center;
        }
        .login-right img {
            max-width: 320px;
            width: 100%;
        }
        .login-form input, .login-form button {
            border-radius: 12px;
        }
        .login-form button {
            background: linear-gradient(120deg, #00FF33 0%, #0F0F0F 100%);
            color: #fff;
            font-weight: 600;
            border: none;
        }
        .login-form button:hover {
            background: #4B6286;
        }
        .login-title {
            font-weight: 700;
            color: #7B61FF;
            margin-bottom: 8px;
        }
        .login-subtitle {
            color: #4B6286;
            font-size: 1.1rem;
            margin-bottom: 24px;
        }
        .watermark {
            position: fixed;
            left: 0; right: 0; bottom: 18px;
            text-align: center;
            color: #2d2d44;
            opacity: 0.97;
            z-index: 9999;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            pointer-events: none;
            text-shadow: 0 2px 8px rgba(76,98,134,0.18);
        }
        @media (max-width: 900px) {
            .login-card { flex-direction: column; min-height: unset; }
            .login-right { min-height: 200px; }
        }
        @media (max-width: 600px) {
            .login-left, .login-right { padding: 24px 12px; }
            .login-card { border-radius: 0; }
        }
        .login-form input[type="text"], .login-form input[type="password"] {
            color: #FFFFFF;
            background-color: #222;
        }
    </style>
</head>
<body>
<div class="login-bg">
    <div class="login-card">
        <div class="login-left">
            <div class="mb-4 text-center">
                <img src="assets/img/images.jpg" alt="images" style="width:170px;height:130px;border-radius:50%;box-shadow:0 2px 8px rgba(76,98,134,0.10);background:#fff;object-fit:cover;">
                <div style="font-size:1.3rem; font-weight:800; color:#00FF33; line-height:1.2; margin-top:10px;">Sistem Pemilihan Anggota Kepengurusan Organisasi Teladan</div>
                <div style="font-size:1.1rem; font-weight:600; color:#00FF33; line-height:1.2;">Wilayah XII Kalimantan</div>
                <div style="font-size:1rem; font-weight:500; color:#00FF33; line-height:1.2;">Perhimpunan Mahasiswa Informatika Dan Komputer Nasional</div>
        </div>
            <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2">
                        <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
                <div class="mb-3">
                <div style=color:#FFFFFF; line-height:1.2;">Username</div>
                    <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
                </div>
                <div class="mb-3">
                <div style=color:#FFFFFF; line-height:1.2;">Password</div>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Login</button>
            </form>
            </div>
        <div class="login-right">
            <img src="assets/img/hacker.jpg" alt="hacker" style="max-width: 420px; width: 100%; border-radius: 18px; box-shadow: 0 4px 16px rgba(76,98,134,0.10);" loading="lazy">
        </div>
    </div>
</div>
<p class="watermark">Copyright &#169; 2025 Muhammad Rizky. All rights reserved.</p>
</body>
</html> 