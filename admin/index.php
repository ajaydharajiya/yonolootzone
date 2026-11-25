<?php
require_once __DIR__ . '/../cms/config.php';

if (logged_in()) {
    header('Location: dashboard.php');
    exit;
}

if (!is_file(CMS_DATA . '/auth.json')) {
    header('Location: ../cms/setup.php');
    exit;
}

// Session security headers
if (session_status() === PHP_SESSION_ACTIVE) {
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.use_only_cookies', '1');
}

$error = '';
$lockout_file = CMS_DATA . '/login_attempts.json';
$attempts = read_json($lockout_file, ['count' => 0, 'last_attempt' => 0]);

// Rate limiting: 5 failed attempts = 10 minute lockout
$is_locked = ($attempts['count'] >= 5) && (time() - $attempts['last_attempt'] < 600);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_locked) {
        $wait_seconds = 600 - (time() - $attempts['last_attempt']);
        $error = 'Too many failed login attempts. Please wait ' . ceil($wait_seconds / 60) . ' minutes before retrying.';
    } else {
        $a = read_json(CMS_DATA . '/auth.json', []);
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (hash_equals((string)($a['username'] ?? ''), $username) && password_verify($password, $a['password_hash'] ?? '')) {
            // Success: reset attempts
            write_json($lockout_file, ['count' => 0, 'last_attempt' => time()]);
            $_SESSION['cms_auth'] = 1;
            $_SESSION['cms_user'] = $username;
            $_SESSION['last_activity'] = time();

            // Log successful login
            $logs = read_json(CMS_DATA . '/activity_log.json', []);
            array_unshift($logs, [
                'action' => 'Admin Login Success',
                'user' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'time' => date('Y-m-d H:i:s')
            ]);
            if (count($logs) > 50) $logs = array_slice($logs, 0, 50);
            write_json(CMS_DATA . '/activity_log.json', $logs);

            header('Location: dashboard.php');
            exit;
        } else {
            // Failure: increment attempts
            $new_count = ($attempts['count'] ?? 0) + 1;
            write_json($lockout_file, ['count' => $new_count, 'last_attempt' => time()]);
            $remaining = 5 - $new_count;
            $error = $remaining > 0 ? "Invalid username or password. ($remaining attempts remaining)" : "Too many failed attempts. Account locked for 10 minutes.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>YonoLootZone Admin Control Panel</title>
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <style>
    body { background: linear-gradient(135deg, #0b0f19, #1e1329); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .login-box { width: 440px; margin-top: 60px; }
    .card { border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); border: 1px solid rgba(224, 0, 104, 0.3); background: #131b2e; color: #fff; }
    .btn-pink { background: linear-gradient(135deg, #e00068, #ff2d78); border: none; color: #fff; font-weight: 700; border-radius: 12px; padding: 12px; }
    .btn-pink:hover { opacity: 0.95; color: #fff; transform: translateY(-1px); }
    .form-control { background: #1a243d; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; }
    .form-control:focus { background: #222f4f; border-color: #e00068; color: #fff; box-shadow: 0 0 0 0.2rem rgba(224,0,104,0.25); }
    .input-group-text { background: #1a243d; border-color: rgba(255,255,255,0.1); color: #94a3b8; }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="text-center text-white mb-4">
    <div style="width:60px; height:60px; margin:0 auto 12px; border-radius:16px; overflow:hidden; border:2px solid #e00068; box-shadow:0 0 20px rgba(224,0,104,0.4);">
      <img src="../assets/all-yono-game-app.webp" style="width:100%; height:100%; object-fit:cover;">
    </div>
    <h2 style="font-weight:900; letter-spacing:-0.5px;">Yono<span style="color:#ff5aa2">LootZone</span></h2>
    <p class="text-white-50 mb-0 font-weight-bold">Administrator Control Panel</p>
    <small class="text-muted"><i class="fas fa-shield-alt mr-1"></i> Protected Rate-Limited Session</small>
  </div>

  <div class="card">
    <div class="card-body p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger" style="border-radius:12px; font-size:13px; font-weight:600;">
          <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="form-group mb-3">
          <label class="text-xs text-muted font-weight-bold">ADMIN USERNAME</label>
          <div class="input-group">
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            <div class="input-group-append">
              <div class="input-group-text"><i class="fas fa-user-shield"></i></div>
            </div>
          </div>
        </div>

        <div class="form-group mb-4">
          <label class="text-xs text-muted font-weight-bold">SECRET PASSWORD</label>
          <div class="input-group">
            <input type="password" name="password" class="form-control" placeholder="Enter admin password" required>
            <div class="input-group-append">
              <div class="input-group-text"><i class="fas fa-lock"></i></div>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-pink btn-block">
          <i class="fas fa-sign-in-alt mr-2"></i> Authenticate & Enter
        </button>
      </form>
    </div>
    <div class="card-footer text-center text-muted py-3" style="font-size:11px; border-top:1px solid rgba(255,255,255,0.05);">
      Public visitors do not require registration or login.
    </div>
  </div>
</div>
</body>
</html>
