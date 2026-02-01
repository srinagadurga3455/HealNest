<?php
session_start();
header('Content-Type: application/json');

// Add output buffering to prevent any HTML output from corrupting JSON
ob_start();

// Include database connection
require_once '../config/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get action from either GET, POST, or JSON body
$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    // Try to get from JSON body
    $json_input = file_get_contents("php://input");
    if ($json_input) {
        $json_data = json_decode($json_input, true);
        if ($json_data && isset($json_data['action'])) {
            $action = $json_data['action'];
        }
    }
}

// Clear any output that might have been generated
ob_clean();

switch ($action) {
    case 'get_today_mood':
        getTodayMood();
        break;
    case 'save_mood':
        saveMood();
        break;
    case 'get_mood_data':
        getMoodData();
        break;
    case 'get_mood_stats':
        getMoodStats();
        break;
    case 'get_recent_entries':
        getRecentEntries();
        break;
    case 'get_mood_trend':
        getMoodTrend();
        break;
    case 'get_mood_calendar':
        getMoodCalendar();
        break;
    case 'test':
        testConnection();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
}

function getTodayMood() {
    global $conn, $user_id;
    
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND entry_date = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mood = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'mood' => [
                'mood' => $mood['mood'],
                'mood_score' => $mood['mood_score'],
                'note' => $mood['note'],
                'date' => $mood['entry_date']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No mood entry for today'
        ]);
    }
}

function saveMood() {
    global $conn, $user_id;
    
    // Get data from JSON body or POST
    $data = null;
    $json_input = file_get_contents("php://input");
    if ($json_input) {
        $data = json_decode($json_input, true);
    }
    if (!$data) {
        $data = $_POST;
    }
    
    error_log("Mood save data received: " . print_r($data, true));
    
    $mood = $data['mood'] ?? '';
    $mood_score = $data['mood_score'] ?? getMoodScore($mood);
    $note = trim($data['note'] ?? '');
    $entry_date = $data['date'] ?? date('Y-m-d');
    
    // Validation
    if (empty($mood)) {
        error_log("Mood save failed: mood is empty");
        echo json_encode(['success' => false, 'message' => 'Mood is required']);
        return;
    }
    
    // Validate mood value
    $valid_moods = ['excellent', 'good', 'neutral', 'challenging', 'difficult'];
    if (!in_array($mood, $valid_moods)) {
        error_log("Mood save failed: invalid mood value: " . $mood);
        echo json_encode(['success' => false, 'message' => 'Invalid mood value']);
        return;
    }
    
    error_log("Saving mood for user $user_id: mood=$mood, score=$mood_score, date=$entry_date");
    
    try {
        // Check if mood already exists for this date
        $stmt = $conn->prepare("SELECT id FROM mood_entries WHERE user_id = ? AND entry_date = ?");
        $stmt->bind_param("is", $user_id, $entry_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing mood
            error_log("Updating existing mood entry");
            $stmt = $conn->prepare("UPDATE mood_entries SET mood = ?, mood_score = ?, note = ?, created_at = NOW() WHERE user_id = ? AND entry_date = ?");
            $stmt->bind_param("sisis", $mood, $mood_score, $note, $user_id, $entry_date);
        } else {
            // Insert new mood
            error_log("Creating new mood entry");
            $stmt = $conn->prepare("INSERT INTO mood_entries (user_id, mood, mood_score, note, entry_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isiss", $user_id, $mood, $mood_score, $note, $entry_date);
        }
        
        if ($stmt->execute()) {
            error_log("Mood saved successfully");
            echo json_encode([
                'success' => true,
                'message' => 'Mood saved successfully'
            ]);
        } else {
            error_log("Database error: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        error_log("Exception in saveMood: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getMoodData() {
    global $conn, $user_id;
    
    $days = $_GET['days'] ?? 30; // Default to last 30 days
    
    $stmt = $conn->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY entry_date DESC");
    $stmt->bind_param("ii", $user_id, $days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mood_data = [];
    while ($row = $result->fetch_assoc()) {
        $mood_data[$row['entry_date']] = [
            'mood' => $row['mood'],
            'mood_score' => $row['mood_score'],
            'note' => $row['note']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'mood_data' => $mood_data
    ]);
}

function getMoodStats() {
    global $conn, $user_id;
    
    $period = $_GET['period'] ?? 'month'; // month, week, year
    
    $date_condition = '';
    switch ($period) {
        case 'week':
            $date_condition = 'AND entry_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
            break;
        case 'year':
            $date_condition = 'AND entry_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
            break;
        case 'month':
        default:
            $date_condition = 'AND entry_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
            break;
    }
    
    // Get mood counts
    $stmt = $conn->prepare("SELECT mood, COUNT(*) as count FROM mood_entries WHERE user_id = ? $date_condition GROUP BY mood");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mood_counts = [
        'excellent' => 0,
        'good' => 0,
        'neutral' => 0,
        'challenging' => 0,
        'difficult' => 0
    ];
    
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $mood_counts[$row['mood']] = (int)$row['count'];
        $total += (int)$row['count'];
    }
    
    // Calculate average mood score
    $stmt = $conn->prepare("SELECT AVG(mood_score) as avg_score FROM mood_entries WHERE user_id = ? $date_condition");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $avg_score = $result->fetch_assoc()['avg_score'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'moods' => $mood_counts,
            'total' => $total,
            'average_score' => round($avg_score, 2),
            'period' => $period
        ]
    ]);
}

function getRecentEntries() {
    global $conn, $user_id;
    
    $limit = $_GET['limit'] ?? 10;
    
    $stmt = $conn->prepare("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY entry_date DESC, created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = [
            'id' => $row['id'],
            'mood' => $row['mood'],
            'mood_score' => $row['mood_score'],
            'note' => $row['note'],
            'date' => $row['entry_date'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
}

function getMoodTrend() {
    global $conn, $user_id;
    
    $days = $_GET['days'] ?? 7; // Default to last 7 days
    
    $stmt = $conn->prepare("SELECT entry_date, mood_score FROM mood_entries WHERE user_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY entry_date ASC");
    $stmt->bind_param("ii", $user_id, $days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trend_data = [];
    while ($row = $result->fetch_assoc()) {
        $trend_data[] = [
            'date' => $row['entry_date'],
            'score' => (int)$row['mood_score']
        ];
    }
    
    // Calculate trend direction
    $trend_direction = 'stable';
    $trend_percentage = 0;
    
    if (count($trend_data) >= 2) {
        $first_half = array_slice($trend_data, 0, ceil(count($trend_data) / 2));
        $second_half = array_slice($trend_data, ceil(count($trend_data) / 2));
        
        $first_avg = array_sum(array_column($first_half, 'score')) / count($first_half);
        $second_avg = array_sum(array_column($second_half, 'score')) / count($second_half);
        
        $difference = $second_avg - $first_avg;
        $trend_percentage = round(($difference / $first_avg) * 100, 1);
        
        if ($difference > 0.3) {
            $trend_direction = 'improving';
        } elseif ($difference < -0.3) {
            $trend_direction = 'declining';
        }
    }
    
    echo json_encode([
        'success' => true,
        'trend' => [
            'direction' => $trend_direction,
            'percentage' => $trend_percentage,
            'data' => $trend_data
        ]
    ]);
}

function getMoodCalendar() {
    global $conn, $user_id;
    
    $year = $_GET['year'] ?? date('Y');
    $month = $_GET['month'] ?? date('n');
    
    // Get first and last day of the month
    $first_day = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $last_day = date('Y-m-t', strtotime($first_day));
    
    $stmt = $conn->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND entry_date BETWEEN ? AND ? ORDER BY entry_date");
    $stmt->bind_param("iss", $user_id, $first_day, $last_day);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calendar_data = [];
    while ($row = $result->fetch_assoc()) {
        $calendar_data[$row['entry_date']] = [
            'mood' => $row['mood'],
            'mood_score' => $row['mood_score'],
            'note' => $row['note']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'calendar' => $calendar_data,
        'year' => (int)$year,
        'month' => (int)$month
    ]);
}

function getMoodScore($mood) {
    $mood_scores = [
        'excellent' => 5,
        'good' => 4,
        'neutral' => 3,
        'challenging' => 2,
        'difficult' => 1
    ];
    
    return $mood_scores[$mood] ?? 3;
}

function testConnection() {
    global $conn, $user_id;
    
    try {
        // Test database connection
        $result = $conn->query("SELECT 1");
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $conn->error]);
            return;
        }
        
        // Test mood_entries table
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM mood_entries WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Connection test successful',
            'user_id' => $user_id,
            'mood_entries_count' => $count,
            'database' => 'healnest_db'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Test failed: ' . $e->getMessage()]);
    }
}

$conn->close();
?>