-- STREAMING_CHUNK:Initializing database creation and charset configuration...
CREATE DATABASE IF NOT EXISTS `career_copilot` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `career_copilot`;

SET FOREIGN_KEY_CHECKS = 0;

-- STREAMING_CHUNK:Creating core user authentication table...
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `onboarding_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating academic profile tables...
CREATE TABLE IF NOT EXISTS `student_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `degree` VARCHAR(50) NOT NULL DEFAULT 'B.Tech',
  `branch` VARCHAR(100) DEFAULT NULL,
  `year` VARCHAR(50) DEFAULT NULL,
  `semester` VARCHAR(50) DEFAULT NULL,
  `institution` VARCHAR(150) DEFAULT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `academic_interests` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating career goals table...
CREATE TABLE IF NOT EXISTS `career_goals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `target_role` VARCHAR(100) DEFAULT NULL,
  `custom_goal_text` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_goal_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating student situation notes and thoughts tables...
CREATE TABLE IF NOT EXISTS `student_thoughts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `raw_situation_notes` TEXT DEFAULT NULL,
  `strengths_text` TEXT DEFAULT NULL,
  `weaknesses_text` TEXT DEFAULT NULL,
  `expectations_text` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_thoughts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating preferences and user skill matrix tables...
CREATE TABLE IF NOT EXISTS `student_preferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `learning_style` VARCHAR(255) DEFAULT NULL,
  `weekly_hours` VARCHAR(50) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  `skill_level` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
  `source` ENUM('student_input', 'resume', 'coding', 'interview') NOT NULL DEFAULT 'student_input',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_skill_unique` (`user_id`, `skill_name`),
  CONSTRAINT `fk_user_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating resume storage and roadmap tables...
CREATE TABLE IF NOT EXISTS `resumes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `extracted_content` TEXT,
  `score` INT NOT NULL DEFAULT 0,
  `analysis` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_resumes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roadmaps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `career_path` VARCHAR(100) NOT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roadmap_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `roadmap_id` INT NOT NULL,
  `progress_percent` INT NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_roadmap_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_roadmap_progress_roadmap` FOREIGN KEY (`roadmap_id`) REFERENCES `roadmaps`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating project explorer and project tracking tables...
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `career_path` VARCHAR(100) NOT NULL,
  `difficulty` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Intermediate',
  `technologies` VARCHAR(255) NOT NULL,
  `skills` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `project_id` INT NOT NULL,
  `status` ENUM('started', 'completed') DEFAULT 'started',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_projects_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating coding practice and progress tracking tables...
CREATE TABLE IF NOT EXISTS `coding_questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `question` TEXT NOT NULL,
  `topic` VARCHAR(100) NOT NULL,
  `difficulty` ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Easy',
  `sample_answer` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coding_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `question_id` INT NOT NULL,
  `status` ENUM('attempted', 'solved') NOT NULL DEFAULT 'attempted',
  `attempts` INT NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_question_unique` (`user_id`, `question_id`),
  CONSTRAINT `fk_coding_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_coding_progress_question` FOREIGN KEY (`question_id`) REFERENCES `coding_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STREAMING_CHUNK:Creating interview evaluation and messaging tables...
CREATE TABLE IF NOT EXISTS `interviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `interview_type` VARCHAR(100) NOT NULL,
  `career_role` VARCHAR(100) NOT NULL,
  `difficulty` VARCHAR(50) NOT NULL DEFAULT 'Intermediate',
  `score` INT NOT NULL,
  `feedback` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_interviews_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mentor_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `sender` ENUM('user', 'ai') NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_mentor_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- STREAMING_CHUNK:Populating system templates and default seed data...
INSERT INTO `roadmaps` (`id`, `title`, `career_path`, `branch`, `description`) VALUES
(1, 'Full Stack Web Engineering', 'Full Stack Developer', 'Computer Science and Engineering', 'Foundation -> Core Backend & Databases -> Advanced Architecture -> Production Projects -> Interview Preparation'),
(2, 'Applied AI & ML Engineering', 'AI/ML Engineer', 'Computer Science and Engineering', 'Mathematics & Python -> Classical Machine Learning -> Deep Learning & PyTorch -> Generative AI & RAG -> MLOps Deployment'),
(3, 'Frontend System Architecture', 'Frontend Developer', 'Computer Science and Engineering', 'HTML5 & CSS Modern Layouts -> JS Async & ES6+ -> React/Next Framework -> UI State & Performance -> Testing & Deploy')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `projects` (`id`, `title`, `description`, `branch`, `career_path`, `difficulty`, `technologies`, `skills`) VALUES
(1, 'Workspace Collaboration Engine', 'Full stack real-time task manager with WebSockets and relational database persistence.', 'Computer Science and Engineering', 'Full Stack Developer', 'Intermediate', 'Node.js, Express, PostgreSQL, Vanilla JS', 'REST API, WebSockets, DB Schema'),
(2, 'Document Q&A AI Copilot', 'Semantic search engine querying custom PDF documentation with vector embeddings.', 'Computer Science and Engineering', 'AI/ML Engineer', 'Advanced', 'Python, FastAPI, FAISS, PyTorch', 'Vector Search, RAG, Embeddings'),
(3, 'Developer Portfolio Platform', 'Glassmorphic responsive interactive portfolio with automated GitHub metrics integration.', 'Computer Science and Engineering', 'Frontend Developer', 'Beginner', 'HTML5, CSS3, JavaScript', 'Responsive Layout, UI/UX Design')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `coding_questions` (`id`, `title`, `question`, `topic`, `difficulty`, `sample_answer`) VALUES
(1, 'Find Max Subarray Sum (Kadane’s Algorithm)', 'Given an integer array nums, find the contiguous subarray with the largest sum and return its sum.', 'Arrays', 'Medium', 'function maxSubArray(nums) {\n  let current = nums[0], globalMax = nums[0];\n  for (let i = 1; i < nums.length; i++) {\n    current = Math.max(nums[i], current + nums[i]);\n    globalMax = Math.max(globalMax, current);\n  }\n  return globalMax;\n}'),
(2, 'Reverse a Singly Linked List', 'Given the head of a singly linked list, reverse the list and return its new head node.', 'Linked Lists', 'Easy', 'function reverseList(head) {\n  let prev = null, curr = head;\n  while(curr) {\n    let next = curr.next;\n    curr.next = prev;\n    prev = curr;\n    curr = next;\n  }\n  return prev;\n}')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);