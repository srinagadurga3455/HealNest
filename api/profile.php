<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../config/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_profile':
        getProfile();
        break;
    case 'update_profile':
        updateProfile();
        break;
    case 'get_user_stats':
        getUserStats();
        break;
    case 'get_achievements':
        getAchievements();
        break;
    case 'get_progress_tracking':
        getProgressTracking();
        break;
    case 'get_recent_activity':
        getRecentActivity();
        break;
    case 'upload_avatar':
        uploadAvatar();
        break;
    case 'change_password':
        changePassword();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getProfile() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    $user = $result->fetch_assoc();
    
    // Remove sensitive data
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'profile' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'birth_date' => $user['birth_date'],
            'bio' => $user['bio'],
            'avatar_path' => $user['avatar_path'],
            'current_streak' => $user['current_streak'],
            'highest_streak' => $user['highest_streak'],
            'wellness_score' => $user['wellness_score'],
            'assessment_taken' => (bool)$user['assessment_taken'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at']
        ]
    ]);
}

function updateProfile() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $birth_date = $data['birth_date'] ?? null;
    $bio = trim($data['bio'] ?? '');
    
    // Validation
    if (empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Full name is required']);
        return;
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Valid email is required']);
        return;
    }
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already taken']);
        return;
    }
    
    // Validate birth date if provided
    if ($birth_date && !empty($birth_date)) {
        $birth_date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
        if (!$birth_date_obj || $birth_date_obj->format('Y-m-d') !== $birth_date) {
            echo json_encode(['success' => false, 'message' => 'Invalid birth date format']);
            return;
        }
        
        // Check if birth date is not in the future
        if ($birth_date_obj > new DateTime()) {
            echo json_encode(['success' => false, 'message' => 'Birth date cannot be in the future']);
            return;
        }
    } else {
        $birth_date = null;
    }
    
    // Update profile
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, birth_date = ?, bio = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssssi", $full_name, $email, $phone, $birth_date, $bio, $user_id);
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
}

function getUserStats() {
    global $conn, $user_id;
    
    // Get user basic info
    $stmt = $conn->prepare("SELECT current_streak, highest_streak, wellness_score, created_at, assigned_program_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    // Calculate days active
    $created_date = new DateTime($user_data['created_at']);
    $today = new DateTime();
    $days_active = $created_date->diff($today)->days + 1;
    
    // Get journal entries count
    $stmt = $conn->prepare("SELECT COUNT(*) as journal_count FROM journal_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $journal_count = $result->fetch_assoc()['journal_count'];
    
    // Get mood tracking days
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT entry_date) as mood_days FROM mood_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mood_days = $result->fetch_assoc()['mood_days'];
    
    // Get assessments count
    $stmt = $conn->prepare("SELECT COUNT(*) as assessment_count FROM assessments WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assessment_count = $result->fetch_assoc()['assessment_count'];
    
    // Get programs enrolled (simplified - just check if user has assigned program)
    $programs_enrolled = $user_data['assigned_program_id'] ? 1 : 0;
    
    // Get completed tasks count
    $stmt = $conn->prepare("SELECT COUNT(*) as completed_tasks FROM user_task_completions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed_tasks = $result->fetch_assoc()['completed_tasks'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'days_active' => $days_active,
            'journal_entries' => $journal_count,
            'mood_days' => $mood_days,
            'programs_enrolled' => $programs_enrolled,
            'assessments' => $assessment_count,
            'completed_tasks' => $completed_tasks,
            'current_streak' => $user_data['current_streak'],
            'highest_streak' => $user_data['highest_streak'],
            'wellness_score' => $user_data['wellness_score']
        ]
    ]);
}

function getAchievements() {
    global $conn, $user_id;
    
    // Get user achievements
    $stmt = $conn->prepare("
        SELECT ua.achievement_id, ua.earned_at, a.achievement_name, a.achievement_description, a.icon, a.badge_color
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.id
        WHERE ua.user_id = ?
        ORDER BY ua.earned_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $earned_achievements = [];
    while ($row = $result->fetch_assoc()) {
        $earned_achievements[] = [
            'id' => $row['achievement_id'],
            'name' => $row['achievement_name'],
            'description' => $row['achievement_description'],
            'icon' => $row['icon'],
            'badge_color' => $row['badge_color'],
            'earned_at' => $row['earned_at']
        ];
    }
    
    // Get all available achievements
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE is_active = 1 ORDER BY requirement_value ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $all_achievements = [];
    while ($row = $result->fetch_assoc()) {
        $all_achievements[] = [
            'id' => $row['id'],
            'name' => $row['achievement_name'],
            'description' => $row['achievement_description'],
            'type' => $row['achievement_type'],
            'requirement_value' => $row['requirement_value'],
            'icon' => $row['icon'],
            'badge_color' => $row['badge_color']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'earned_achievements' => $earned_achievements,
        'all_achievements' => $all_achievements
    ]);
}

function getProgressTracking() {
    global $conn, $user_id;
    
    // Get user stats for progress calculation
    $stats = getUserStatsData();
    
    $progress_items = [
        [
            'title' => 'Journal Writing',
            'subtitle' => $stats['journal_entries'] . ' entries written',
            'progress' => min(($stats['journal_entries'] / 25) * 100, 100),
            'target' => 25
        ],
        [
            'title' => 'Mood Tracking',
            'subtitle' => $stats['mood_days'] . ' days tracked',
            'progress' => min(($stats['mood_days'] / 30) * 100, 100),
            'target' => 30
        ],
        [
            'title' => 'Task Completion',
            'subtitle' => $stats['completed_tasks'] . ' tasks completed',
            'progress' => min(($stats['completed_tasks'] / 50) * 100, 100),
            'target' => 50
        ],
        [
            'title' => 'Wellness Score',
            'subtitle' => 'Current score: ' . $stats['wellness_score'],
            'progress' => $stats['wellness_score'],
            'target' => 100
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'progress_items' => $progress_items
    ]);
}

function getRecentActivity() {
    global $conn, $user_id;
    
    $activities = [];
    
    // Get recent journal entries
    $stmt = $conn->prepare("SELECT title, created_at FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'journal',
            'title' => 'Wrote "' . $row['title'] . '"',
            'time' => $row['created_at'],
            'icon' => '📝'
        ];
    }
    
    // Get recent mood entries
    $stmt = $conn->prepare("SELECT mood, entry_date, created_at FROM mood_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'mood',
            'title' => 'Tracked mood: ' . $row['mood'],
            'time' => $row['created_at'],
            'icon' => '😊'
        ];
    }
    
    // Get recent task completions
    $stmt = $conn->prepare("
        SELECT t.task_name, utc.completed_at 
        FROM user_task_completions utc
        JOIN tasks t ON utc.task_id = t.id
        WHERE utc.user_id = ? 
        ORDER BY utc.completed_at DESC 
        LIMIT 3
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'task',
            'title' => 'Completed: ' . $row['task_name'],
            'time' => $row['completed_at'],
            'icon' => '✅'
        ];
    }
    
    // Get recent assessments
    $stmt = $conn->prepare("SELECT wellness_score, completed_at FROM assessments WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => 'assessment',
            'title' => 'Completed wellness assessment (Score: ' . $row['wellness_score'] . ')',
            'time' => $row['completed_at'],
            'icon' => '📊'
        ];
    }
    
    // Sort by time and take most recent
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    $recent_activities = array_slice($activities, 0, 5);
    
    echo json_encode([
        'success' => true,
        'activities' => $recent_activities
    ]);
}

function uploadAvatar() {
    global $conn, $user_id;
    
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        return;
    }
    
    $file = $_FILES['avatar'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed']);
        return;
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
        return;
    }
    
    // Create avatars directory if it doesn't exist
    $upload_dir = '../avatars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update user avatar path in database
        $avatar_path = '/avatars/' . $filename;
        $stmt = $conn->prepare("UPDATE users SET avatar_path = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $avatar_path, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_path' => $avatar_path
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update avatar path']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }
}

function changePassword() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required']);
        return;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        return;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
        return;
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        return;
    }
    
    // Update password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_password_hash, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to change password']);
    }
}

function getUserStatsData() {
    global $conn, $user_id;
    
    // Get user basic info
    $stmt = $conn->prepare("SELECT current_streak, highest_streak, wellness_score FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    // Get journal entries count
    $stmt = $conn->prepare("SELECT COUNT(*) as journal_count FROM journal_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $journal_count = $result->fetch_assoc()['journal_count'];
    
    // Get mood tracking days
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT entry_date) as mood_days FROM mood_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mood_days = $result->fetch_assoc()['mood_days'];
    
    // Get completed tasks count
    $stmt = $conn->prepare("SELECT COUNT(*) as completed_tasks FROM user_task_completions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed_tasks = $result->fetch_assoc()['completed_tasks'];
    
    return [
        'journal_entries' => $journal_count,
        'mood_days' => $mood_days,
        'completed_tasks' => $completed_tasks,
        'current_streak' => $user_data['current_streak'],
        'highest_streak' => $user_data['highest_streak'],
        'wellness_score' => $user_data['wellness_score']
    ];
}

$conn->close();
?>