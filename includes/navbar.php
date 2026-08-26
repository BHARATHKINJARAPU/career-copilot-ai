<?php
require_once __DIR__ . '/../config/session.php';
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Student';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="navbar">
  <div class="nav-container">
    <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'index.php'; ?>" class="brand-logo">
      <div class="logo-icon-box">🚀</div>
      <span>Career Copilot <span class="gradient-text">AI</span></span>
    </a>

    <ul class="nav-links">
      <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a></li>
      <li><a href="about.php" class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">About</a></li>
      <?php if ($isLoggedIn): ?>
        <li><a href="career-hub.php" class="<?php echo $currentPage === 'career-hub.php' ? 'active' : ''; ?>">Career Hub</a></li>
        <li><a href="ai-mentor.php" class="<?php echo $currentPage === 'ai-mentor.php' ? 'active' : ''; ?>">AI Mentor</a></li>
        <li><a href="interview-lab.php" class="<?php echo $currentPage === 'interview-lab.php' ? 'active' : ''; ?>">Interview Lab</a></li>
        <li><a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
      <?php endif; ?>
      <li><a href="contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
    </ul>

    <div class="nav-auth">
      <?php if ($isLoggedIn): ?>
        <span style="font-size:0.88rem; font-weight:600; color:var(--accent-cyan); margin-right:8px;">
          👤 <?php echo htmlspecialchars($userName); ?>
        </span>
        <a href="logout.php" class="btn btn-outline" style="padding: 7px 16px;">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline" style="padding: 7px 16px;">Login</a>
        <a href="signup.php" class="btn btn-primary" style="padding: 7px 16px;">Sign Up</a>
      <?php endif; ?>
    </div>

    <button class="hamburger" aria-label="Toggle Navigation">☰</button>
  </div>
</header>