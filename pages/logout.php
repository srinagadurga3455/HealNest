<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Clear any client-side storage
echo '<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
        <h2>Logging out...</h2>
        <p>Please wait while we log you out securely.</p>
    </div>
    
    <script>
        // Clear client-side storage
        localStorage.clear();
        sessionStorage.clear();
        
        // Redirect to landing page
        setTimeout(() => {
            window.location.href = "pages/landing.html";
        }, 1000);
    </script>
</body>
</html>';
?>