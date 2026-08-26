<?php
require_once 'includes/auth-check.php';
require_once 'includes/functions.php';

$userId = $_SESSION['user_id'];
checkOnboardingOrRedirect($userId);

$userProfile = getStudentFullProfile($userId);

$pdo = getDBConnection();
$projects = $pdo->query("SELECT * FROM projects LIMIT 6")->fetchAll();
$questions = $pdo->query("SELECT * FROM coding_questions LIMIT 5")->fetchAll();

$extraCss = 'career-hub.css';
$extraJs = 'career-hub.js';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$skills = $userProfile['skills'] ?? [];
$targetRole = htmlspecialchars($userProfile['career_goal']['target_role'] ?? 'Developer');
?>

<main class="hub-container">
  <div style="margin-bottom: 24px;">
    <span class="glow-pill">Integrated Career Discovery</span>
    <h1 class="gradient-text" style="font-size: 2.4rem; margin-top: 8px;">Career Hub</h1>
  </div>

  <div class="hub-nav-tabs">
    <button class="tab-btn active" data-tab="resumeStudio">Resume Studio</button>
    <button class="tab-btn" data-tab="skillInsights">Skill Insights</button>
    <button class="tab-btn" data-tab="roadmaps">Learning Roadmaps</button>
    <button class="tab-btn" data-tab="projects">Project Explorer</button>
    <button class="tab-btn" data-tab="coding">Coding Sandbox</button>
  </div>

  <section id="resumeStudio" class="tab-pane active">
    <div class="resume-layout">
      <div class="glass-card" style="padding: 24px;">
        <h3>Upload & Extract Resume</h3>
        <p class="text-muted" style="font-size: 0.9rem; margin: 8px 0 20px;">Upload PDF or DOCX to analyze skills and save extracted data directly into MySQL.</p>
        
        <form id="resumeUploadForm" enctype="multipart/form-data">
          <div class="upload-zone" id="resumeDrop">
            <div style="font-size: 2.5rem; margin-bottom: 10px;">📄</div>
            <p style="font-weight:600;">Drag & Drop or Browse Resume File</p>
            <p class="text-muted" style="font-size:0.8rem; margin-top:4px;">Supports PDF, DOCX, TXT (Max 5MB)</p>
            <input type="file" name="resume" id="resumeFileInput" style="display:none;" accept=".pdf,.docx,.txt">
          </div>
        </form>
      </div>

      <div class="glass-card" id="resumeAnalysisResult" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h4>Logged Profile Skills</h4>
          <div class="score-badge"><?php echo $userProfile['resume'] ? htmlspecialchars($userProfile['resume']['score']) . ' / 100' : 'N/A'; ?></div>
        </div>
        <hr style="border: 0; border-top: 1px solid var(--border-glass); margin: 12px 0;">
        
        <div class="pill-tag-container">
          <?php if (empty($skills)): ?>
            <p class="text-muted" style="font-size:0.88rem;">No skills entered in profile yet.</p>
            <a href="onboarding.php" class="btn btn-outline" style="margin-top:10px; font-size:0.82rem;">Add Skills Now</a>
          <?php else: ?>
            <?php foreach ($skills as $s): ?>
              <span class="pill-tag verified">
                <?php echo htmlspecialchars($s['skill_name']); ?> (<?php echo htmlspecialchars($s['skill_level']); ?>)
              </span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section id="skillInsights" class="tab-pane">
    <div class="glass-card" style="padding: 30px;">
      <h3>Market Skill Alignment for <?php echo $targetRole; ?></h3>
      <p class="text-muted" style="margin-bottom: 24px;">Displaying your entered skills against industry requirements.</p>

      <?php if (empty($skills)): ?>
        <div class="zero-state-box">
          <p class="text-muted">You haven't entered any skills yet.</p>
          <a href="onboarding.php" class="btn btn-primary" style="margin-top:10px; font-size:0.85rem;">Add Your Skills</a>
        </div>
      <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
          <?php foreach ($skills as $s): ?>
            <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md);">
              <h4><?php echo htmlspecialchars($s['skill_name']); ?></h4>
              <p class="text-cyan" style="font-weight:700; margin: 4px 0 6px;"><?php echo htmlspecialchars($s['skill_level']); ?></p>
              <p class="text-muted" style="font-size:0.82rem;">Source: <?php echo htmlspecialchars($s['source']); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section id="roadmaps" class="tab-pane">
    <div class="glass-card" style="padding: 30px;">
      <h3>Personalized Learning Roadmap for <?php echo $targetRole; ?></h3>
      <div class="roadmap-timeline" style="margin-top:20px;">
        <div class="roadmap-node">
          <span class="glow-pill">Stage 01: Foundations</span>
          <h4 style="margin:6px 0;">Core Language Syntax, Version Control & Development Setup</h4>
        </div>
        <div class="roadmap-node" style="border-left-color: var(--accent-cyan);">
          <span class="glow-pill" style="border-color:var(--accent-cyan); color:var(--accent-cyan);">Stage 02: Core Technology Stack</span>
          <h4 style="margin:6px 0;">Data Modeling, Framework Integration & REST API Design</h4>
        </div>
        <div class="roadmap-node" style="border-left-color: var(--accent-purple);">
          <span class="glow-pill" style="border-color:var(--accent-purple); color:var(--accent-purple);">Stage 03: Production Projects & Testing</span>
          <h4 style="margin:6px 0;">Full-Stack Application Deployment, Authentication & Performance Tuning</h4>
        </div>
      </div>
    </div>
  </section>

  <section id="projects" class="tab-pane">
    <div class="projects-grid">
      <?php foreach ($projects as $proj): ?>
        <div class="glass-card" style="padding: 24px;">
          <span class="glow-pill" style="margin-bottom: 8px;"><?php echo htmlspecialchars($proj['difficulty']); ?></span>
          <h3 style="margin-top: 6px;"><?php echo htmlspecialchars($proj['title']); ?></h3>
          <p class="text-muted" style="font-size: 0.88rem; margin: 10px 0;"><?php echo htmlspecialchars($proj['description']); ?></p>
          <div style="font-size: 0.8rem; color: var(--accent-cyan);">Tech: <?php echo htmlspecialchars($proj['technologies']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="coding" class="tab-pane">
    <div class="code-practice-layout">
      <?php if (!empty($questions)): $q = $questions[0]; ?>
        <div class="glass-card" style="padding: 24px;">
          <span class="glow-pill"><?php echo htmlspecialchars($q['topic']); ?></span>
          <h3 style="margin: 8px 0;"><?php echo htmlspecialchars($q['title']); ?></h3>
          <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 14px;"><?php echo htmlspecialchars($q['question']); ?></p>
        </div>

        <div class="glass-card" style="padding: 24px;">
          <div class="code-editor-sim">
            <textarea class="code-textarea" id="userCodeInput"><?php echo htmlspecialchars($q['sample_answer']); ?></textarea>
          </div>
          <button class="btn btn-primary" id="submitCodeBtn" style="margin-top: 14px; width: 100%;">Run & Submit Solution ⚡</button>
          <div id="codeFeedback"></div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once 'includes/footer.php'; ?>