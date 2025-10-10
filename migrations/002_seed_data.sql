-- DevAllies Platform Seed Data
-- Migration: 002_seed_data
-- Description: Sample data for testing and development

USE devallies;

-- Insert Admin User
-- NOTE: Passwords stored in plain text (NOT RECOMMENDED FOR PRODUCTION)
INSERT INTO users (email, password_hash, user_type, first_name, last_name, email_verified) VALUES
('admin@devallies.com', 'password', 'admin', 'Admin', 'User', TRUE);

-- Insert Sample Creators (Developers)
INSERT INTO users (email, password_hash, user_type, first_name, last_name, email_verified) VALUES
('john.dev@example.com', 'password', 'creator', 'John', 'Developer', TRUE),
('sarah.dev@example.com', 'password', 'creator', 'Sarah', 'Chen', TRUE),
('mike.dev@example.com', 'password', 'creator', 'Mike', 'Johnson', TRUE);

-- Insert Sample Creators (Designers)
INSERT INTO users (email, password_hash, user_type, first_name, last_name, email_verified) VALUES
('emma.design@example.com', 'password', 'creator', 'Emma', 'Wilson', TRUE),
('alex.design@example.com', 'password', 'creator', 'Alex', 'Martinez', TRUE);

-- Insert Sample Clients
INSERT INTO users (email, password_hash, user_type, first_name, last_name, email_verified) VALUES
('client@startup.com', 'password', 'client', 'Tech', 'Startup', TRUE),
('client@agency.com', 'password', 'client', 'Digital', 'Agency', TRUE);

-- Create Creator Profiles (Developers)
INSERT INTO creator_profiles (user_id, creator_type, display_name, headline, bio, location, timezone, hourly_rate, is_available, verified_badge) VALUES
(2, 'developer', 'John Developer', 'Full-Stack Developer | React & Node.js Expert', 'Experienced full-stack developer with 8+ years building scalable web applications. Specialized in React, Node.js, and cloud architecture.', 'San Francisco, CA', 'America/Los_Angeles', 15000, TRUE, TRUE),
(3, 'developer', 'Sarah Chen', 'Senior Backend Engineer | Python & AWS Specialist', 'Backend engineer passionate about building robust, scalable APIs and microservices. Expert in Python, Django, and AWS infrastructure.', 'Austin, TX', 'America/Chicago', 18000, TRUE, TRUE),
(4, 'developer', 'Mike Johnson', 'Mobile App Developer | iOS & Android', 'Mobile app developer creating beautiful, performant applications for iOS and Android. Specialized in React Native and Flutter.', 'New York, NY', 'America/New_York', 12500, TRUE, FALSE);

-- Create Creator Profiles (Designers)
INSERT INTO creator_profiles (user_id, creator_type, display_name, headline, bio, location, timezone, hourly_rate, is_available, verified_badge) VALUES
(5, 'designer', 'Emma Wilson', 'UI/UX Designer | Product Design Specialist', 'Product designer with a passion for creating intuitive, user-centered experiences. 6+ years working with startups and Fortune 500 companies.', 'Los Angeles, CA', 'America/Los_Angeles', 13500, TRUE, TRUE),
(6, 'designer', 'Alex Martinez', 'Brand & Interface Designer', 'Creative designer specializing in brand identity and modern interface design. I help startups build memorable visual experiences.', 'Miami, FL', 'America/New_York', 11000, TRUE, FALSE);

-- Add Skills for Developers
INSERT INTO creator_skills (creator_profile_id, skill_name, proficiency_level, years_experience) VALUES
-- John Developer
(1, 'JavaScript', 'expert', 8.0),
(1, 'React', 'expert', 6.0),
(1, 'Node.js', 'expert', 7.0),
(1, 'TypeScript', 'advanced', 5.0),
(1, 'MongoDB', 'advanced', 5.0),
(1, 'PostgreSQL', 'advanced', 6.0),
(1, 'Docker', 'advanced', 4.0),
(1, 'AWS', 'intermediate', 3.0),
-- Sarah Chen
(2, 'Python', 'expert', 8.0),
(2, 'Django', 'expert', 7.0),
(2, 'PostgreSQL', 'expert', 8.0),
(2, 'AWS', 'expert', 6.0),
(2, 'Redis', 'advanced', 5.0),
(2, 'Docker', 'expert', 5.0),
(2, 'Kubernetes', 'advanced', 3.0),
-- Mike Johnson
(3, 'React Native', 'expert', 5.0),
(3, 'Flutter', 'advanced', 3.0),
(3, 'iOS', 'advanced', 4.0),
(3, 'Android', 'advanced', 4.0),
(3, 'TypeScript', 'advanced', 4.0),
(3, 'Firebase', 'advanced', 4.0);

-- Add Skills for Designers
INSERT INTO creator_skills (creator_profile_id, skill_name, proficiency_level, years_experience) VALUES
-- Emma Wilson
(4, 'Figma', 'expert', 6.0),
(4, 'Sketch', 'advanced', 5.0),
(4, 'Adobe XD', 'advanced', 4.0),
(4, 'Prototyping', 'expert', 6.0),
(4, 'User Research', 'advanced', 5.0),
(4, 'Design Systems', 'expert', 4.0),
-- Alex Martinez
(5, 'Figma', 'expert', 5.0),
(5, 'Adobe Illustrator', 'expert', 6.0),
(5, 'Adobe Photoshop', 'advanced', 6.0),
(5, 'Branding', 'expert', 5.0),
(5, 'UI Design', 'expert', 5.0);

-- Add Portfolio Projects
INSERT INTO portfolio_projects (creator_profile_id, title, description, project_type, tech_stack, is_featured) VALUES
(1, 'E-commerce Platform', 'Built a full-featured e-commerce platform handling 100k+ daily users with real-time inventory management.', 'Web Application', '["React", "Node.js", "MongoDB", "Redis", "Stripe"]', TRUE),
(1, 'Analytics Dashboard', 'Real-time analytics dashboard for SaaS companies with custom reporting and data visualization.', 'Web Application', '["React", "TypeScript", "D3.js", "PostgreSQL"]', TRUE),
(2, 'Microservices API Gateway', 'Designed and implemented a scalable API gateway handling 1M+ requests per day.', 'Backend System', '["Python", "Django", "AWS", "Docker", "Kubernetes"]', TRUE),
(3, 'Fitness Tracking App', 'Mobile fitness app with workout tracking, nutrition planning, and social features.', 'Mobile App', '["React Native", "Firebase", "TypeScript"]', TRUE),
(4, 'Banking App Redesign', 'Complete UX/UI redesign for a digital banking application, improving user satisfaction by 45%.', 'UI/UX Design', '["Figma", "User Research", "Prototyping"]', TRUE),
(5, 'Startup Brand Identity', 'Created comprehensive brand identity including logo, color system, and design guidelines.', 'Branding', '["Illustrator", "Figma"]', TRUE);

-- Create Client Profiles
INSERT INTO client_profiles (user_id, company_name, company_type, website_url) VALUES
(7, 'TechStart Inc', 'startup', 'https://techstart.example.com'),
(8, 'Creative Digital Agency', 'agency', 'https://creativedigital.example.com');

-- Add Sample Project Briefs
INSERT INTO project_briefs (client_profile_id, title, description, project_type, budget_type, budget_min, budget_max, timeline, required_skills, status) VALUES
(1, 'React Developer for SaaS Dashboard', 'We need an experienced React developer to build a modern analytics dashboard for our SaaS product. The dashboard should include real-time data visualization, custom reporting, and responsive design.', 'development', 'fixed', 500000, 800000, '4-6 weeks', '["React", "TypeScript", "D3.js", "REST API"]', 'open'),
(1, 'Mobile App Developer - React Native', 'Looking for a skilled React Native developer to create a cross-platform mobile app for our delivery service. Must have experience with maps integration and real-time tracking.', 'development', 'fixed', 1000000, 1500000, '8-12 weeks', '["React Native", "Firebase", "Google Maps API", "Redux"]', 'open'),
(2, 'UI/UX Designer for E-commerce Platform', 'Seeking a talented UI/UX designer to redesign our e-commerce platform. Project includes user research, wireframing, prototyping, and final design delivery.', 'design', 'hourly', 10000, 15000, '6-8 weeks', '["Figma", "User Research", "Prototyping", "E-commerce"]', 'open'),
(2, 'Full-Stack Developer - Python/Django', 'Need a full-stack developer to build an admin panel and API for content management. Backend with Django, frontend with React.', 'development', 'fixed', 600000, 900000, '6 weeks', '["Python", "Django", "React", "PostgreSQL"]', 'open');

-- Add Sample Courses
INSERT INTO courses (creator_profile_id, title, slug, description, short_description, price, is_published, difficulty_level, duration_hours) VALUES
(1, 'Master React & TypeScript', 'master-react-typescript', 'Complete guide to building modern web applications with React and TypeScript. Learn best practices, advanced patterns, and real-world project development.', 'Build production-ready React apps with TypeScript', 9900, TRUE, 'intermediate', 24.5),
(2, 'AWS Architecture for Developers', 'aws-architecture-developers', 'Learn to design and deploy scalable cloud applications on AWS. Covers EC2, S3, Lambda, RDS, and best practices for cloud architecture.', 'Master AWS cloud architecture and deployment', 14900, TRUE, 'advanced', 18.0),
(4, 'UI/UX Design Fundamentals', 'ui-ux-design-fundamentals', 'Learn the fundamentals of user interface and user experience design. Perfect for beginners wanting to start a career in product design.', 'Start your journey in UI/UX design', 7900, TRUE, 'beginner', 16.0);

-- Add Course Modules
INSERT INTO course_modules (course_id, title, description, display_order) VALUES
(1, 'Getting Started with React & TypeScript', 'Introduction to React and TypeScript setup', 1),
(1, 'Advanced React Patterns', 'Learn advanced patterns like HOCs, Render Props, and Hooks', 2),
(1, 'State Management with Redux', 'Master state management in large applications', 3);

-- Add Course Lessons
INSERT INTO course_lessons (course_module_id, title, content, duration_minutes, is_preview, display_order) VALUES
(1, 'Introduction to the Course', 'Welcome to the course! In this lesson we will cover what you will learn.', 5, TRUE, 1),
(1, 'Setting Up Your Development Environment', 'Learn how to set up React and TypeScript with Vite.', 15, TRUE, 2),
(1, 'Your First React Component', 'Create your first TypeScript-powered React component.', 20, FALSE, 3);

-- Add Digital Products
INSERT INTO digital_products (creator_profile_id, title, slug, description, product_type, price, is_published) VALUES
(4, 'Modern Dashboard UI Kit', 'modern-dashboard-ui-kit', 'Complete dashboard UI kit with 50+ screens, 200+ components. Built in Figma with design system included.', 'ui-kit', 4900, TRUE),
(5, 'Startup Branding Template', 'startup-branding-template', 'Complete branding template for startups including logo variations, color palettes, typography, and brand guidelines.', 'template', 2900, TRUE);

-- Add Communities
INSERT INTO communities (creator_profile_id, title, slug, description, community_type, membership_price, is_published) VALUES
(1, 'React Developers Hub', 'react-developers-hub', 'Join a community of React developers sharing knowledge, code reviews, and career advice.', 'paid', 1900, TRUE),
(4, 'UX Design Mastery', 'ux-design-mastery', 'A community for UX designers to share work, get feedback, and learn from industry experts.', 'paid', 2900, TRUE);

-- Add Sample Reviews
INSERT INTO reviews (reviewed_creator_id, reviewer_user_id, rating, review_text, is_featured) VALUES
(1, 7, 5, 'John delivered exceptional work on our dashboard project. His code quality and attention to detail were outstanding. Highly recommended!', TRUE),
(2, 8, 5, 'Sarah is an absolute expert in backend development. She built our API infrastructure flawlessly and ahead of schedule.', TRUE),
(4, 7, 5, 'Emma transformed our product with her design skills. The user feedback has been overwhelmingly positive!', TRUE);

-- Update creator profiles with sample stats
UPDATE creator_profiles SET
    total_projects = 12,
    total_earnings = 4500000,
    rating_average = 4.95,
    rating_count = 8,
    views_count = 1250
WHERE id = 1;

UPDATE creator_profiles SET
    total_projects = 15,
    total_earnings = 6200000,
    rating_average = 5.00,
    rating_count = 12,
    views_count = 980
WHERE id = 2;

UPDATE creator_profiles SET
    total_projects = 8,
    total_earnings = 2800000,
    rating_average = 4.80,
    rating_count = 5,
    views_count = 650
WHERE id = 3;

UPDATE creator_profiles SET
    total_projects = 18,
    total_earnings = 5100000,
    rating_average = 4.92,
    rating_count = 15,
    views_count = 1580
WHERE id = 4;

UPDATE creator_profiles SET
    total_projects = 10,
    total_earnings = 3400000,
    rating_average = 4.85,
    rating_count = 7,
    views_count = 820
WHERE id = 5;
