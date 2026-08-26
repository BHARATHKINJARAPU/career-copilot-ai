<?php
require_once 'config/session.php';
$extraCss = 'about.css';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main>
  <section class="about-hero">
    <span class="glow-pill">Product Architecture</span>
    <h1 class="gradient-text" style="font-size: 2.8rem; margin: 16px 0;">What is Career Copilot AI?</h1>
    <p class="text-muted" style="font-size: 1.15rem;">
      An intelligent career mentor bridging academia and real-world tech requirements for engineering students.
    </p>
  </section>

  <section class="about-grid">
    <div class="problem-solution-card glass-card">
      <h3><span style="color:var(--accent-rose)">⚠️</span> The Problem</h3>
      <p class="text-muted">
        Undergraduate engineering curricula often lag behind fast-paced industry requirements. Students graduate with theoretical foundations, but lack hands-on clarity regarding modern tech stacks, distributed systems, and real-world project execution.
      </p>
    </div>

    <div class="problem-solution-card glass-card">
      <h3><span style="color:var(--accent-emerald)">✨</span> Our Solution</h3>
      <p class="text-muted">
        A unified platform connecting <strong>Skills + Roadmaps + Projects + Coding Sandbox + AI Mentorship + Mock Interviews</strong> powered by PHP 8 and MySQL.
      </p>
    </div>
  </section>
</main>

<?php require_once 'includes/footer.php'; ?>