<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Auth Guard Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

// Check if student has already completed onboarding
$stmt = $pdo->prepare("SELECT onboarding_completed, name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user && (int)$user['onboarding_completed'] === 1) {
    header("Location: dashboard.php");
    exit();
}

$extraCss = 'onboarding.css';
$extraJs = 'onboarding.js';
require_once 'includes/header.php';
?>

<main class="onboarding-wrapper">
  <div class="onboarding-card glass-card">
    
    <!-- Header & Step Tracker -->
    <div class="onboarding-header">
      <div class="logo-badge">
        <div class="logo-icon-box">🚀</div>
        <span>Career Copilot AI</span>
      </div>
      <h2>Let's build your Career Profile</h2>
      <p class="text-muted">Tell us about yourself so we can personalize your career journey.</p>

      <div class="step-progress-wrapper">
        <div class="step-progress-bar">
          <div class="step-indicator active" data-step="1">
            <span class="step-num">1</span>
            <span class="step-label">Personal</span>
          </div>
          <div class="step-line"></div>
          <div class="step-indicator" data-step="2">
            <span class="step-num">2</span>
            <span class="step-label">Academic</span>
          </div>
          <div class="step-line"></div>
          <div class="step-indicator" data-step="3">
            <span class="step-num">3</span>
            <span class="step-label">Goals</span>
          </div>
          <div class="step-line"></div>
          <div class="step-indicator" data-step="4">
            <span class="step-num">4</span>
            <span class="step-label">Skills</span>
          </div>
          <div class="step-line"></div>
          <div class="step-indicator" data-step="5">
            <span class="step-num">5</span>
            <span class="step-label">Situation</span>
          </div>
        </div>

        <div class="overall-progress-track">
          <div class="overall-progress-fill" id="onboardingProgressFill" style="width: 20%;"></div>
        </div>
        <div class="step-title-text" id="stepTitleText">Step 1 of 5: Personal & Campus Details</div>
      </div>
    </div>

    <form id="onboardingForm" action="actions/onboarding-action.php" method="POST" novalidate>
      
      <div class="onboarding-step-pane active" data-pane="1">
        <div class="pane-section-header">
          <span class="pane-icon">👤</span>
          <div>
            <h4>Personal & Campus Profile</h4>
            <p class="text-muted">Basic contact details and institution location.</p>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Full Name <span class="req-star">*</span></label>
          <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required placeholder="e.g. Alex Mercer">
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">College / University <span class="req-star">*</span></label>
            <input type="text" name="institution" class="form-control" required placeholder="e.g. National Institute of Technology">
          </div>

          <div class="form-group">
            <label class="form-label">City & State <span class="req-star">*</span></label>
            <input type="text" name="location" class="form-control" required placeholder="e.g. Mumbai, Maharashtra">
          </div>
        </div>
      </div>

      <div class="onboarding-step-pane" data-pane="2">
        <div class="pane-section-header">
          <span class="pane-icon">🎓</span>
          <div>
            <h4>B.Tech Academic Details</h4>
            <p class="text-muted">Specify your engineering program and current semester standing.</p>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Degree Program</label>
            <input type="text" name="degree" class="form-control readonly-field" value="B.Tech" readonly>
          </div>

          <div class="form-group">
            <label class="form-label">Engineering Branch <span class="req-star">*</span></label>
            <select name="branch" class="form-control" required>
              <option value="Computer Science and Engineering">Computer Science and Engineering</option>
              <option value="Information Technology">Information Technology</option>
              <option value="Artificial Intelligence and Machine Learning">Artificial Intelligence & ML</option>
              <option value="Data Science">Data Science</option>
              <option value="Electronics and Communication Engineering">Electronics & Communication (ECE)</option>
              <option value="Electrical and Electronics Engineering">Electrical Engineering (EEE)</option>
              <option value="Mechanical Engineering">Mechanical Engineering</option>
              <option value="Civil Engineering">Civil Engineering</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Current Academic Year <span class="req-star">*</span></label>
            <select name="year" class="form-control" required>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year" selected>3rd Year</option>
              <option value="4th Year">4th Year (Final Year)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Current Semester <span class="req-star">*</span></label>
            <select name="semester" class="form-control" required>
              <option value="Semester 1">Semester 1</option>
              <option value="Semester 2">Semester 2</option>
              <option value="Semester 3">Semester 3</option>
              <option value="Semester 4">Semester 4</option>
              <option value="Semester 5" selected>Semester 5</option>
              <option value="Semester 6">Semester 6</option>
              <option value="Semester 7">Semester 7</option>
              <option value="Semester 8">Semester 8</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Favorite Academic Subjects (Optional)</label>
          <textarea name="academic_interests" class="form-control" rows="2" placeholder="e.g. Data Structures, Web Technology, Operating Systems, Database Systems..."></textarea>
        </div>
      </div>

      <div class="onboarding-step-pane" data-pane="3">
        <div class="pane-section-header">
          <span class="pane-icon">🎯</span>
          <div>
            <h4>Target Role & Career Ambition</h4>
            <p class="text-muted">Define the tech role you aim to achieve after graduation.</p>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Target Role <span class="req-star">*</span></label>
          <select name="target_role" id="targetRoleSelect" class="form-control" required>
            <option value="Full Stack Developer">Full Stack Developer</option>
            <option value="Frontend Developer">Frontend Developer</option>
            <option value="Backend Developer">Backend Developer</option>
            <option value="AI/ML Engineer">AI / ML Engineer</option>
            <option value="Data Analyst">Data Analyst</option>
            <option value="Data Scientist">Data Scientist</option>
            <option value="Cybersecurity Specialist">Cybersecurity Specialist</option>
            <option value="Cloud / DevOps Engineer">Cloud / DevOps Engineer</option>
            <option value="Mobile Developer">Mobile Developer</option>
            <option value="Software Engineer">Software Engineer</option>
            <option value="UI/UX Designer">UI/UX Designer</option>
            <option value="Not sure yet">Not sure yet</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Tell your AI Mentor about your goal in your own words</label>
          <textarea name="custom_goal_text" class="form-control" rows="3" placeholder="e.g. I want to build full-stack web applications using Node.js and React, and prepare for campus placement coding drives..."></textarea>
        </div>
      </div>

      <div class="onboarding-step-pane" data-pane="4">
        <div class="pane-section-header">
          <span class="pane-icon">⚡</span>
          <div>
            <h4>Current Technical Skills</h4>
            <p class="text-muted">Select technologies you know. (Starts clean at 0 skills if none are added).</p>
          </div>
        </div>

        <div class="skill-input-builder">
          <div class="skill-input-row">
            <input type="text" id="skillNameInput" class="form-control" placeholder="e.g. Python, HTML, C++, MySQL, React...">
            <select id="skillLevelSelect" class="form-control level-select">
              <option value="Beginner">Beginner</option>
              <option value="Intermediate" selected>Intermediate</option>
              <option value="Advanced">Advanced</option>
            </select>
            <button type="button" id="addSkillBtn" class="btn btn-outline add-skill-btn">+ Add Skill</button>
          </div>

          <!-- Quick Suggestion Chips -->
          <div class="quick-chips-box">
            <span class="quick-chip-title">Quick Add Popular Skills:</span>
            <div class="quick-chips-list">
              <button type="button" class="quick-skill-chip" data-skill="Python">+ Python</button>
              <button type="button" class="quick-skill-chip" data-skill="HTML/CSS">+ HTML/CSS</button>
              <button type="button" class="quick-skill-chip" data-skill="JavaScript">+ JavaScript</button>
              <button type="button" class="quick-skill-chip" data-skill="C++">+ C++</button>
              <button type="button" class="quick-skill-chip" data-skill="Java">+ Java</button>
              <button type="button" class="quick-skill-chip" data-skill="MySQL">+ MySQL</button>
              <button type="button" class="quick-skill-chip" data-skill="React">+ React</button>
              <button type="button" class="quick-skill-chip" data-skill="Git">+ Git</button>
            </div>
          </div>

          <!-- Added Tags Container -->
          <div id="skillsListContainer" class="skills-tag-container">
            <div class="zero-skills-notice" id="noSkillsText">
              <span>💡 No skills added yet. Your profile will start clean at 0 skills. Click a quick skill chip above or type a skill to add.</span>
            </div>
          </div>
          <input type="hidden" name="skills_json" id="skillsJsonInput" value="[]">
        </div>
      </div>

      <div class="onboarding-step-pane" data-pane="5">
        <div class="pane-section-header">
          <span class="pane-icon">📝</span>
          <div>
            <h4>Strengths, Weaknesses & Copilot Notes</h4>
            <p class="text-muted">Help Career Copilot AI understand your exact situation and needs.</p>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Current Strengths</label>
            <textarea name="strengths_text" class="form-control" rows="3" placeholder="e.g. Logical problem solving, good at UI layout design, fast learner..."></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Current Weak Areas / Difficulties</label>
            <textarea name="weaknesses_text" class="form-control" rows="3" placeholder="e.g. Struggle with recursion, database normalization, or public speaking in interviews..."></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">What would you like Career Copilot AI to help you with?</label>
          <textarea name="expectations_text" class="form-control" rows="2" placeholder="e.g. Guide me through project selection, coding practice, and mock interviews for campus placements."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Tell your Career Copilot anything about your current situation</label>
          <textarea name="raw_situation_notes" class="form-control" rows="3" placeholder="Example: I'm a third-year CSE student. I know basic Python and C++. I want to become job-ready before graduation and need a clear roadmap to follow."></textarea>
        </div>
      </div>

      <!-- Action Navigation Buttons -->
      <div class="onboarding-actions">
        <button type="button" id="prevBtn" class="btn btn-outline" style="display:none;">← Back</button>
        <div style="flex:1;"></div>
        <button type="button" id="nextBtn" class="btn btn-primary">Next Step →</button>
        <button type="submit" id="submitOnboardingBtn" class="btn btn-primary" style="display:none;">Finish & Launch Dashboard 🚀</button>
      </div>
    </form>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>