<?php
/**
 * Dynamic AI Reasoning Engine for Career Copilot AI
 * 
 * Context-aware system that analyzes student profiles, verified skills,
 * academic standing, and performance metrics to generate actionable career guidance.
 */

function generateAIReasoning($userProfile, $prompt) {
    $cleanPrompt = trim($prompt);
    $lowerPrompt = strtolower($cleanPrompt);

    // 1. EXTRACT STUDENT CONTEXT
    $studentName = htmlspecialchars($userProfile['name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
    
    $academic = $userProfile['academic'] ?? [];
    $branch = $academic['branch'] ?? 'Computer Science and Engineering';
    $year = $academic['year'] ?? '3rd Year';
    $semester = $academic['semester'] ?? 'Semester 5';
    
    $goalData = $userProfile['career_goal'] ?? [];
    $targetRole = is_array($goalData) ? ($goalData['target_role'] ?? 'Full Stack Developer') : (string)$goalData;
    if (empty($targetRole) || $targetRole === 'Not selected yet') {
        $targetRole = 'Software Engineer';
    }
    
    // Skills Matrix
    $skills = $userProfile['skills'] ?? [];
    $userSkillsList = [];
    foreach ($skills as $s) {
        if (is_array($s) && isset($s['skill_name'])) {
            $userSkillsList[] = strtolower($s['skill_name']);
        } elseif (is_string($s)) {
            $userSkillsList[] = strtolower($s);
        }
    }
    
    $strengthsText = $userProfile['strengths_text'] ?? '';
    $weaknessText = $userProfile['weaknesses_text'] ?? '';
    $thoughtsText = $userProfile['thoughts'] ?? '';
    
    // Performance Telemetry
    $codingStats = $userProfile['coding'] ?? ['attempted' => 0, 'solved' => 0];
    $interviewStats = $userProfile['interview'] ?? ['count' => 0, 'avg_score' => null];
    $resume = $userProfile['resume'] ?? null;
    $resumeScore = $resume ? ($resume['score'] ?? 0) : null;

    // 2. DETECT QUESTION INTENT
    $intent = detectQuestionIntent($lowerPrompt);

    // 3. GENERATE INTENT-SPECIFIC DIRECT ANSWER
    switch ($intent) {
        case 'OFF_TOPIC':
            return generateOffTopicResponse();

        case 'LEARN_NEXT':
            return generateLearnNextResponse($targetRole, $userSkillsList, $year, $semester, $weaknessText);

        case 'ROADMAP':
            return generateRoadmapResponse($targetRole, $userSkillsList, $year);

        case 'PROJECT_RECOMMENDATION':
            return generateProjectRecommendation($targetRole, $userSkillsList);

        case 'CAREER_PLAN':
            return generateCareerPlanResponse($targetRole, $year, $semester, $userSkillsList);

        case 'SKILL_GAP':
            return generateSkillGapResponse($targetRole, $userSkillsList);

        case 'CAREER_READINESS':
            return generateReadinessResponse($targetRole, $userSkillsList, $codingStats, $interviewStats, $resumeScore);

        case 'RESUME_ADVICE':
            return generateResumeAdviceResponse($targetRole, $resumeScore, $userSkillsList);

        case 'WEEKLY_PLAN':
            return generateWeeklyPlanResponse($targetRole, $userSkillsList);

        case 'EXPLAIN_SKILL':
            return generateExplainSkillResponse($cleanPrompt, $targetRole);

        default:
            return generateGeneralCareerResponse($cleanPrompt, $targetRole, $userSkillsList, $year);
    }
}

/**
 * Intent Classifier
 */
function detectQuestionIntent($lowerPrompt) {
    // Off-topic check
    $offTopicKeywords = ['weather', 'recipe', 'cook', 'movie', 'cricket', 'football', 'song', 'joke', 'president', 'capital of'];
    foreach ($offTopicKeywords as $kw) {
        if (str_contains($lowerPrompt, $kw)) {
            return 'OFF_TOPIC';
        }
    }

    if (str_contains($lowerPrompt, 'what should i learn next') || 
        str_contains($lowerPrompt, 'what to learn') || 
        str_contains($lowerPrompt, 'which skill should i learn') || 
        str_contains($lowerPrompt, 'what do i study now') ||
        str_contains($lowerPrompt, 'what should i learn')) {
        return 'LEARN_NEXT';
    }

    if (str_contains($lowerPrompt, 'roadmap') || 
        str_contains($lowerPrompt, 'learning path') || 
        str_contains($lowerPrompt, 'step by step guide')) {
        return 'ROADMAP';
    }

    if (str_contains($lowerPrompt, 'project') || 
        str_contains($lowerPrompt, 'what should i build') || 
        str_contains($lowerPrompt, 'suggest a project')) {
        return 'PROJECT_RECOMMENDATION';
    }

    if (str_contains($lowerPrompt, 'future') || 
        str_contains($lowerPrompt, 'job ready') || 
        str_contains($lowerPrompt, 'career plan') || 
        str_contains($lowerPrompt, 'how to become job ready')) {
        return 'CAREER_PLAN';
    }

    if (str_contains($lowerPrompt, 'analyze my skills') || 
        str_contains($lowerPrompt, 'skill gap') || 
        str_contains($lowerPrompt, 'missing skills') || 
        str_contains($lowerPrompt, 'which skills should i improve')) {
        return 'SKILL_GAP';
    }

    if (str_contains($lowerPrompt, 'am i ready') || 
        str_contains($lowerPrompt, 'readiness') || 
        str_contains($lowerPrompt, 'prep for interview') || 
        str_contains($lowerPrompt, 'interview')) {
        return 'CAREER_READINESS';
    }

    if (str_contains($lowerPrompt, 'resume') || str_contains($lowerPrompt, 'cv')) {
        return 'RESUME_ADVICE';
    }

    if (str_contains($lowerPrompt, 'this week') || str_contains($lowerPrompt, 'weekly plan') || str_contains($lowerPrompt, 'today')) {
        return 'WEEKLY_PLAN';
    }

    if (str_contains($lowerPrompt, 'explain') || str_contains($lowerPrompt, 'why should i learn') || str_contains($lowerPrompt, 'what is')) {
        return 'EXPLAIN_SKILL';
    }

    return 'GENERAL';
}

/**
 * RESPONSE GENERATOR: What Should I Learn Next?
 */
function generateLearnNextResponse($targetRole, $userSkills, $year, $semester, $weaknessText) {
    $hasJs = in_array('javascript', $userSkills) || in_array('html/css', $userSkills) || in_array('html', $userSkills);
    $hasPython = in_array('python', $userSkills);
    $hasBackend = in_array('php', $userSkills) || in_array('node.js', $userSkills) || in_array('node', $userSkills) || in_array('java', $userSkills);
    $hasSql = in_array('mysql', $userSkills) || in_array('sql', $userSkills);

    if (str_contains(strtolower($targetRole), 'ai') || str_contains(strtolower($targetRole), 'data') || str_contains(strtolower($targetRole), 'machine learning')) {
        $nextSkill = "Python Data Science Libraries (NumPy, Pandas & Matplotlib)";
        $why = "You are aiming for <strong>{$targetRole}</strong>. Mastering vector manipulation and data frame operations is the fundamental prerequisite before moving to PyTorch or Scikit-Learn.";
        $topics = ["NumPy ndarray manipulation & vectorization", "Pandas DataFrames, data cleaning & CSV parsing", "Exploratory Data Analysis (EDA) visualizations", "Basic statistical distributions & feature scaling"];
        $practice = "Load a public dataset (e.g., Iris or Housing prices) and perform missing value cleaning, filtering, and summary metrics.";
        $nextMilestone = "Supervised Machine Learning algorithms (Linear Regression & Decision Trees).";
    } elseif ($hasJs && !$hasBackend) {
        $nextSkill = "Backend Development & REST API Architecture (PHP or Node.js)";
        $why = "Your profile indicates frontend foundations. To advance toward your goal of <strong>{$targetRole}</strong>, you need server-side request processing and API design.";
        $topics = ["HTTP Methods (GET, POST, PUT, DELETE)", "JSON payload encoding & parsing", "Database connection & PDO parameterized queries", "Session management & JWT/Token authentication"];
        $practice = "Build a lightweight RESTful API for a Task Manager application that supports CRUD operations.";
        $nextMilestone = "Relational Database Schema Design & Normalization using MySQL.";
    } elseif (!$hasJs && !$hasPython) {
        $nextSkill = "JavaScript Fundamentals & DOM Engine";
        $why = "As an engineering student targeting <strong>{$targetRole}</strong>, learning core JavaScript syntax is essential for asynchronous logic and modern web software development.";
        $topics = ["Variables (let/const), Arrow Functions, Scope", "Array Methods (map, filter, reduce)", "DOM manipulation and Event Listeners", "Promises, Async/Await & Fetch API"];
        $practice = "Create an interactive dynamic UI component (e.g., live search filter or modal dialog) without external libraries.";
        $nextMilestone = "Asynchronous Data Fetching & Integration with Backend APIs.";
    } else {
        $nextSkill = "Database Design & SQL Query Optimization (MySQL)";
        $why = "Connecting application logic with relational storage is critical for <strong>{$targetRole}</strong> systems architecture.";
        $topics = ["Primary & Foreign Keys, Relational Integrity", "JOIN queries (INNER, LEFT, RIGHT)", "Database Normalization (1NF to 3NF)", "SQL Indexing & Query Execution Plans"];
        $practice = "Design an E-Commerce relational database schema with Users, Orders, and Products tables.";
        $nextMilestone = "Full-Stack System Deployment & Cloud Integration.";
    }

    $html = "<div class='ai-structured-response'>";
    $html .= "<div class='glow-pill' style='margin-bottom:8px;'>Target Role: {$targetRole}</div>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:6px;'>YOUR NEXT RECOMMENDED SKILL: {$nextSkill}</h3>";
    $html .= "<p style='margin-bottom:12px;'><strong>Why this skill:</strong> {$why}</p>";
    
    $html .= "<h4 style='font-size:0.92rem; color:var(--text-main); margin-bottom:4px;'>Core Topics to Study:</h4><ul style='margin-bottom:12px; padding-left:20px;'>";
    foreach ($topics as $t) {
        $html .= "<li>{$t}</li>";
    }
    $html .= "</ul>";

    $html .= "<div style='background:rgba(99,102,241,0.1); border-left:3px solid var(--accent-indigo); padding:10px 14px; border-radius:6px; margin-bottom:12px;'>";
    $html .= "<strong style='color:var(--accent-blue); font-size:0.85rem;'>💡 Hands-on Practice Task:</strong>";
    $html .= "<p style='font-size:0.88rem; margin:2px 0 0;'>{$practice}</p>";
    $html .= "</div>";

    $html .= "<p style='font-size:0.85rem; color:var(--text-muted);'><strong>Suggested Next Milestone:</strong> {$nextMilestone}</p>";
    $html .= "</div>";

    return $html;
}

/**
 * RESPONSE GENERATOR: Phased Roadmap
 */
function generateRoadmapResponse($targetRole, $userSkills, $year) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.15rem; margin-bottom:10px;'>PHASED LEARNING ROADMAP: {$targetRole}</h3>";
    
    $html .= "<div class='roadmap-step-box' style='margin-bottom:10px; padding:12px; background:var(--bg-secondary); border-left:4px solid var(--accent-cyan); border-radius:6px;'>";
    $html .= "<strong style='color:var(--accent-cyan);'>Phase 1: Foundations & Version Control</strong>";
    $html .= "<p style='font-size:0.85rem; color:var(--text-muted); margin-top:4px;'>Core Language Syntax (JavaScript/Python) • Git & GitHub Workflow • Data Structures basics.</p>";
    $html .= "</div>";

    $html .= "<div class='roadmap-step-box' style='margin-bottom:10px; padding:12px; background:var(--bg-secondary); border-left:4px solid var(--accent-indigo); border-radius:6px;'>";
    $html .= "<strong style='color:var(--accent-indigo);'>Phase 2: Core Engineering Stack</strong>";
    $html .= "<p style='font-size:0.85rem; color:var(--text-muted); margin-top:4px;'>Web Protocols (HTTP/REST) • Relational Database Schema Design (MySQL) • Backend API Development.</p>";
    $html .= "</div>";

    $html .= "<div class='roadmap-step-box' style='margin-bottom:10px; padding:12px; background:var(--bg-secondary); border-left:4px solid var(--accent-purple); border-radius:6px;'>";
    $html .= "<strong style='color:var(--accent-purple);'>Phase 3: Production Projects & Architecture</strong>";
    $html .= "<p style='font-size:0.85rem; color:var(--text-muted); margin-top:4px;'>Full-Stack CRUD Application • Authentication & Session Security • Performance & Error Boundary Handling.</p>";
    $html .= "</div>";

    $html .= "<div class='roadmap-step-box' style='margin-bottom:10px; padding:12px; background:var(--bg-secondary); border-left:4px solid var(--accent-emerald); border-radius:6px;'>";
    $html .= "<strong style='color:var(--accent-emerald);'>Phase 4: Interview Preparation & Placement Readiness</strong>";
    $html .= "<p style='font-size:0.85rem; color:var(--text-muted); margin-top:4px;'>Data Structures & Algorithms Problem Solving • System Design Basics • Mock Behavioral & Technical Practice.</p>";
    $html .= "</div>";

    $html .= "<p style='font-size:0.85rem; color:var(--accent-cyan); margin-top:10px;'>💡 <em>Check your Career Hub tab to track active progress on these stages!</em></p>";
    $html .= "</div>";

    return $html;
}

/**
 * RESPONSE GENERATOR: Project Recommendation
 */
function generateProjectRecommendation($targetRole, $userSkills) {
    if (str_contains(strtolower($targetRole), 'ai') || str_contains(strtolower($targetRole), 'machine learning')) {
        $pTitle = "Document Q&A RAG Engine with Embeddings";
        $pWhy = "Demonstrates applied machine learning by processing custom PDF documentation into vector embeddings for semantic search.";
        $tech = "Python, FastAPI, FAISS / ChromaDB, PyTorch / SentenceTransformers";
        $skillsGained = "Vector Embeddings, RAG Architecture, Fast API Endpoint Integration";
    } else {
        $pTitle = "Real-Time Student Collaboration & Task Workspace";
        $pWhy = "Connects relational database persistence with asynchronous UI updating and backend REST API routing.";
        $tech = "PHP / Node.js, MySQL, JavaScript (ES6+), HTML5/CSS3";
        $skillsGained = "REST API Routing, Database Schema Normalization, Session Auth, CRUD";
    }

    $html = "<div class='ai-structured-response'>";
    $html .= "<span class='glow-pill' style='margin-bottom:6px;'>Tailored Project Recommendation</span>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin:6px 0;'>{$pTitle}</h3>";
    $html .= "<p style='font-size:0.88rem; margin-bottom:10px;'><strong>Why this fits your profile:</strong> {$pWhy}</p>";
    $html .= "<div style='background:var(--bg-secondary); padding:12px; border-radius:8px; font-size:0.85rem;'>";
    $html .= "<div><strong>Tech Stack:</strong> <span style='color:var(--accent-cyan);'>{$tech}</span></div>";
    $html .= "<div style='margin-top:4px;'><strong>Skills Gained:</strong> {$skillsGained}</div>";
    $html .= "</div>";
    $html .= "<p style='font-size:0.84rem; color:var(--text-muted); margin-top:10px;'><strong>Actionable First Step:</strong> Open your IDE, initialize your GitHub repository, and draft the database entity relationship diagram.</p>";
    $html .= "</div>";

    return $html;
}

/**
 * RESPONSE GENERATOR: Career Progression Plan
 */
function generateCareerPlanResponse($targetRole, $year, $semester, $userSkills) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:8px;'>JOB READINESS PROGRESSION PLAN ({$targetRole})</h3>";
    $html .= "<ol style='padding-left:20px; font-size:0.88rem; line-height:1.7;'>";
    $html .= "<li><strong>Stage 1 (Current Technical Build):</strong> Consolidate core programming fundamentals and version control hygiene.</li>";
    $html .= "<li><strong>Stage 2 (Full Stack Integration):</strong> Complete at least 2 non-trivial portfolio applications involving database persistence.</li>";
    $html .= "<li><strong>Stage 3 (Algorithmic Problem Solving):</strong> Solve core array, string, and linked list coding problems to pass placement technical screenings.</li>";
    $html .= "<li><strong>Stage 4 (Interview & Resume Verification):</strong> Upload your resume to the Resume Studio to verify ATS score and take 2 mock technical interviews.</li>";
    $html .= "</ol>";
    $html .= "</div>";

    return $html;
}

/**
 * RESPONSE GENERATOR: Skill Gap Analysis
 */
function generateSkillGapResponse($targetRole, $userSkills) {
    $verifiedCount = count($userSkills);
    $formattedSkills = !empty($userSkills) ? implode(', ', array_map('ucwords', $userSkills)) : 'None logged yet';

    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:8px;'>SKILL GAP ANALYSIS FOR {$targetRole}</h3>";
    $html .= "<p style='font-size:0.88rem;'><strong>Verified Skills Logged:</strong> {$formattedSkills}</p>";
    
    $html .= "<div style='margin-top:10px; background:var(--bg-secondary); padding:12px; border-radius:8px;'>";
    $html .= "<strong style='color:var(--accent-rose); font-size:0.88rem;'>Detected Industry Skill Gaps:</strong>";
    $html .= "<ul style='margin-top:6px; padding-left:18px; font-size:0.85rem;'>";
    if (!in_array('mysql', $userSkills) && !in_array('sql', $userSkills)) {
        $html .= "<li><strong>Database Management (MySQL/SQL):</strong> Required for data persistence.</li>";
    }
    if (!in_array('git', $userSkills)) {
        $html .= "<li><strong>Version Control (Git/GitHub):</strong> Essential for team software collaboration.</li>";
    }
    if (!in_array('php', $userSkills) && !in_array('node.js', $userSkills) && !in_array('python', $userSkills)) {
        $html .= "<li><strong>Backend Programming:</strong> Server-side logic & API design.</li>";
    }
    $html .= "<li><strong>System Testing & Deployment:</strong> Cloud environment basics & automated validation.</li>";
    $html .= "</ul>";
    $html .= "</div>";

    $html .= "<p style='font-size:0.84rem; color:var(--text-muted); margin-top:10px;'>💡 <em>Focus on bridging these gaps through the Recommended Projects section in Career Hub.</em></p>";
    $html .= "</div>";

    return $html;
}

/**
 * RESPONSE GENERATOR: Readiness Evaluation
 */
function generateReadinessResponse($targetRole, $userSkills, $codingStats, $interviewStats, $resumeScore) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:8px;'>CAREER READINESS ASSESSMENT</h3>";
    
    $html .= "<ul style='padding-left:18px; font-size:0.88rem; line-height:1.6;'>";
    $html .= "<li><strong>Resume ATS Readiness:</strong> " . ($resumeScore !== null ? "{$resumeScore} / 100" : "<span style='color:var(--accent-rose);'>No resume uploaded yet</span>") . "</li>";
    $html .= "<li><strong>Verified Skills Count:</strong> " . count($userSkills) . " skills</li>";
    $html .= "<li><strong>Coding Problems Solved:</strong> {$codingStats['solved']} solved ({$codingStats['attempted']} attempted)</li>";
    $html .= "<li><strong>Mock Interview Average:</strong> " . ($interviewStats['avg_score'] !== null ? "{$interviewStats['avg_score']}%" : "<span style='color:var(--text-muted);'>No interviews taken yet</span>") . "</li>";
    $html .= "</ul>";

    if ($resumeScore === null && $codingStats['solved'] === 0) {
        $html .= "<div style='margin-top:10px; background:rgba(244,63,94,0.1); border-left:3px solid var(--accent-rose); padding:10px; border-radius:6px; font-size:0.85rem;'>";
        $html .= "<strong style='color:var(--accent-rose);'>Telemetry Recommendation:</strong> Upload your resume in the Resume Studio and complete 1 coding practice problem to calculate a precise readiness index.";
        $html .= "</div>";
    } else {
        $html .= "<p style='font-size:0.85rem; color:var(--accent-emerald); margin-top:8px;'>✓ Profile telemetry active. Continue practice in the Mock Interview Lab to boost your readiness score.</p>";
    }

    $html .= "</div>";
    return $html;
}

/**
 * RESPONSE GENERATOR: Resume Advice
 */
function generateResumeAdviceResponse($targetRole, $resumeScore, $userSkills) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:8px;'>RESUME OPTIMIZATION ADVICE</h3>";
    $html .= "<p style='font-size:0.88rem;'>To tailor your resume for <strong>{$targetRole}</strong> opportunities:</p>";
    $html .= "<ol style='padding-left:20px; font-size:0.85rem; line-height:1.6;'>";
    $html .= "<li><strong>Quantify Project Results:</strong> Instead of 'Built web app', write 'Engineered full-stack task portal using PHP & MySQL, handling 100+ simulated requests'.</li>";
    $html .= "<li><strong>Technical Keyword Alignment:</strong> Include explicit framework and database keywords in a dedicated Technical Skills section.</li>";
    $html .= "<li><strong>Clean Formatting:</strong> Use standard single-column layout so ATS scanners extract text correctly.</li>";
    $html .= "</ol>";
    $html .= "<p style='font-size:0.84rem; color:var(--text-muted); margin-top:8px;'>💡 <em>Upload your PDF resume to the Resume Studio tab to run automated text analysis.</em></p>";
    $html .= "</div>";
    return $html;
}

/**
 * RESPONSE GENERATOR: Weekly Action Plan
 */
function generateWeeklyPlanResponse($targetRole, $userSkills) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:8px;'>ACTIONABLE WEEKLY GOALS</h3>";
    $html .= "<ul style='padding-left:18px; font-size:0.88rem; line-height:1.6;'>";
    $html .= "<li><strong>Mon - Tue:</strong> Study 1 new core topic in your target tech stack (e.g., Asynchronous Promises or SQL JOIN queries).</li>";
    $html .= "<li><strong>Wed - Thu:</strong> Solve 2 coding problems in the Coding Sandbox.</li>";
    $html .= "<li><strong>Fri - Sat:</strong> Dedicate 2 hours to building your active portfolio project.</li>";
    $html .= "<li><strong>Sunday:</strong> Run a 15-minute simulation in the Mock Interview Lab to evaluate verbal articulation.</li>";
    $html .= "</ul>";
    $html .= "</div>";
    return $html;
}

/**
 * RESPONSE GENERATOR: Explain Skill
 */
function generateExplainSkillResponse($prompt, $targetRole) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.1rem; margin-bottom:6px;'>CONCEPT EXPLANATION</h3>";
    $html .= "<p style='font-size:0.88rem;'>Understanding this concept is essential for your progression as a <strong>{$targetRole}</strong>.</p>";
    $html .= "<p style='font-size:0.88rem; margin-top:6px;'>Core technical components require mastering fundamentals, practical syntax implementation, and error boundary handling. Combine theoretical documentation with hands-on practice projects for optimal retention.</p>";
    $html .= "</div>";
    return $html;
}

/**
 * RESPONSE GENERATOR: General Fallback
 */
function generateGeneralCareerResponse($prompt, $targetRole, $userSkills, $year) {
    $html = "<div class='ai-structured-response'>";
    $html .= "<h3 style='color:var(--accent-cyan); font-size:1.05rem; margin-bottom:6px;'>CAREER COPILOT GUIDANCE</h3>";
    $html .= "<p style='font-size:0.88rem;'>To achieve your target goal as a <strong>{$targetRole}</strong>, focus on building verifiable evidence: write clean code, build portfolio projects with relational database persistence, and practice technical problem solving.</p>";
    $html .= "<p style='font-size:0.84rem; color:var(--text-muted); margin-top:8px;'>💡 Ask me specific questions like: <em>'What should I learn next?'</em>, <em>'Give me a roadmap'</em>, or <em>'Suggest a project'</em> for detailed step-by-step guidance.</p>";
    $html .= "</div>";
    return $html;
}

/**
 * RESPONSE GENERATOR: Off Topic Filter
 */
function generateOffTopicResponse() {
    return "<div class='ai-structured-response'>
        <p style='font-size:0.9rem; color:var(--accent-rose);'><strong>Career Focus Notice:</strong></p>
        <p style='font-size:0.88rem; color:var(--text-muted);'>I am specialized in helping you with your academic learning, technical skills, career roadmaps, projects, coding practice, resume optimization, and interview preparation. Please ask me a question related to your tech career journey!</p>
    </div>";
}
?>