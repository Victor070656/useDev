-- DevAllies Platform Database Schema
-- Migration: 001_initial_schema
-- Description: Initial database structure for the DevAllies platform

CREATE DATABASE IF NOT EXISTS devallies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devallies;

-- Users table (core authentication and user data)
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('creator', 'client', 'admin') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(64) NULL,
    password_reset_token VARCHAR(64) NULL,
    password_reset_expires DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_email_verified (email_verified)
) ENGINE=InnoDB;

-- Creator profiles (developers and designers)
CREATE TABLE creator_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    creator_type ENUM('developer', 'designer') NOT NULL,
    display_name VARCHAR(150) NOT NULL,
    headline VARCHAR(255) NULL,
    bio TEXT NULL,
    profile_image VARCHAR(255) NULL,
    cover_image VARCHAR(255) NULL,
    location VARCHAR(150) NULL,
    timezone VARCHAR(50) NULL,
    website_url VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    twitter_url VARCHAR(255) NULL,
    dribbble_url VARCHAR(255) NULL,
    behance_url VARCHAR(255) NULL,
    hourly_rate INT UNSIGNED NULL COMMENT 'Rate in cents',
    fixed_rate_available BOOLEAN DEFAULT TRUE,
    is_available BOOLEAN DEFAULT TRUE,
    response_time_hours INT UNSIGNED NULL,
    total_projects INT UNSIGNED DEFAULT 0,
    total_earnings INT UNSIGNED DEFAULT 0 COMMENT 'Total earnings in cents',
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    rating_count INT UNSIGNED DEFAULT 0,
    views_count INT UNSIGNED DEFAULT 0,
    verified_badge BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_creator_type (creator_type),
    INDEX idx_is_available (is_available),
    INDEX idx_rating (rating_average),
    INDEX idx_verified (verified_badge)
) ENGINE=InnoDB;

-- Creator skills/technologies
CREATE TABLE creator_skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_profile_id INT UNSIGNED NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
    years_experience DECIMAL(3,1) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_skill_name (skill_name),
    INDEX idx_creator_skill (creator_profile_id, skill_name)
) ENGINE=InnoDB;

-- Portfolio projects
CREATE TABLE portfolio_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    project_url VARCHAR(255) NULL,
    thumbnail_image VARCHAR(255) NULL,
    project_type VARCHAR(100) NULL COMMENT 'web app, mobile app, design system, etc.',
    tech_stack TEXT NULL COMMENT 'JSON array of technologies',
    display_order INT UNSIGNED DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator_featured (creator_profile_id, is_featured),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB;

-- Portfolio media (images/videos for projects)
CREATE TABLE portfolio_media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_project_id INT UNSIGNED NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    display_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (portfolio_project_id) REFERENCES portfolio_projects(id) ON DELETE CASCADE,
    INDEX idx_project_media (portfolio_project_id, display_order)
) ENGINE=InnoDB;

-- Client profiles
CREATE TABLE client_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    company_name VARCHAR(255) NULL,
    company_type ENUM('startup', 'agency', 'enterprise', 'individual') NULL,
    company_size VARCHAR(50) NULL,
    website_url VARCHAR(255) NULL,
    logo_image VARCHAR(255) NULL,
    bio TEXT NULL,
    total_briefs INT UNSIGNED DEFAULT 0,
    total_spent INT UNSIGNED DEFAULT 0 COMMENT 'Total spent in cents',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Project briefs / job posts
CREATE TABLE project_briefs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    project_type ENUM('development', 'design', 'both') NOT NULL,
    budget_type ENUM('fixed', 'hourly') NOT NULL,
    budget_min INT UNSIGNED NULL COMMENT 'Budget in cents',
    budget_max INT UNSIGNED NULL COMMENT 'Budget in cents',
    timeline VARCHAR(100) NULL COMMENT 'e.g., 2-4 weeks',
    required_skills TEXT NULL COMMENT 'JSON array of required skills',
    experience_level ENUM('junior', 'mid', 'senior', 'expert') NULL,
    status ENUM('draft', 'open', 'in_progress', 'completed', 'closed') DEFAULT 'draft',
    deadline DATE NULL,
    views_count INT UNSIGNED DEFAULT 0,
    proposal_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_profile_id) REFERENCES client_profiles(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_project_type (project_type),
    INDEX idx_created_at (created_at DESC)
) ENGINE=InnoDB;

-- Proposals (creator responses to briefs)
CREATE TABLE proposals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_brief_id INT UNSIGNED NOT NULL,
    creator_profile_id INT UNSIGNED NOT NULL,
    cover_letter TEXT NOT NULL,
    proposed_budget INT UNSIGNED NOT NULL COMMENT 'Proposed amount in cents',
    proposed_timeline VARCHAR(100) NULL,
    status ENUM('pending', 'accepted', 'rejected', 'withdrawn') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_brief_id) REFERENCES project_briefs(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_brief_status (project_brief_id, status),
    INDEX idx_creator_proposals (creator_profile_id, status)
) ENGINE=InnoDB;

-- Contracts (accepted proposals become contracts)
CREATE TABLE contracts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT UNSIGNED NOT NULL UNIQUE,
    project_brief_id INT UNSIGNED NOT NULL,
    creator_profile_id INT UNSIGNED NOT NULL,
    client_profile_id INT UNSIGNED NOT NULL,
    contract_amount INT UNSIGNED NOT NULL COMMENT 'Contract amount in cents',
    platform_fee INT UNSIGNED NOT NULL COMMENT 'Platform fee in cents',
    creator_payout INT UNSIGNED NOT NULL COMMENT 'Creator payout in cents',
    status ENUM('active', 'completed', 'disputed', 'cancelled') DEFAULT 'active',
    start_date DATE NULL,
    end_date DATE NULL,
    terms TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE,
    FOREIGN KEY (project_brief_id) REFERENCES project_briefs(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (client_profile_id) REFERENCES client_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator_contracts (creator_profile_id, status),
    INDEX idx_client_contracts (client_profile_id, status),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Contract milestones
CREATE TABLE contract_milestones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    amount INT UNSIGNED NOT NULL COMMENT 'Milestone amount in cents',
    due_date DATE NULL,
    status ENUM('pending', 'in_progress', 'submitted', 'approved', 'paid') DEFAULT 'pending',
    submitted_at DATETIME NULL,
    approved_at DATETIME NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    INDEX idx_contract_milestones (contract_id, status)
) ENGINE=InnoDB;

-- Messages (in-app messaging between clients and creators)
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_user_id INT UNSIGNED NOT NULL,
    recipient_user_id INT UNSIGNED NOT NULL,
    project_brief_id INT UNSIGNED NULL,
    contract_id INT UNSIGNED NULL,
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    parent_message_id INT UNSIGNED NULL COMMENT 'For threading',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_brief_id) REFERENCES project_briefs(id) ON DELETE SET NULL,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_recipient (recipient_user_id, is_read),
    INDEX idx_conversation (sender_user_id, recipient_user_id, created_at),
    INDEX idx_brief (project_brief_id),
    INDEX idx_contract (contract_id)
) ENGINE=InnoDB;

-- Transactions (payment tracking)
CREATE TABLE transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_type ENUM('payment', 'payout', 'refund', 'fee') NOT NULL,
    contract_id INT UNSIGNED NULL,
    milestone_id INT UNSIGNED NULL,
    payer_user_id INT UNSIGNED NULL,
    payee_user_id INT UNSIGNED NULL,
    amount INT UNSIGNED NOT NULL COMMENT 'Amount in cents',
    currency VARCHAR(3) DEFAULT 'USD',
    payment_provider ENUM('stripe', 'paypal', 'manual') NOT NULL,
    provider_transaction_id VARCHAR(255) NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    metadata TEXT NULL COMMENT 'JSON metadata',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL,
    FOREIGN KEY (milestone_id) REFERENCES contract_milestones(id) ON DELETE SET NULL,
    FOREIGN KEY (payer_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (payee_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_contract (contract_id),
    INDEX idx_payer (payer_user_id),
    INDEX idx_payee (payee_user_id),
    INDEX idx_status (status),
    INDEX idx_provider (payment_provider, provider_transaction_id)
) ENGINE=InnoDB;

-- Courses (educational content by creators)
CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    short_description VARCHAR(500) NULL,
    thumbnail_image VARCHAR(255) NULL,
    preview_video_url VARCHAR(255) NULL,
    price INT UNSIGNED NOT NULL COMMENT 'Price in cents',
    is_published BOOLEAN DEFAULT FALSE,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NULL,
    duration_hours DECIMAL(5,2) NULL,
    enrollment_count INT UNSIGNED DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    rating_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_profile_id),
    INDEX idx_published (is_published),
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- Course modules/sections
CREATE TABLE course_modules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    display_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_order (course_id, display_order)
) ENGINE=InnoDB;

-- Course lessons
CREATE TABLE course_lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_module_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NULL,
    video_url VARCHAR(255) NULL,
    duration_minutes INT UNSIGNED NULL,
    is_preview BOOLEAN DEFAULT FALSE,
    display_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    INDEX idx_module_order (course_module_id, display_order)
) ENGINE=InnoDB;

-- Course enrollments
CREATE TABLE course_enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    transaction_id INT UNSIGNED NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    completed BOOLEAN DEFAULT FALSE,
    completed_at DATETIME NULL,
    last_accessed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    UNIQUE KEY unique_enrollment (course_id, user_id),
    INDEX idx_user_enrollments (user_id),
    INDEX idx_course_enrollments (course_id)
) ENGINE=InnoDB;

-- Digital products (templates, UI kits, source files)
CREATE TABLE digital_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    short_description VARCHAR(500) NULL,
    product_type VARCHAR(100) NULL COMMENT 'template, ui-kit, source-code, design-asset, etc.',
    thumbnail_image VARCHAR(255) NULL,
    preview_images TEXT NULL COMMENT 'JSON array of preview image URLs',
    file_path VARCHAR(255) NULL COMMENT 'Actual downloadable file',
    file_size_kb INT UNSIGNED NULL,
    price INT UNSIGNED NOT NULL COMMENT 'Price in cents',
    is_published BOOLEAN DEFAULT FALSE,
    download_count INT UNSIGNED DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    rating_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_profile_id),
    INDEX idx_published (is_published),
    INDEX idx_product_type (product_type),
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- Digital product purchases
CREATE TABLE product_purchases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    digital_product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    transaction_id INT UNSIGNED NULL,
    download_count INT UNSIGNED DEFAULT 0,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (digital_product_id) REFERENCES digital_products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    UNIQUE KEY unique_purchase (digital_product_id, user_id),
    INDEX idx_user_purchases (user_id),
    INDEX idx_product_purchases (digital_product_id)
) ENGINE=InnoDB;

-- Communities (creator-led communities)
CREATE TABLE communities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    short_description VARCHAR(500) NULL,
    cover_image VARCHAR(255) NULL,
    community_type ENUM('free', 'paid', 'cohort') NOT NULL,
    membership_price INT UNSIGNED NULL COMMENT 'Monthly price in cents (if paid)',
    is_published BOOLEAN DEFAULT FALSE,
    member_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_profile_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator (creator_profile_id),
    INDEX idx_type (community_type),
    INDEX idx_published (is_published),
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- Community memberships
CREATE TABLE community_memberships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    community_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_membership (community_id, user_id),
    INDEX idx_user_memberships (user_id, status),
    INDEX idx_community_members (community_id, status)
) ENGINE=InnoDB;

-- Community posts
CREATE TABLE community_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    community_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NULL,
    content TEXT NOT NULL,
    is_pinned BOOLEAN DEFAULT FALSE,
    like_count INT UNSIGNED DEFAULT 0,
    comment_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_community_posts (community_id, created_at DESC),
    INDEX idx_pinned (community_id, is_pinned)
) ENGINE=InnoDB;

-- Reviews/testimonials
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id INT UNSIGNED NULL,
    course_id INT UNSIGNED NULL,
    digital_product_id INT UNSIGNED NULL,
    reviewer_user_id INT UNSIGNED NOT NULL,
    reviewed_creator_id INT UNSIGNED NOT NULL,
    rating INT UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (digital_product_id) REFERENCES digital_products(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE,
    INDEX idx_creator_reviews (reviewed_creator_id),
    INDEX idx_contract (contract_id),
    INDEX idx_course (course_id),
    INDEX idx_product (digital_product_id),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link_url VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id, is_read, created_at DESC)
) ENGINE=InnoDB;

-- Activity log (for admin monitoring)
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata TEXT NULL COMMENT 'JSON metadata',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_activity (user_id, created_at DESC),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- Sessions table (for session management)
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    data TEXT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB;
