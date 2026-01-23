<?php
/**
 * HealNest Database Configuration
 * Handles database connection and setup for comprehensive user tracking
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'healnest_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }

    public function createTables() {
        $sql = "
        -- Users table with comprehensive tracking
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            birth_date DATE,
            bio TEXT,
            avatar_path VARCHAR(255),
            
            -- Program Assignment
            assigned_program_id INT DEFAULT NULL,
            program_start_date DATE DEFAULT NULL,
            
            -- Streak Tracking
            current_streak INT DEFAULT 0,
            highest_streak INT DEFAULT 0,
            last_activity_date DATE DEFAULT NULL,
            total_program_days INT DEFAULT 0,
            
            -- Assessment Status
            assessment_taken BOOLEAN DEFAULT FALSE,
            assessment_date TIMESTAMP NULL,
            wellness_score INT DEFAULT 0,
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (assigned_program_id) REFERENCES programs(id) ON DELETE SET NULL
        );

        -- Programs table with detailed information
        CREATE TABLE IF NOT EXISTS programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            program_name VARCHAR(255) NOT NULL,
            program_description TEXT NOT NULL,
            program_goal TEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            duration_days INT NOT NULL,
            difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
            icon VARCHAR(10),
            color VARCHAR(7) DEFAULT '#5D87FF',
            
            -- Program Status
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Tasks table - Master list of all available tasks
        CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_name VARCHAR(255) NOT NULL,
            task_description TEXT,
            task_type ENUM('daily', 'weekly', 'milestone') DEFAULT 'daily',
            category VARCHAR(100),
            estimated_duration INT DEFAULT 10, -- in minutes
            difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
            instructions TEXT,
            
            -- Task Status
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Program Tasks - Which tasks belong to which programs
        CREATE TABLE IF NOT EXISTS program_tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            program_id INT NOT NULL,
            task_id INT NOT NULL,
            day_number INT NOT NULL, -- Which day of the program this task appears
            is_required BOOLEAN DEFAULT TRUE,
            order_sequence INT DEFAULT 1,
            
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
            FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            UNIQUE KEY unique_program_task_day (program_id, task_id, day_number)
        );

        -- User Task Completions - Track daily task completions
        CREATE TABLE IF NOT EXISTS user_task_completions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            task_id INT NOT NULL,
            program_id INT NOT NULL,
            completion_date DATE NOT NULL,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_task_date (user_id, task_id, completion_date)
        );

        -- Daily Progress Summary - Track daily completion stats
        CREATE TABLE IF NOT EXISTS daily_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            progress_date DATE NOT NULL,
            total_tasks_assigned INT DEFAULT 0,
            tasks_completed INT DEFAULT 0,
            completion_percentage DECIMAL(5,2) DEFAULT 0.00,
            streak_day INT DEFAULT 0,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_date (user_id, progress_date)
        );

        -- Assessments table
        CREATE TABLE IF NOT EXISTS assessments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_score INT NOT NULL,
            wellness_score INT NOT NULL,
            answers JSON NOT NULL,
            recommendations JSON,
            recommended_program_id INT,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (recommended_program_id) REFERENCES programs(id) ON DELETE SET NULL
        );

        -- Mood entries table
        CREATE TABLE IF NOT EXISTS mood_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            mood VARCHAR(50) NOT NULL,
            mood_score INT NOT NULL, -- 1-5 scale
            note TEXT,
            entry_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_date (user_id, entry_date)
        );

        -- Journal entries table
        CREATE TABLE IF NOT EXISTS journal_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            mood VARCHAR(50) DEFAULT 'neutral',
            tags JSON,
            is_private BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- User sessions table
        CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Achievements table
        CREATE TABLE IF NOT EXISTS achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            achievement_name VARCHAR(255) NOT NULL,
            achievement_description TEXT,
            achievement_type ENUM('streak', 'completion', 'milestone', 'special') DEFAULT 'completion',
            requirement_value INT DEFAULT 1,
            icon VARCHAR(10),
            badge_color VARCHAR(7) DEFAULT '#FFD700',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- User Achievements - Track earned achievements
        CREATE TABLE IF NOT EXISTS user_achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            achievement_id INT NOT NULL,
            earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_achievement (user_id, achievement_id)
        );
        ";

        try {
            $this->conn->exec($sql);
            return true;
        } catch(PDOException $exception) {
            echo "Table creation error: " . $exception->getMessage();
            return false;
        }
    }

    public function seedPrograms() {
        $programs = [
            [
                'program_name' => 'Mindfulness & Stress Relief',
                'program_description' => 'A comprehensive 30-day program designed to help you develop mindfulness skills, reduce stress, and improve emotional well-being through daily meditation and mindfulness exercises.',
                'program_goal' => 'Reduce stress levels by 40% and develop consistent mindfulness practice',
                'category' => 'Mental Wellness',
                'duration_days' => 30,
                'difficulty_level' => 'beginner',
                'icon' => '🧘‍♀️',
                'color' => '#5D87FF'
            ],
            [
                'program_name' => 'Emotional Balance & Resilience',
                'program_description' => 'Build emotional intelligence and resilience through targeted exercises, journaling, and cognitive behavioral techniques over 45 days.',
                'program_goal' => 'Improve emotional regulation and build resilience to handle life challenges',
                'category' => 'Emotional Health',
                'duration_days' => 45,
                'difficulty_level' => 'intermediate',
                'icon' => '❤️',
                'color' => '#28a745'
            ],
            [
                'program_name' => 'Sleep Optimization & Recovery',
                'program_description' => 'Improve sleep quality and establish healthy sleep habits through sleep hygiene practices, relaxation techniques, and lifestyle adjustments.',
                'program_goal' => 'Achieve 7-8 hours of quality sleep consistently and improve energy levels',
                'category' => 'Physical Wellness',
                'duration_days' => 21,
                'difficulty_level' => 'beginner',
                'icon' => '🌙',
                'color' => '#6f42c1'
            ],
            [
                'program_name' => 'Anxiety Management & Coping',
                'program_description' => 'Learn evidence-based techniques to manage anxiety, including breathing exercises, cognitive restructuring, and gradual exposure therapy.',
                'program_goal' => 'Reduce anxiety symptoms and develop effective coping strategies',
                'category' => 'Mental Health',
                'duration_days' => 60,
                'difficulty_level' => 'intermediate',
                'icon' => '🌊',
                'color' => '#17a2b8'
            ],
            [
                'program_name' => 'Self-Confidence & Personal Growth',
                'program_description' => 'Build self-confidence, improve self-esteem, and develop leadership skills through daily affirmations, goal-setting, and personal challenges.',
                'program_goal' => 'Increase self-confidence and achieve personal growth milestones',
                'category' => 'Personal Development',
                'duration_days' => 90,
                'difficulty_level' => 'advanced',
                'icon' => '👑',
                'color' => '#fd7e14'
            ]
        ];

        $sql = "INSERT IGNORE INTO programs (program_name, program_description, program_goal, category, duration_days, difficulty_level, icon, color) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($programs as $program) {
                $stmt->execute([
                    $program['program_name'], $program['program_description'], $program['program_goal'],
                    $program['category'], $program['duration_days'], $program['difficulty_level'],
                    $program['icon'], $program['color']
                ]);
            }
            return true;
        } catch(PDOException $exception) {
            echo "Program seeding error: " . $exception->getMessage();
            return false;
        }
    }

    public function seedTasks() {
        $tasks = [
            // Mindfulness Tasks
            ['task_name' => 'Morning Meditation', 'task_description' => '10-minute guided meditation to start your day', 'task_type' => 'daily', 'category' => 'Mindfulness', 'estimated_duration' => 10, 'difficulty_level' => 'easy'],
            ['task_name' => 'Breathing Exercise', 'task_description' => '5-minute deep breathing exercise', 'task_type' => 'daily', 'category' => 'Mindfulness', 'estimated_duration' => 5, 'difficulty_level' => 'easy'],
            ['task_name' => 'Body Scan', 'task_description' => '15-minute body scan meditation', 'task_type' => 'daily', 'category' => 'Mindfulness', 'estimated_duration' => 15, 'difficulty_level' => 'medium'],
            ['task_name' => 'Mindful Walking', 'task_description' => '20-minute mindful walking exercise', 'task_type' => 'daily', 'category' => 'Mindfulness', 'estimated_duration' => 20, 'difficulty_level' => 'easy'],
            
            // Emotional Health Tasks
            ['task_name' => 'Gratitude Journal', 'task_description' => 'Write 3 things you are grateful for', 'task_type' => 'daily', 'category' => 'Emotional', 'estimated_duration' => 5, 'difficulty_level' => 'easy'],
            ['task_name' => 'Emotion Check-in', 'task_description' => 'Identify and name your current emotions', 'task_type' => 'daily', 'category' => 'Emotional', 'estimated_duration' => 3, 'difficulty_level' => 'easy'],
            ['task_name' => 'Positive Affirmations', 'task_description' => 'Recite 5 positive affirmations', 'task_type' => 'daily', 'category' => 'Emotional', 'estimated_duration' => 5, 'difficulty_level' => 'easy'],
            ['task_name' => 'Stress Level Assessment', 'task_description' => 'Rate and reflect on your stress level (1-10)', 'task_type' => 'daily', 'category' => 'Emotional', 'estimated_duration' => 2, 'difficulty_level' => 'easy'],
            
            // Physical Wellness Tasks
            ['task_name' => 'Sleep Schedule Check', 'task_description' => 'Go to bed at your target time', 'task_type' => 'daily', 'category' => 'Physical', 'estimated_duration' => 1, 'difficulty_level' => 'easy'],
            ['task_name' => 'Screen Time Limit', 'task_description' => 'No screens 1 hour before bedtime', 'task_type' => 'daily', 'category' => 'Physical', 'estimated_duration' => 60, 'difficulty_level' => 'medium'],
            ['task_name' => 'Relaxation Routine', 'task_description' => 'Practice bedtime relaxation routine', 'task_type' => 'daily', 'category' => 'Physical', 'estimated_duration' => 15, 'difficulty_level' => 'easy'],
            ['task_name' => 'Water Intake', 'task_description' => 'Drink 8 glasses of water throughout the day', 'task_type' => 'daily', 'category' => 'Physical', 'estimated_duration' => 1, 'difficulty_level' => 'easy'],
            
            // Anxiety Management Tasks
            ['task_name' => 'Anxiety Breathing', 'task_description' => '4-7-8 breathing technique for anxiety relief', 'task_type' => 'daily', 'category' => 'Anxiety', 'estimated_duration' => 5, 'difficulty_level' => 'easy'],
            ['task_name' => 'Worry Time', 'task_description' => 'Dedicated 10 minutes to process worries', 'task_type' => 'daily', 'category' => 'Anxiety', 'estimated_duration' => 10, 'difficulty_level' => 'medium'],
            ['task_name' => 'Grounding Exercise', 'task_description' => '5-4-3-2-1 grounding technique', 'task_type' => 'daily', 'category' => 'Anxiety', 'estimated_duration' => 5, 'difficulty_level' => 'easy'],
            ['task_name' => 'Progressive Muscle Relaxation', 'task_description' => 'Tense and release muscle groups', 'task_type' => 'daily', 'category' => 'Anxiety', 'estimated_duration' => 15, 'difficulty_level' => 'medium'],
            
            // Personal Development Tasks
            ['task_name' => 'Goal Review', 'task_description' => 'Review and update your daily goals', 'task_type' => 'daily', 'category' => 'Development', 'estimated_duration' => 5, 'difficulty_level' => 'easy'],
            ['task_name' => 'Skill Practice', 'task_description' => 'Practice a new skill for 15 minutes', 'task_type' => 'daily', 'category' => 'Development', 'estimated_duration' => 15, 'difficulty_level' => 'medium'],
            ['task_name' => 'Self-Reflection', 'task_description' => 'Reflect on your progress and learnings', 'task_type' => 'daily', 'category' => 'Development', 'estimated_duration' => 10, 'difficulty_level' => 'medium'],
            ['task_name' => 'Confidence Building', 'task_description' => 'Complete one task outside your comfort zone', 'task_type' => 'daily', 'category' => 'Development', 'estimated_duration' => 30, 'difficulty_level' => 'hard'],
            
            // Weekly Tasks
            ['task_name' => 'Weekly Planning', 'task_description' => 'Plan your goals and tasks for the upcoming week', 'task_type' => 'weekly', 'category' => 'Planning', 'estimated_duration' => 30, 'difficulty_level' => 'medium'],
            ['task_name' => 'Progress Review', 'task_description' => 'Review your weekly progress and achievements', 'task_type' => 'weekly', 'category' => 'Reflection', 'estimated_duration' => 20, 'difficulty_level' => 'medium'],
            ['task_name' => 'Social Connection', 'task_description' => 'Reach out to a friend or family member', 'task_type' => 'weekly', 'category' => 'Social', 'estimated_duration' => 30, 'difficulty_level' => 'easy']
        ];

        $sql = "INSERT IGNORE INTO tasks (task_name, task_description, task_type, category, estimated_duration, difficulty_level) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($tasks as $task) {
                $stmt->execute([
                    $task['task_name'], $task['task_description'], $task['task_type'],
                    $task['category'], $task['estimated_duration'], $task['difficulty_level']
                ]);
            }
            return true;
        } catch(PDOException $exception) {
            echo "Task seeding error: " . $exception->getMessage();
            return false;
        }
    }

    public function seedAchievements() {
        $achievements = [
            ['achievement_name' => 'First Step', 'achievement_description' => 'Complete your first task', 'achievement_type' => 'milestone', 'requirement_value' => 1, 'icon' => '🎯', 'badge_color' => '#28a745'],
            ['achievement_name' => 'Week Warrior', 'achievement_description' => 'Maintain a 7-day streak', 'achievement_type' => 'streak', 'requirement_value' => 7, 'icon' => '🔥', 'badge_color' => '#fd7e14'],
            ['achievement_name' => 'Consistency King', 'achievement_description' => 'Maintain a 30-day streak', 'achievement_type' => 'streak', 'requirement_value' => 30, 'icon' => '👑', 'badge_color' => '#ffc107'],
            ['achievement_name' => 'Task Master', 'achievement_description' => 'Complete 100 tasks', 'achievement_type' => 'completion', 'requirement_value' => 100, 'icon' => '⭐', 'badge_color' => '#17a2b8'],
            ['achievement_name' => 'Mindful Soul', 'achievement_description' => 'Complete 50 mindfulness tasks', 'achievement_type' => 'completion', 'requirement_value' => 50, 'icon' => '🧘‍♀️', 'badge_color' => '#6f42c1'],
            ['achievement_name' => 'Wellness Champion', 'achievement_description' => 'Complete an entire program', 'achievement_type' => 'milestone', 'requirement_value' => 1, 'icon' => '🏆', 'badge_color' => '#FFD700']
        ];

        $sql = "INSERT IGNORE INTO achievements (achievement_name, achievement_description, achievement_type, requirement_value, icon, badge_color) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($achievements as $achievement) {
                $stmt->execute([
                    $achievement['achievement_name'], $achievement['achievement_description'], $achievement['achievement_type'],
                    $achievement['requirement_value'], $achievement['icon'], $achievement['badge_color']
                ]);
            }
            return true;
        } catch(PDOException $exception) {
            echo "Achievement seeding error: " . $exception->getMessage();
            return false;
        }
    }
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Create tables and seed data if they don't exist
if ($db) {
    $database->createTables();
    $database->seedPrograms();
    $database->seedTasks();
    $database->seedAchievements();
}
?>