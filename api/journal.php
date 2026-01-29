<?php
session_start();

// Suppress PHP notices and warnings to prevent JSON corruption
error_reporting(E_ERROR | E_PARSE);

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
    case 'get_entries':
        getEntries();
        break;
    case 'get_entry':
        getEntry();
        break;
    case 'save_entry':
        saveEntry();
        break;
    case 'update_entry':
        updateEntry();
        break;
    case 'delete_entry':
        deleteEntry();
        break;
    case 'get_stats':
        getJournalStats();
        break;
    case 'search_entries':
        searchEntries();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getEntries() {
    global $conn, $user_id;
    
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT * FROM journal_entries WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";
    
    // Add mood filter
    if ($filter !== 'all') {
        $sql .= " AND mood = ?";
        $params[] = $filter;
        $types .= "s";
    }
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR content LIKE ? OR tags LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'mood' => $row['mood'],
            'tags' => json_decode($row['tags'], true) ?: [],
            'is_private' => (bool)$row['is_private'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
}

function getEntry() {
    global $conn, $user_id;
    
    $entry_id = $_GET['entry_id'] ?? 0;
    
    if (!$entry_id) {
        echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Entry not found']);
        return;
    }
    
    $row = $result->fetch_assoc();
    $entry = [
        'id' => $row['id'],
        'title' => $row['title'],
        'content' => $row['content'],
        'mood' => $row['mood'],
        'tags' => json_decode($row['tags'], true) ?: [],
        'is_private' => (bool)$row['is_private'],
        'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at']
    ];
    
    echo json_encode([
        'success' => true,
        'entry' => $entry
    ]);
}

function saveEntry() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $title = trim($data['title'] ?? '');
    $content = trim($data['content'] ?? '');
    $mood = $data['mood'] ?? 'neutral';
    $tags = $data['tags'] ?? [];
    $is_private = $data['is_private'] ?? true;
    
    // Validation
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        return;
    }
    
    // Extract hashtags from content if tags not provided
    if (empty($tags)) {
        preg_match_all('/#(\w+)/', $content, $matches);
        $tags = $matches[1];
    }
    
    // Ensure tags is an array
    if (!is_array($tags)) {
        $tags = [];
    }
    
    // Remove duplicates and limit to 10 tags
    $tags = array_slice($tags, 0, 10);
    $tags = array_unique($tags);
    
    $stmt = $conn->prepare("INSERT INTO journal_entries (user_id, title, content, mood, tags, is_private, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("issssi", $user_id, $title, $content, $mood, json_encode($tags), $is_private);
    
    if ($stmt->execute()) {
        $entry_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Entry saved successfully',
            'entry_id' => $entry_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save entry']);
    }
}

function updateEntry() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $entry_id = $data['entry_id'] ?? 0;
    $title = trim($data['title'] ?? '');
    $content = trim($data['content'] ?? '');
    $mood = $data['mood'] ?? 'neutral';
    $tags = $data['tags'] ?? [];
    $is_private = $data['is_private'] ?? true;
    
    // Validation
    if (!$entry_id) {
        echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
        return;
    }
    
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        return;
    }
    
    // Extract hashtags from content if tags not provided
    if (empty($tags)) {
        preg_match_all('/#(\w+)/', $content, $matches);
        $tags = $matches[1];
    }
    
    // Ensure tags is an array
    if (!is_array($tags)) {
        $tags = [];
    }
    
    // Remove duplicates and limit to 10 tags
    $tags = array_slice($tags, 0, 10);
    $tags = array_unique($tags);
    
    // Check if entry belongs to user
    $stmt = $conn->prepare("SELECT id FROM journal_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Entry not found or access denied']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE journal_entries SET title = ?, content = ?, mood = ?, tags = ?, is_private = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssiii", $title, $content, $mood, json_encode($tags), $is_private, $entry_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Entry updated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update entry']);
    }
}

function deleteEntry() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $entry_id = $data['entry_id'] ?? $_GET['entry_id'] ?? 0;
    
    if (!$entry_id) {
        echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
        return;
    }
    
    // Check if entry belongs to user
    $stmt = $conn->prepare("SELECT id FROM journal_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Entry not found or access denied']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM journal_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Entry deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete entry']);
    }
}

function getJournalStats() {
    global $conn, $user_id;
    
    // Get total entries
    $stmt = $conn->prepare("SELECT COUNT(*) as total_entries FROM journal_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_entries = $result->fetch_assoc()['total_entries'];
    
    // Get entries this week
    $stmt = $conn->prepare("SELECT COUNT(*) as week_entries FROM journal_entries WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $week_entries = $result->fetch_assoc()['week_entries'];
    
    // Get average word count
    $stmt = $conn->prepare("SELECT content FROM journal_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_words = 0;
    $entry_count = 0;
    while ($row = $result->fetch_assoc()) {
        $word_count = str_word_count($row['content']);
        $total_words += $word_count;
        $entry_count++;
    }
    
    $avg_words = $entry_count > 0 ? round($total_words / $entry_count) : 0;
    
    // Get popular tags
    $stmt = $conn->prepare("SELECT tags FROM journal_entries WHERE user_id = ? AND tags IS NOT NULL AND tags != '[]'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tag_counts = [];
    while ($row = $result->fetch_assoc()) {
        $tags = json_decode($row['tags'], true);
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tag_counts[$tag] = ($tag_counts[$tag] ?? 0) + 1;
            }
        }
    }
    
    // Sort tags by count and get top 10
    arsort($tag_counts);
    $popular_tags = array_slice($tag_counts, 0, 10, true);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_entries' => $total_entries,
            'week_entries' => $week_entries,
            'avg_words' => $avg_words,
            'popular_tags' => $popular_tags
        ]
    ]);
}

function searchEntries() {
    global $conn, $user_id;
    
    $search = $_GET['q'] ?? $_POST['q'] ?? '';
    $mood_filter = $_GET['mood'] ?? $_POST['mood'] ?? '';
    
    if (empty($search)) {
        echo json_encode(['success' => false, 'message' => 'Search query is required']);
        return;
    }
    
    $sql = "SELECT * FROM journal_entries WHERE user_id = ? AND (title LIKE ? OR content LIKE ? OR tags LIKE ?)";
    $params = [$user_id];
    $types = "i";
    
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
    
    // Add mood filter if specified
    if (!empty($mood_filter)) {
        $sql .= " AND mood = ?";
        $params[] = $mood_filter;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'mood' => $row['mood'],
            'tags' => json_decode($row['tags'], true) ?: [],
            'is_private' => (bool)$row['is_private'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'entries' => $entries,
        'search_term' => $search,
        'results_count' => count($entries)
    ]);
}

$conn->close();
?>