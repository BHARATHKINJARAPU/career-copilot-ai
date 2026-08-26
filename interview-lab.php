<?php
require_once 'includes/auth-check.php';
require_once 'includes/functions.php';

$userId = $_SESSION['user_id'];
checkOnboardingOrRedirect($userId);

$extraCss = 'interview-lab.css';
$extraJs = 'interview.js';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="interview-container">
  <div style="text-align: center; margin-bottom: 30px;">
    <span class="glow-pill">AI Evaluation Suite</span>
    <h1 class="gradient-text" style="font-size: 2.5rem; margin-top: 10px;">Mock Interview Lab</h1>
    <p class="text-muted">Simulate technical and behavioral interviews with real-time AI concept evaluation.</p>
  </div>

  <!-- Setup Section -->
  <div id="interviewSetup" class="glass-card" style="padding: 30px; max-width: 800px; margin: 0 auto;">
    <h3 style="margin-bottom: 20px;">Configure Session</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
      <div>
        <label style="font-size: 0.85rem; color:var(--text-muted); display:block; margin-bottom: 6px;">Interview Track</label>
        <select id="trackSelect" class="form-control" style="width:100%;">
          <option value="Technical">Technical (Systems & Algorithms)</option>
          <option value="HR">HR & Behavioral (STAR Method)</option>
        </select>
      </div>

      <div>
        <label style="font-size: 0.85rem; color:var(--text-muted); display:block; margin-bottom: 6px;">Role Profile</label>
        <select id="roleSelect" class="form-control" style="width:100%;">
          <option value="Full Stack Developer">Full Stack Developer</option>
          <option value="AI/ML Engineer">AI / ML Engineer</option>
        </select>
      </div>

      <div>
        <label style="font-size: 0.85rem; color:var(--text-muted); display:block; margin-bottom: 6px;">Difficulty Level</label>
        <select id="difficultySelect" class="form-control" style="width:100%;">
          <option value="Beginner">Beginner</option>
          <option value="Intermediate" selected>Intermediate</option>
          <option value="Advanced">Advanced</option>
        </select>
      </div>
    </div>

    <button id="startInterviewBtn" class="btn btn-primary" style="width: 100%; padding: 14px; font-size:1rem;">Begin Mock Interview 🎙️</button>
  </div>

  <!-- Question Session Section -->
  <div id="interviewSession" style="display:none; max-width: 900px; margin: 0 auto;">
    <div class="progress-track" style="height: 8px; margin-bottom: 24px;">
      <div class="progress-fill" id="interviewProgressFill" style="width: 20%;"></div>
    </div>

    <div class="glass-card" style="padding: 30px;">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span class="glow-pill" id="qNumber">Question 1 of 5</span>
        <span id="roleBadge" class="text-cyan" style="font-weight:700; font-size:0.88rem;">Full Stack Developer</span>
      </div>

      <h2 id="qText" style="margin: 20px 0 14px; font-size: 1.35rem; line-height: 1.5;">Loading question...</h2>
      
      <!-- Input Area -->
      <div id="answerInputGroup">
        <label style="font-size: 0.85rem; color:var(--text-muted); display:block; margin-bottom: 6px;">Your Technical Response:</label>
        <textarea id="answerText" class="form-control" style="width:100%; height:160px; resize:vertical;" placeholder="Type your answer here... Be thorough and use technical terms to demonstrate core concepts."></textarea>

        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
          <button id="submitAnswerBtn" class="btn btn-primary" style="padding: 12px 24px;">Submit Answer & Evaluate ⚡</button>
        </div>
      </div>

      <!-- Single Question Evaluation Feedback Card -->
      <div id="singleEvaluationCard" style="display:none; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-glass);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
          <h4 style="font-size:1.1rem; color:var(--text-main);">AI Question Evaluation</h4>
          <span class="score-badge" id="qScoreBadge" style="font-size:1.4rem;">8 / 10</span>
        </div>

        <div style="background: var(--bg-secondary); padding: 16px; border-radius: var(--radius-md); margin-bottom: 14px;">
          <strong style="color:var(--accent-cyan); display:block; margin-bottom:4px;">Evaluation Summary:</strong>
          <p id="evalSummaryText" style="font-size:0.9rem; margin:0;"></p>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom: 14px;">
          <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid var(--accent-emerald); padding: 14px; border-radius: var(--radius-md);">
            <strong style="color:var(--accent-emerald); font-size:0.88rem;">What You Did Well:</strong>
            <ul id="strengthsList" style="font-size:0.84rem; padding-left:18px; margin-top:6px;"></ul>
          </div>

          <div style="background: rgba(244, 63, 94, 0.08); border: 1px solid var(--accent-rose); padding: 14px; border-radius: var(--radius-md);">
            <strong style="color:var(--accent-rose); font-size:0.88rem;">What You Missed / Mistakes:</strong>
            <ul id="mistakesList" style="font-size:0.84rem; padding-left:18px; margin-top:6px;"></ul>
          </div>
        </div>

        <div style="background: rgba(99, 102, 241, 0.1); border-left: 4px solid var(--accent-indigo); padding: 16px; border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-bottom: 18px;">
          <strong style="color:var(--accent-blue); font-size:0.88rem; display:block; margin-bottom:4px;">Reference / Correct Answer:</strong>
          <p id="referenceAnswerText" style="font-size:0.88rem; margin:0; line-height:1.6; color:var(--text-main);"></p>
        </div>

        <div style="display: flex; justify-content: flex-end;">
          <button id="nextQuestionBtn" class="btn btn-primary" style="padding: 12px 24px;">Next Question →</button>
        </div>
      </div>

    </div>
  </div>

  <!-- Final Interview Performance Report -->
  <div id="interviewReport" class="glass-card" style="display:none; padding:35px; max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
      <div>
        <span class="glow-pill" style="border-color: var(--accent-emerald); color: var(--accent-emerald);">Assessment Completed</span>
        <h2 style="font-size: 2rem; margin-top: 8px;">Final Performance Report</h2>
      </div>
      <div class="score-badge" id="finalScoreBadge" style="font-size:2.2rem;">0% Score</div>
    </div>

    <div style="margin-top: 20px; background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md);">
      <h4 style="color:var(--accent-cyan); margin-bottom:6px;">Performance Grade</h4>
      <p id="performanceGradeText" style="font-size:1.05rem; font-weight:700; margin:0; color:var(--text-main);"></p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 20px 0;">
      <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md);">
        <h4 style="color: var(--accent-emerald); margin-bottom: 8px;">Demonstrated Strengths</h4>
        <ul id="reportStrengths" style="font-size: 0.88rem; padding-left: 18px; line-height:1.6;"></ul>
      </div>

      <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md);">
        <h4 style="color: var(--accent-rose); margin-bottom: 8px;">Concepts to Revise</h4>
        <ul id="reportWeaknesses" style="font-size: 0.88rem; padding-left: 18px; line-height:1.6;"></ul>
      </div>
    </div>

    <div style="margin-top: 30px; text-align: right; display:flex; gap:12px; justify-content:flex-end;">
      <button onclick="window.location.reload();" class="btn btn-outline">Retake Interview 🔄</button>
      <a href="dashboard.php" class="btn btn-primary">Save & View Dashboard →</a>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>