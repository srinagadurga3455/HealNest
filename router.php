<?php
// Simple PHP Router for HealNest
// This file routes requests to the correct location

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = str_replace('/php-projects/HealNest', '', $request);

// Remove query string
$request = strtok($request, '?');

// If request is just /, redirect to redesign/landing.html
if ($request === '/' || $request === '') {
    header('Location: /redesign/landing.html');
    exit;
}

// Check if file exists in redesign folder
if (preg_match('/\.(html|css|js|json)$/', $request)) {
    // If it's in redesign folder, serve it
    if (file_exists(__DIR__ . '/redesign' . $request)) {
        return false; // Let PHP serve the static file
    }
    
    // If request is for a page that should be in redesign folder
    if (in_array(basename($request), ['landing.html', 'login.html', 'register.html', 'dashboard.html', 'assessment.html', 'program.html', 'journal.html', 'mood.html', 'profile.html'])) {
        if (file_exists(__DIR__ . '/redesign/' . basename($request))) {
            require __DIR__ . '/redesign/' . basename($request);
            exit;
        }
    }
    
    // Try to serve from redesign or root
    if (file_exists(__DIR__ . '/redesign' . $request)) {
        return false;
    }
    
    if (file_exists(__DIR__ . $request)) {
        return false;
    }
}

// If file doesn't exist, return 404
http_response_code(404);
echo "Not Found: " . htmlspecialchars($request);
?>
