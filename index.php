<?php
require_once 'config/session.php';
$extraCss = 'home.css';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
  <section class="hero-section">
    <div class="hero-content">
      <div class="glow-pill">⚡ IBM Hackathon Prototype</div>
      <h1 class="hero-title">
        Bridge the Gap Between <br>
        <span class="gradient-text">Academia and Industry</span> with AI.
      </h1>
      <p class="hero-subtitle">
        Career Copilot AI is your personalized AI career companion that analyzes your skills, builds targeted roadmaps, selects real projects, and trains you for mock interviews.
      </p>
      <div class="hero-actions">
        <a href="signup.php" class="btn btn-primary">Get Started Now →</a>
        <a href="login.php" class="btn btn-outline">Existing User Login 💬</a>
      </div>
    </div>

    <div class="pipeline-container glass-card">
      <div class="pipeline-steps">
        <div class="pipeline-step"><div class="step-icon">📄</div><span class="step-label">Resume</span></div>
        <span class="pipeline-arrow">➔</span>
        <div class="pipeline-step"><div class="step-icon">🧠</div><span class="step-label">Skill Analysis</span></div>
        <span class="pipeline-arrow">➔</span>
        <div class="pipeline-step"><div class="step-icon">🗺️</div><span class="step-label">Roadmap</span></div>
        <span class="pipeline-arrow">➔</span>
        <div class="pipeline-step"><div class="step-icon">🛠️</div><span class="step-label">Projects</span></div>
        <span class="pipeline-arrow">➔</span>
        <div class="pipeline-step"><div class="step-icon">💻</div><span class="step-label">Coding</span></div>
        <span class="pipeline-arrow">➔</span>
        <div class="pipeline-step"><div class="step-icon">🎙️</div><span class="step-label">Interview</span></div>
        <span class="pipeline-arrow">➔</span>
        <div class="pipeline-step"><div class="step-icon">🚀</div><span class="step-label">Career Growth</span></div>
      </div>
    </div>
  </section>
</main>

<?php require_once 'includes/footer.php'; ?>