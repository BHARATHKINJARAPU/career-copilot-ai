<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        $pdo = getDBConnection();

        // 1. Check if email is already registered
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $error = "This email is already registered. Please login.";
        } else {
            // 2. Insert new user into database
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, onboarding_completed) VALUES (?, ?, ?, 0)");
            
            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $newUserId = $pdo->lastInsertId();
                
                // 3. Log user in and redirect to Onboarding Wizard
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                header("Location: onboarding.php");
                exit();
            } else {
                $error = "Failed to register account. Please try again.";
            }
        }
    } else {
        $error = "Please fill in all mandatory registration fields.";
    }
}

$extraCss = 'auth.css';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-wrapper">
  <div class="auth-card glass-card" style="max-width: 460px;">
    <div class="auth-header">
      <div class="logo-icon-box" style="margin: 0 auto 10px;">🚀</div>
      <h2>Create Student Account</h2>
      <p class="text-muted" style="font-size: 0.88rem;">Initialize your personalized AI career copilot profile</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert-error" style="background:rgba(244,63,94,0.15); border:1px solid var(--accent-rose); color:var(--accent-rose); padding:10px; border-radius:8px; margin-bottom:16px; font-size:0.85rem; text-align:center;">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="signup.php" id="signupForm">
      <div style="margin-bottom: 14px;">
        <label style="display:block; font-size:0.85rem; margin-bottom:6px; color:var(--text-muted);">Full Name <span style="color:var(--accent-rose);">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="Alex Mercer" required style="width: 100%;" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
      </div>

      <div style="margin-bottom: 14px;">
        <label style="display:block; font-size:0.85rem; margin-bottom:6px; color:var(--text-muted);">Email Address <span style="color:var(--accent-rose);">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="alex@university.edu" required style="width: 100%;" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display:block; font-size:0.85rem; margin-bottom:6px; color:var(--text-muted);">Password <span style="color:var(--accent-rose);">*</span></label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required style="width: 100%;">
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Create Account & Build Profile →</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Sign In</a>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
```

---

### What Happens Now:
1. Open **[http://localhost/career-copilot-ai/signup.php](http://localhost/career-copilot-ai/signup.php)**
2. Enter your Name, Email, and Password.
3. Upon clicking **Create Account & Build Profile**, it will log you in automatically and take you straight into the 5-step **Onboarding Wizard (`onboarding.php`)** where you select your engineering branch, semester, target role, and current skills.