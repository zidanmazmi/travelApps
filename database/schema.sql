-- White-Label Travel Platform - Base Schema
-- Schema only: no production/client data is included.
-- Compatible baseline for MySQL 8.x / MariaDB with utf8mb4 collations.
-- After import, run: php spark migrate

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `admin_activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `admin_name` varchar(150) DEFAULT NULL,
  `admin_email` varchar(150) DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(100) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chatbot_knowledge` (
  `id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `keywords` text COLLATE utf8mb4_general_ci,
  `answer` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `source_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source_priority` int DEFAULT '50',
  `auto_generated` tinyint(1) DEFAULT '0',
  `embedding_json` longtext COLLATE utf8mb4_general_ci,
  `embedding_model` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chatbot_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` bigint UNSIGNED NOT NULL,
  `sender` enum('visitor','bot') COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `intent` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metadata_json` longtext COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chatbot_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `session_token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `visitor_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `visitor_phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source_page` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `last_intent` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `conversation_context_json` longtext COLLATE utf8mb4_general_ci,
  `last_message_at` datetime DEFAULT NULL,
  `context_updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chatbot_sources` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `document_type` enum('itinerary','flyer','booklet','other') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'other',
  `original_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `stored_path` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` bigint UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('uploaded','processing','review','published','archived','failed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'uploaded',
  `summary` text COLLATE utf8mb4_general_ci,
  `extracted_text` longtext COLLATE utf8mb4_general_ci,
  `structured_json` longtext COLLATE utf8mb4_general_ci,
  `confidence` decimal(5,4) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `priority` int NOT NULL DEFAULT '50',
  `admin_notes` text COLLATE utf8mb4_general_ci,
  `error_message` text COLLATE utf8mb4_general_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `published_by` bigint UNSIGNED DEFAULT NULL,
  `last_processed_at` datetime DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chatbot_source_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `source_id` bigint UNSIGNED NOT NULL,
  `knowledge_id` bigint UNSIGNED DEFAULT NULL,
  `question` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `keywords` text COLLATE utf8mb4_general_ci,
  `answer` text COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `program` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `confidence` decimal(5,4) DEFAULT NULL,
  `is_selected` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('draft','published','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chatbot_unanswered` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` bigint UNSIGNED DEFAULT NULL,
  `question` text COLLATE utf8mb4_general_ci NOT NULL,
  `normalized_question` text COLLATE utf8mb4_general_ci,
  `status` enum('new','resolved','ignored') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'new',
  `resolved_by` bigint UNSIGNED DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contact_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `content_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `page_key` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` longtext,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `verification_code` varchar(10) NOT NULL,
  `purpose` enum('registration','payment','status_check') NOT NULL DEFAULT 'registration',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `expired_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `galleries` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `image` varchar(255) NOT NULL,
  `video_file` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint UNSIGNED DEFAULT NULL,
  `aspect_ratio` enum('portrait','landscape','square') NOT NULL DEFAULT 'landscape',
  `duration_seconds` int UNSIGNED DEFAULT NULL,
  `description` text,
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('registration','payment','document','package','gallery','testimonial','faq','lead','system') NOT NULL DEFAULT 'system',
  `reference_id` bigint DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `program` varchar(30) DEFAULT NULL,
  `description` text,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `duration_days` int NOT NULL DEFAULT '0',
  `duration_nights` int NOT NULL DEFAULT '0',
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','full','closed') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `airline` varchar(255) DEFAULT NULL,
  `hotel_makkah` text,
  `hotel_madinah` text,
  `facilities_text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_departures` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `quota` int NOT NULL DEFAULT '0',
  `booked` int NOT NULL DEFAULT '0',
  `status` enum('available','full','closed') NOT NULL DEFAULT 'available',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_facilities` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `type` enum('included','excluded') NOT NULL DEFAULT 'included',
  `facility_name` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_itineraries` (
  `id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `day_number` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `gross_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `transaction_status` varchar(50) NOT NULL DEFAULT 'pending',
  `fraud_status` varchar(50) DEFAULT NULL,
  `va_number` varchar(100) DEFAULT NULL,
  `payment_code` varchar(100) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `snap_redirect_url` text,
  `raw_response` json DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `paid_email_sent_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pilgrims` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `is_leader` tinyint(1) NOT NULL DEFAULT '0',
  `full_name` varchar(150) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `birth_place` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `passport_number` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pilgrim_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `registration_id` bigint UNSIGNED NOT NULL,
  `pilgrim_id` bigint UNSIGNED DEFAULT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_file` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Verifikasi',
  `note` text,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `delete_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registrations` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `registration_no` varchar(30) NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `departure_id` bigint UNSIGNED DEFAULT NULL,
  `total_participants` int NOT NULL DEFAULT '1',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `registration_status` varchar(50) NOT NULL DEFAULT 'Menunggu Verifikasi',
  `payment_status` varchar(50) NOT NULL DEFAULT 'Belum Bayar',
  `note` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `site_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `testimonials` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `label` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` tinyint UNSIGNED DEFAULT '5',
  `sort_order` int DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `travel_leads` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `departure_id` bigint UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_participants` int NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'website',
  `status` enum('new','contacted','follow_up','qualified','waiting_decision','converted','not_interested','unreachable','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `lead_temperature` enum('cold','warm','hot') COLLATE utf8mb4_unicode_ci DEFAULT 'warm',
  `assigned_admin_id` bigint UNSIGNED DEFAULT NULL,
  `next_follow_up_at` datetime DEFAULT NULL,
  `last_contacted_at` datetime DEFAULT NULL,
  `whatsapp_clicked_at` datetime DEFAULT NULL,
  `converted_registration_id` bigint UNSIGNED DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,
  `lost_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `delete_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `travel_lead_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `activity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','jamaah') NOT NULL DEFAULT 'jamaah',
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` datetime DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expired_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `delete_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chatbot_knowledge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `deleted_at` (`deleted_at`);

ALTER TABLE `chatbot_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `intent` (`intent`),
  ADD KEY `created_at` (`created_at`);

ALTER TABLE `chatbot_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `last_message_at` (`last_message_at`);

ALTER TABLE `chatbot_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `document_type` (`document_type`),
  ADD KEY `status` (`status`),
  ADD KEY `valid_until` (`valid_until`),
  ADD KEY `deleted_at` (`deleted_at`);

ALTER TABLE `chatbot_source_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `knowledge_id` (`knowledge_id`),
  ADD KEY `status` (`status`);

ALTER TABLE `chatbot_unanswered`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `content_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_key` (`page_key`);

ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

ALTER TABLE `package_departures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_departures_package` (`package_id`);

ALTER TABLE `package_facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_facilities_package` (`package_id`);

ALTER TABLE `package_itineraries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_itineraries_package` (`package_id`);

ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `fk_payments_registration` (`registration_id`);

ALTER TABLE `pilgrims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pilgrims_registration` (`registration_id`);

ALTER TABLE `pilgrim_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pilgrim_documents_registration` (`registration_id`),
  ADD KEY `idx_pilgrim_documents_deleted_at` (`deleted_at`);

ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_no` (`registration_no`),
  ADD UNIQUE KEY `unique_registration_no` (`registration_no`),
  ADD KEY `fk_registrations_package` (`package_id`),
  ADD KEY `fk_registrations_departure` (`departure_id`),
  ADD KEY `fk_registrations_user` (`user_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `travel_leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_no` (`lead_no`),
  ADD KEY `idx_travel_leads_package` (`package_id`),
  ADD KEY `idx_travel_leads_departure` (`departure_id`),
  ADD KEY `idx_travel_leads_status` (`status`),
  ADD KEY `idx_travel_leads_temperature` (`lead_temperature`),
  ADD KEY `idx_travel_leads_admin` (`assigned_admin_id`),
  ADD KEY `idx_travel_leads_follow_up` (`next_follow_up_at`),
  ADD KEY `idx_travel_leads_created_at` (`created_at`),
  ADD KEY `idx_travel_leads_deleted_at` (`deleted_at`);

ALTER TABLE `travel_lead_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_activities_lead` (`lead_id`),
  ADD KEY `idx_lead_activities_admin` (`admin_id`),
  ADD KEY `idx_lead_activities_created_at` (`created_at`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_users_email` (`email`),
  ADD UNIQUE KEY `unique_users_nik` (`nik`),
  ADD KEY `idx_users_deleted_at` (`deleted_at`);

ALTER TABLE `admin_activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chatbot_knowledge`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chatbot_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chatbot_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chatbot_sources`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chatbot_source_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chatbot_unanswered`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `contact_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `content_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `galleries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `package_departures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `package_facilities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `package_itineraries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `pilgrims`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `pilgrim_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `registrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `site_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `testimonials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `travel_leads`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `travel_lead_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `package_departures`
  ADD CONSTRAINT `fk_departures_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_facilities`
  ADD CONSTRAINT `fk_facilities_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_itineraries`
  ADD CONSTRAINT `fk_itineraries_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

ALTER TABLE `pilgrims`
  ADD CONSTRAINT `fk_pilgrims_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

ALTER TABLE `pilgrim_documents`
  ADD CONSTRAINT `fk_pilgrim_documents_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

ALTER TABLE `registrations`
  ADD CONSTRAINT `fk_registrations_departure` FOREIGN KEY (`departure_id`) REFERENCES `package_departures` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_registrations_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS = 1;
