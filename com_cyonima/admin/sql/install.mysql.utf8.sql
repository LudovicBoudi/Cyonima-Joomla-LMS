--
-- Cyonima LMS install schema
--

CREATE TABLE IF NOT EXISTS `#__cyonima_courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `intro` text NOT NULL,
  `description` mediumtext NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  `featured` tinyint NOT NULL DEFAULT 0,
  `access` int unsigned NOT NULL DEFAULT 1,
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT 0,
  `hits` int unsigned NOT NULL DEFAULT 0,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`),
  KEY `idx_featured_catid` (`featured`,`published`),
  KEY `idx_state` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_lessons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'content',
  `content` mediumtext NULL,
  `url` varchar(2048) NOT NULL DEFAULT '',
  `media` varchar(255) NOT NULL DEFAULT '',
  `duration` int unsigned NOT NULL DEFAULT 0,
  `max_score` int unsigned NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  `access` int unsigned NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_type` (`type`),
  KEY `idx_state` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_enrollments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'enrolled',
  `progress` int unsigned NOT NULL DEFAULT 0,
  `score` decimal(10,2) NOT NULL DEFAULT 0,
  `max_score` decimal(10,2) NOT NULL DEFAULT 0,
  `enrolled_date` datetime NOT NULL,
  `completed_date` datetime NULL DEFAULT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_course_user` (`course_id`,`user_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_lesson_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` int unsigned NOT NULL DEFAULT 0,
  `lesson_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'incomplete',
  `score` decimal(10,2) NOT NULL DEFAULT 0,
  `max_score` decimal(10,2) NOT NULL DEFAULT 0,
  `completed_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_enrollment_lesson` (`enrollment_id`,`lesson_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_lesson` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` int unsigned NOT NULL DEFAULT 0,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `due_date` datetime NULL DEFAULT NULL,
  `max_score` int unsigned NOT NULL DEFAULT 100,
  `coefficient` decimal(10,2) NOT NULL DEFAULT 1,
  `attempts_allowed` int unsigned NOT NULL DEFAULT 1,
  `published` tinyint NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lesson` (`lesson_id`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_submissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `content` mediumtext NULL,
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `submitted_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'submitted',
  `score` decimal(10,2) NOT NULL DEFAULT 0,
  `feedback` text NULL,
  `graded_by` int unsigned NOT NULL DEFAULT 0,
  `graded_date` datetime NULL DEFAULT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_assignment_user` (`assignment_id`,`user_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_exams` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` int unsigned NOT NULL DEFAULT 0,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `time_limit` int unsigned NOT NULL DEFAULT 0,
  `pass_mark` int unsigned NOT NULL DEFAULT 50,
  `attempts_allowed` int unsigned NOT NULL DEFAULT 1,
  `shuffle` tinyint NOT NULL DEFAULT 0,
  `coefficient` decimal(10,2) NOT NULL DEFAULT 1,
  `published` tinyint NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lesson` (`lesson_id`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int unsigned NOT NULL DEFAULT 0,
  `assignment_id` int unsigned NOT NULL DEFAULT 0,
  `question` text NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'single',
  `options` text NOT NULL,
  `answer` text NOT NULL,
  `points` int unsigned NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_assignment` (`assignment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_exam_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int unsigned NOT NULL DEFAULT 0,
  `assignment_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `started` datetime NOT NULL,
  `finished` datetime NULL DEFAULT NULL,
  `score` decimal(10,2) NOT NULL DEFAULT 0,
  `max_score` decimal(10,2) NOT NULL DEFAULT 0,
  `passed` tinyint NOT NULL DEFAULT 0,
  `answers` mediumtext NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'in_progress',
  PRIMARY KEY (`id`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_assignment` (`assignment_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_certificate_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT '',
  `params` text NULL,
  `published` tinyint NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_certificates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `template_id` int unsigned NOT NULL DEFAULT 0,
  `certificate_number` varchar(64) NOT NULL DEFAULT '',
  `issued_date` datetime NOT NULL,
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `params` text NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_course_user` (`course_id`,`user_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_learning_paths` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 0,
  `access` int unsigned NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`published`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cyonima_learning_path_courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `path_id` int unsigned NOT NULL DEFAULT 0,
  `course_id` int unsigned NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_path_course` (`path_id`,`course_id`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
