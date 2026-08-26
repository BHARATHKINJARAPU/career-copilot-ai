<?php
require_once 'includes/auth-check.php';
require_once 'includes/functions.php';

$userId = $_SESSION['user_id'];
checkOnboardingOrRedirect($userId);

$userProfile = getStudentFullProfile($userId);

$extraCss = 'dashboard.css';
$extraJs = 'dashboard.js';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Profile Extract Variables
$name = htmlspecialchars($userProfile['name'] ?? 'Student');
$academic = $userProfile['academic'] ?? [];
$branch = htmlspecialchars($academic['branch'] ?? 'Not specified');
$year = htmlspecialchars($academic['year'] ?? 'Not specified');

$goalData = $userProfile['career_goal'] ?? [];
$targetRole = htmlspecialchars($goalData['target_role'] ?? 'Not selected yet');
$customGoal = htmlspecialchars($goalData['custom_goal_text'] ?? '');

$skills = $userProfile['skills'] ?? [];
$resume = $userProfile['resume'] ?? null;
$codingStats = $userProfile['coding'] ?? ['attempted' => 0, 'solved' => 0];
$interviewStats = $userProfile['interview'] ?? ['count' => 0, 'avg_score' => null];
$roadmap = $userProfile['roadmap'] ?? null;
$projectsCount = $userProfile['projects_count'] ?? 0;
?>

<main class="dashboard-container">
  <!-- Header -->
  <div class="dash-header">
    <div>
      <h1 style="font-size: 2.1rem;">Welcome, <?php echo $name; ?> 👋</h1>
      <p class="text-muted">Target Career Goal: <strong class="text-cyan"><?php echo $targetRole; ?></strong> (<?php echo $branch; ?>, <?php echo $year; ?>)</p>
    </div>
    <a href="ai-mentor.php" class="btn btn-outline">Ask AI Mentor 💬</a>
  </div>

  <div class="dash-metrics-grid">
    <div class="glass-card metric-card">
      <div class="metric-icon-box">⚡</div>
      <div>
        <div style="font-size: 1.5rem; font-weight: 800;">
          <?php echo count($skills); ?>
        </div>
        <div class="text-muted" style="font-size: 0.8rem;">Skills Analyzed</div>
      </div>
    </div>

    <div class="glass-card metric-card">
      <div class="metric-icon-box">📄</div>
      <div>
        <div style="font-size: 1.5rem; font-weight: 800;">
          <?php echo $resume ? $resume['score'] . '/100' : 'Not uploaded'; ?>
        </div>
        <div class="text-muted" style="font-size: 0.8rem;">Resume Score</div>
      </div>
    </div>

    <div class="glass-card metric-card">
      <div class="metric-icon-box">💻</div>
      <div>
        <div style="font-size: 1.5rem; font-weight: 800;">
          <?php echo $codingStats['solved']; ?> / <?php echo $codingStats['attempted']; ?>
        </div>
        <div class="text-muted" style="font-size: 0.8rem;">Questions Solved</div>
      </div>
    </div>

    <div class="glass-card metric-card">
      <div class="metric-icon-box">🎙️</div>
      <div>
        <div style="font-size: 1.5rem; font-weight: 800;">
          <?php echo $interviewStats['avg_score'] !== null ? $interviewStats['avg_score'] . '%' : 'Not calculated'; ?>
        </div>
        <div class="text-muted" style="font-size: 0.8rem;">Interview Readiness</div>
      </div>
    </div>
  </div>

  <div class="dash-main-grid">
    <div style="display: flex; flex-direction: column; gap: 20px;">

      <!-- 1. SKILLS SECTION -->
      <div class="glass-card" style="padding: 24px;">
        <div style="display:flex; justify-style:space-between; align-items:center; margin-bottom:12px;">
          <h3>My Verified Skills</h3>
          <a href="onboarding.php" style="font-size:0.8rem; color:var(--accent-blue); text-decoration:none;">Update Skills →</a>
        </div>

        <?php if (empty($skills)): ?>
          <div class="zero-state-box">
            <p class="text-muted">Skills not added yet.</p>
            <a href="career-hub.php?tab=resumeStudio" class="btn btn-outline" style="margin-top:10px; font-size:0.85rem;">Upload Resume to Extract Skills</a>
          </div>
        <?php else: ?>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <?php foreach ($skills as $s): ?>
              <span class="pill-tag verified">
                <?php echo htmlspecialchars($s['skill_name']); ?> (<?php echo htmlspecialchars($s['skill_level']); ?>)
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 2. ROADMAP SECTION -->
      <div class="glass-card" style="padding: 24px;">
        <h3>Roadmap Progress</h3>
        <?php if (!$roadmap): ?>
          <div class="zero-state-box">
            <p class="text-muted">No roadmap started yet.</p>
            <a href="career-hub.php?tab=roadmaps" class="btn btn-primary" style="margin-top:10px; font-size:0.85rem;">Start Your First Roadmap</a>
          </div>
        <?php else: ?>
          <p class="text-muted" style="font-size:0.85rem; margin:6px 0 10px;"><?php echo htmlspecialchars($roadmap['title']); ?></p>
          <div class="progress-track" style="height: 10px;">
            <div class="progress-fill" style="width: <?php echo (int)$roadmap['progress_percent']; ?>%;"></div>
          </div>
          <div style="font-size:0.8rem; color:var(--text-dim); margin-top:6px;"><?php echo (int)$roadmap['progress_percent']; ?>% Completion</div>
        <?php endif; ?>
      </div>

      <!-- 3. CODING & INTERVIEWS SECTION -->
      <div class="glass-card" style="padding: 24px;">
        <h3>Activity Analytics</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:10px;">
          <div style="background:var(--bg-secondary); padding:16px; border-radius:var(--radius-md);">
            <div style="font-size:0.85rem; color:var(--text-muted);">Coding Practice</div>
            <div style="font-size:1.2rem; font-weight:700; margin-top:4px;"><?php echo $codingStats['solved']; ?> Solved</div>
            <a href="career-hub.php?tab=coding" style="font-size:0.8rem; color:var(--accent-cyan); display:inline-block; margin-top:8px; text-decoration:none;">Practice Sandbox →</a>
          </div>

          <div style="background:var(--bg-secondary); padding:16px; border-radius:var(--radius-md);">
            <div style="font-size:0.85rem; color:var(--text-muted);">Mock Interviews</div>
            <div style="font-size:1.2rem; font-weight:700; margin-top:4px;"><?php echo $interviewStats['count']; ?> Completed</div>
            <a href="interview-lab.php" style="font-size:0.8rem; color:var(--accent-purple); display:inline-block; margin-top:8px; text-decoration:none;">Launch Interview Lab →</a>
          </div>
        </div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 20px;">
      <div class="glass-card" style="padding: 24px; border: 1px solid var(--accent-indigo);">
        <span class="glow-pill" style="margin-bottom: 10px;">Your Next Best Step</span>
        <h4 style="margin: 8px 0;"><?php echo empty($skills) ? "Upload Your Resume or Add Skills" : "Explore Projects for " . $targetRole; ?></h4>
        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 16px;">
          Reason: Based on your entry as a <?php echo $year; ?> <?php echo $branch; ?> student interested in <strong><?php echo $targetRole; ?></strong>.
        </p>
        <a href="ai-mentor.php" class="btn btn-primary" style="width: 100%; font-size: 0.9rem;">Ask AI Mentor For Guidance →</a>
      </div>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>