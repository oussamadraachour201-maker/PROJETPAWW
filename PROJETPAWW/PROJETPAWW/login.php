<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Attendance System - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #001f3f 0%, #003d82 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      font-family: Inter, system-ui, Arial, Helvetica, sans-serif;
      color: white;
    }
    .login-container {
      background: rgba(10, 46, 92, 0.9);
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      border: 1px solid #003d82;
      max-width: 500px;
      width: 100%;
    }
    .login-container h1 {
      color: #4da6ff;
      margin-bottom: 30px;
      text-align: center;
      font-size: 28px;
    }
    .role-buttons {display:grid;grid-template-columns:1fr;gap:15px}
    .role-btn{padding:20px;font-size:18px;font-weight:600;border:2px solid transparent;border-radius:8px;cursor:pointer;color:white}
    .role-btn-student{background:linear-gradient(135deg,#0d9488 0%,#10b981 100%)}
    .role-btn-professor{background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%)}
    .role-btn-admin{background:linear-gradient(135deg,#dc2626 0%,#ef4444 100%)}
    .divider{color:#7a9cc6;margin:30px 0;text-align:center;font-size:14px}
    .demo-note{background:rgba(77,166,255,0.1);border-left:3px solid #4da6ff;padding:12px;border-radius:4px;font-size:13px;color:#b3d9ff;margin-top:20px}
    footer{text-align:center;color:#7a9cc6;margin-top:40px;font-size:12px}
  </style>
</head>
<body>
  <div class="login-container">
    <h1>🎓 Attendance System</h1>

    <?php if (!empty($_SESSION['login_error'])): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?>
      </div>
    <?php endif; ?>

    <form method="post" action="process_login.php">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" placeholder="username (e.g. admin)" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" placeholder="password" required>
      </div>
      <div class="d-flex gap-2 mb-3">
        <button class="btn btn-primary" type="submit">Sign in</button>
        <button type="button" class="btn btn-outline-secondary" onclick="viewDemo()">Demo Mode</button>
      </div>
    </form>

    <div class="divider">Or quick role links</div>

    <div class="role-buttons">
      <button class="role-btn role-btn-student" onclick="goToPage('student.php')">👤 Student View</button>
      <button class="role-btn role-btn-professor" onclick="goToPage('professor.php')">👨‍🏫 Professor View</button>
      <button class="role-btn role-btn-admin" onclick="goToPage('admin.php')">⚙️ Admin View</button>
    </div>

    <div class="demo-note"><strong>Demo Mode:</strong> Loads sample data for testing. Use the seeded admin user (admin/admin123) after running `seed_users.php`.</div>
  </div>

  <footer>
    <p>Attendance Management System | Algiers University</p>
    <p>Secure • Fast • Reliable</p>
  </footer>

  <script>
    function goToPage(page) {
      localStorage.setItem('userRole', page.split('.')[0]);
      window.location.href = page;
    }
    function viewDemo() { localStorage.setItem('demoMode', 'true'); window.location.href = 'student.php'; }
  </script>
</body>
</html>
