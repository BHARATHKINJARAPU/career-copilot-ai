<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password credentials.";
        }
    } else {
        $error = "Please fill in all mandatory fields.";
    }
}

$extraCss = 'auth.css';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-wrapper">
  <div class="auth-card glass-card">
    <div class="auth-header">
      <div class="logo-icon-box" style="margin: 0 auto 10px;">🚀</div>
      <h2>Welcome Back</h2>
      <p class="text-muted" style="font-size: 0.88rem;">Sign in to access your Career Copilot dashboard</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert-error" style="background:rgba(244,63,94,0.15); border:1px solid var(--accent-rose); color:var(--accent-rose); padding:10px; border-radius:8px; margin-bottom:16px; font-size:0.85rem; text-align:center;">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" id="loginForm">
      <div style="margin-bottom: 16px;">
        <label style="display:block; font-size:0.85rem; margin-bottom:6px; color:var(--text-muted);">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="alex@university.edu" required style="width: 100%;" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display:block; font-size:0.85rem; margin-bottom:6px; color:var(--text-muted);">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required style="width: 100%;">
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">Sign In to Dashboard →</button>
    </form>

    <div class="auth-footer">
      New student? <a href="signup.php">Create an account here</a>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>