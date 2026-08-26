<footer>
    <div class="footer-container">
      <div class="footer-col">
        <div class="brand-logo" style="margin-bottom: 14px;">
          <div class="logo-icon-box">🚀</div>
          <span>Career Copilot AI</span>
        </div>
        <p class="text-muted" style="font-size: 0.88rem;">
          Empowering computer science & engineering students with intelligent roadmaps, skill tracking, and AI mentorship.
        </p>
        <div style="margin-top: 14px;">
          <span class="badge-hackathon">IBM Hackathon Prototype</span>
        </div>
      </div>

      <div class="footer-col">
        <h4>Navigation</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About Project</a></li>
          <li><a href="career-hub.php">Career Hub</a></li>
          <li><a href="dashboard.php">Student Dashboard</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>AI Workspaces</h4>
        <ul>
          <li><a href="ai-mentor.php">AI Mentor Chat</a></li>
          <li><a href="interview-lab.php">Mock Interview Lab</a></li>
          <li><a href="career-hub.php">Resume Studio</a></li>
          <li><a href="career-hub.php">Coding Sandbox</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <li><a href="login.php">Sign In</a></li>
          <li><a href="signup.php">Create Account</a></li>
          <li><a href="contact.php">Contact Team</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?php echo date('Y'); ?> Career Copilot AI. All rights reserved. PHP 8 + MySQL Engine.</span>
      <span>Designed for IBM Hackathon Demonstration</span>
    </div>
  </footer>

  <script src="js/navigation.js"></script>
  <script src="js/main.js"></script>
  <?php if (isset($extraJs)): echo "<script src='js/{$extraJs}'></script>"; endif; ?>
</body>
</html>