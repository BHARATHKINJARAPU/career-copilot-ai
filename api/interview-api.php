<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse('error', ['message' => 'Unauthorized user session']);
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = sanitizeInput($input['action'] ?? 'evaluate_question');

$pdo = getDBConnection();

$questionBank = [
    'Full Stack Developer' => [
        [
            'id' => 1,
            'question' => 'Explain how the JavaScript Event Loop handles asynchronous callbacks in the microtask queue versus the macrotask queue.',
            'concepts' => ['event loop', 'call stack', 'microtask', 'macrotask', 'promise', 'settimeout', 'queue', 'async'],
            'reference' => 'The JavaScript Event Loop coordinates execution between the call stack, microtask queue, and macrotask queue. Synchronous code executes first on the call stack. When async operations resolve, Promise callbacks (.then, async/await) enter the Microtask Queue, while timers (setTimeout, setInterval) enter the Macrotask Queue. After the call stack clears, the Event Loop processes ALL pending microtasks before processing the next single macrotask.'
        ],
        [
            'id' => 2,
            'question' => 'What are the key differences between SQL and NoSQL databases regarding ACID transactions, schema design, and horizontal scaling?',
            'concepts' => ['sql', 'nosql', 'acid', 'relational', 'schema', 'scaling', 'horizontal', 'vertical', 'joins', 'document'],
            'reference' => 'SQL databases (e.g., MySQL, PostgreSQL) are relational, use structured schemas with tables/columns, enforce strict ACID transactions (Atomicity, Consistency, Isolation, Durability), and scale vertically. NoSQL databases (e.g., MongoDB, Redis) are non-relational, schema-flexible, scale horizontally across clusters, and prioritize eventual consistency (BASE model) for unstructured or rapid document storage.'
        ],
        [
            'id' => 3,
            'question' => 'How do REST APIs use HTTP methods (GET, POST, PUT, DELETE) and status codes for scalable client-server resource management?',
            'concepts' => ['rest', 'http', 'get', 'post', 'put', 'delete', 'status code', 'json', 'stateless', 'resource', '200', '404'],
            'reference' => 'REST (Representational State Transfer) is a stateless client-server architecture. It maps standard HTTP methods to resource actions: GET retrieves resources, POST creates new resources, PUT updates existing resources, and DELETE removes resources. Standard HTTP status codes communicate result states (200 OK, 201 Created, 400 Bad Request, 401 Unauthorized, 404 Not Found, 500 Server Error).'
        ],
        [
            'id' => 4,
            'question' => 'Explain Session-based Authentication versus JSON Web Tokens (JWT). How do you protect applications against XSS and CSRF attacks?',
            'concepts' => ['jwt', 'session', 'cookie', 'token', 'xss', 'csrf', 'httpOnly', 'sameSite', 'authentication', 'security'],
            'reference' => 'Session auth stores session state on the server and passes a session ID in an HTTP cookie. JWT is stateless; the client stores a signed token containing user claims. To prevent Cross-Site Scripting (XSS), sanitize user inputs and store JWTs in HTTPOnly, Secure cookies rather than localStorage. To prevent Cross-Site Request Forgery (CSRF), implement Anti-CSRF tokens and set SameSite=Strict cookie attributes.'
        ],
        [
            'id' => 5,
            'question' => 'What is database normalization (1NF to 3NF) and how do primary and foreign keys maintain relational integrity?',
            'concepts' => ['normalization', '1nf', '2nf', '3nf', 'primary key', 'foreign key', 'redundancy', 'integrity', 'relationships'],
            'reference' => 'Normalization organizes relational tables to minimize data redundancy and dependency anomalies. 1NF eliminates duplicate columns and requires atomic values. 2NF eliminates partial functional dependencies. 3NF eliminates transitive dependencies. Primary Keys uniquely identify each record in a table, while Foreign Keys link rows across tables to enforce referential integrity.'
        ]
    ],
    'AI/ML Engineer' => [
        [
            'id' => 1,
            'question' => 'Explain the bias-variance tradeoff and how regularization techniques (L1 Lasso & L2 Ridge) help prevent model overfitting.',
            'concepts' => ['bias', 'variance', 'overfitting', 'underfitting', 'regularization', 'l1', 'l2', 'lasso', 'ridge', 'generalization'],
            'reference' => 'The bias-variance tradeoff balances model simplicity against flexibility. High bias leads to underfitting (oversimplified model), while high variance leads to overfitting (memorizing training noise). Regularization adds a penalty term to the loss function to constrain weights: L1 (Lasso) adds absolute weight values encouraging feature sparsity, while L2 (Ridge) adds squared weight values to prevent large parameter magnitudes.'
        ],
        [
            'id' => 2,
            'question' => 'What are the core differences between Supervised, Unsupervised, and Reinforcement Learning paradigms?',
            'concepts' => ['supervised', 'unsupervised', 'reinforcement', 'labels', 'clustering', 'reward', 'agent', 'environment', 'classification'],
            'reference' => 'Supervised learning trains models on labeled input-output data pairs for classification or regression. Unsupervised learning analyzes unlabeled data to discover hidden patterns, clusters, or dimensionality reductions (e.g., K-Means, PCA). Reinforcement learning trains an autonomous agent to take sequential actions in an environment to maximize cumulative reward.'
        ],
        [
            'id' => 3,
            'question' => 'How does a Convolutional Neural Network (CNN) extract spatial features using convolution layers, pooling, and activation functions?',
            'concepts' => ['cnn', 'convolution', 'filter', 'kernel', 'pooling', 'relu', 'feature map', 'spatial', 'flatten', 'dense'],
            'reference' => 'CNNs process grid data like images. Convolutional layers apply learnable filters (kernels) to extract local spatial features (edges, textures). Non-linear activation functions like ReLU introduce non-linearity. Pooling layers (Max Pooling) downsample feature maps to reduce spatial dimensions and computational complexity while maintaining translation invariance.'
        ],
        [
            'id' => 4,
            'question' => 'Explain Retrieval-Augmented Generation (RAG) and how vector databases with embeddings improve Large Language Model responses.',
            'concepts' => ['rag', 'embeddings', 'vector database', 'retrieval', 'llm', 'semantic search', 'faiss', 'chroma', 'context'],
            'reference' => 'RAG enhances LLM responses by retrieving relevant external knowledge prior to generation. Text documents are converted into dense vector embeddings and stored in a vector database (e.g., FAISS, Chroma). When a user asks a query, semantic similarity search retrieves relevant document chunks, which are passed into the LLM prompt as grounding context to eliminate hallucinations.'
        ],
        [
            'id' => 5,
            'question' => 'What evaluation metrics (Precision, Recall, F1-Score, ROC-AUC) should you use for an imbalanced classification dataset?',
            'concepts' => ['precision', 'recall', 'f1-score', 'roc-auc', 'imbalanced', 'confusion matrix', 'false positive', 'false negative'],
            'reference' => 'Accuracy is misleading for imbalanced datasets because predicting the majority class yields high accuracy. Precision measures how many predicted positives are truly positive (TP / (TP + FP)). Recall measures how many actual positives were correctly identified (TP / (TP + FN)). F1-Score is the harmonic mean of Precision and Recall. ROC-AUC evaluates true positive rate versus false positive rate across classification thresholds.'
        ]
    ]
];

if ($action === 'get_questions') {
    $role = sanitizeInput($input['role'] ?? 'Full Stack Developer');
    $questions = $questionBank[$role] ?? $questionBank['Full Stack Developer'];
    
    // Return question text without reference answers
    $cleanQuestions = array_map(function($q) {
        return [
            'id' => $q['id'],
            'question' => $q['question']
        ];
    }, $questions);

    jsonResponse('success', [
        'role' => $role,
        'total_questions' => count($cleanQuestions),
        'questions' => $cleanQuestions
    ]);
}

if ($action === 'evaluate_question') {
    $role = sanitizeInput($input['role'] ?? 'Full Stack Developer');
    $qId = (int)($input['question_id'] ?? 1);
    $studentAnswer = sanitizeInput($input['student_answer'] ?? '');
    $difficulty = sanitizeInput($input['difficulty'] ?? 'Intermediate');

    if (empty(trim($studentAnswer))) {
        jsonResponse('error', ['message' => 'Please provide an answer before submitting.']);
    }

    $roleQuestions = $questionBank[$role] ?? $questionBank['Full Stack Developer'];
    $matchedQuestion = null;
    foreach ($roleQuestions as $q) {
        if ($q['id'] === $qId) {
            $matchedQuestion = $q;
            break;
        }
    }

    if (!$matchedQuestion) {
        $matchedQuestion = $roleQuestions[0];
    }

    // Evaluate answer against expected concepts
    $eval = evaluateTechnicalAnswer($studentAnswer, $matchedQuestion['concepts'], $matchedQuestion['reference'], $difficulty);

    jsonResponse('success', [
        'question_id' => $qId,
        'question_text' => $matchedQuestion['question'],
        'question_score' => $eval['question_score'],
        'max_score' => $eval['max_score'],
        'is_correct' => $eval['is_correct'],
        'evaluation' => $eval['evaluation'],
        'correct_answer' => $matchedQuestion['reference'],
        'strengths' => $eval['strengths'],
        'mistakes' => $eval['mistakes'],
        'missing_points' => $eval['missing_points'],
        'improvement_advice' => $eval['improvement_advice']
    ]);
}

if ($action === 'finish_interview') {
    $role = sanitizeInput($input['role'] ?? 'Full Stack Developer');
    $track = sanitizeInput($input['track'] ?? 'Technical');
    $difficulty = sanitizeInput($input['difficulty'] ?? 'Intermediate');
    $evaluations = $input['evaluations'] ?? [];

    $totalScore = 0;
    $totalPossible = 0;
    $strengthsList = [];
    $weaknessList = [];

    foreach ($evaluations as $e) {
        $s = (int)($e['question_score'] ?? 0);
        $m = (int)($e['max_score'] ?? 10);
        $totalScore += $s;
        $totalPossible += $m;

        if (!empty($e['strengths'])) {
            $strengthsList = array_merge($strengthsList, $e['strengths']);
        }
        if (!empty($e['missing_points'])) {
            $weaknessList = array_merge($weaknessList, $e['missing_points']);
        }
    }

    if ($totalPossible === 0) {
        $totalPossible = 50;
    }

    // Exact Mathematical Percentage Calculation
    $finalPercentage = round(($totalScore / $totalPossible) * 100);
    $finalPercentage = min(100, max(0, $finalPercentage));

    $performanceGrade = 'Needs Improvement';
    if ($finalPercentage >= 85) {
        $performanceGrade = 'Excellent (Placement Ready)';
    } elseif ($finalPercentage >= 70) {
        $performanceGrade = 'Good (Solid Foundation)';
    } elseif ($finalPercentage >= 50) {
        $performanceGrade = 'Satisfactory (Developing)';
    }

    $feedbackReport = json_encode([
        'percentage' => $finalPercentage,
        'grade' => $performanceGrade,
        'strengths' => array_unique($strengthsList),
        'weaknesses' => array_unique($weaknessList),
        'total_evaluated' => count($evaluations)
    ]);

    // Store in MySQL database
    $stmt = $pdo->prepare("INSERT INTO interviews (user_id, interview_type, career_role, difficulty, score, feedback) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $track, $role, $difficulty, $finalPercentage, $feedbackReport]);

    jsonResponse('success', [
        'final_score' => $finalPercentage,
        'total_score' => $totalScore,
        'total_possible' => $totalPossible,
        'grade' => $performanceGrade,
        'strengths' => array_slice(array_unique($strengthsList), 0, 4),
        'weaknesses' => array_slice(array_unique($weaknessList), 0, 4)
    ]);
}

function evaluateTechnicalAnswer($answerText, $expectedConcepts, $referenceAnswer, $difficulty) {
    $cleanAnswer = strtolower(trim($answerText));
    $wordCount = str_word_count($cleanAnswer);

    // 1. Concept Keyword Matching
    $matchedConcepts = [];
    $missingConcepts = [];

    foreach ($expectedConcepts as $concept) {
        if (str_contains($cleanAnswer, strtolower($concept))) {
            $matchedConcepts[] = ucfirst($concept);
        } else {
            $missingConcepts[] = ucfirst($concept);
        }
    }

    $matchedCount = count($matchedConcepts);
    $totalConcepts = count($expectedConcepts);
    $matchRatio = $totalConcepts > 0 ? ($matchedCount / $totalConcepts) : 0;

    // 2. Score Calculation (0 to 10 scale)
    $score = 0;
    if ($wordCount < 6) {
        $score = 2; // Extremely brief / non-descriptive answer
    } elseif ($matchRatio >= 0.7 && $wordCount >= 25) {
        $score = 10;
    } elseif ($matchRatio >= 0.5 && $wordCount >= 18) {
        $score = 8;
    } elseif ($matchRatio >= 0.3 || $wordCount >= 12) {
        $score = 6;
    } else {
        $score = 4;
    }

    // Difficulty Adjustment
    if ($difficulty === 'Advanced' && $score > 4) {
        $score = max(3, $score - 1);
    }

    $isCorrect = $score >= 7;

    // 3. Feedback Arrays
    $strengths = [];
    $mistakes = [];
    $improvementAdvice = [];

    if (!empty($matchedConcepts)) {
        $strengths[] = "Accurately discussed key terms: " . implode(', ', array_slice($matchedConcepts, 0, 4)) . ".";
    } else {
        $strengths[] = "Attempted response but omitted core technical terminology.";
    }

    if ($wordCount < 15) {
        $mistakes[] = "Response was too brief. Expand with concrete technical explanations and examples.";
    }

    if (!empty($missingConcepts)) {
        $mistakes[] = "Missing key concepts: " . implode(', ', array_slice($missingConcepts, 0, 4)) . ".";
    }

    if ($score >= 8) {
        $evaluation = "Excellent technical answer! Your response clearly covers the required concepts with proper domain terminology.";
        $improvementAdvice[] = "Review advanced system trade-offs to prepare for senior-level follow-up questions.";
    } elseif ($score >= 6) {
        $evaluation = "Good response with partial coverage. You demonstrated foundational understanding but missed key implementation details.";
        $improvementAdvice[] = "Include specific API syntax, status codes, or structural examples to earn full credit.";
    } else {
        $evaluation = "Insufficient answer. The response lacks key technical concepts required for this role profile.";
        $improvementAdvice[] = "Study the reference answer carefully and practice explaining core principles step-by-step.";
    }

    return [
        'question_score' => $score,
        'max_score' => 10,
        'is_correct' => $isCorrect,
        'evaluation' => $evaluation,
        'strengths' => $strengths,
        'mistakes' => $mistakes,
        'missing_points' => array_map(fn($c) => "Cover concept: " . $c, $missingConcepts),
        'improvement_advice' => $improvementAdvice
    ];
}
?>