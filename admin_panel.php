<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealNest Admin Panel</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8fafc;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            background: #5D87FF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover {
            background: #4c7fff;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌟 HealNest Admin Panel</h1>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <button class="btn" onclick="testDashboardAPI()">Test Dashboard API</button>
            <button class="btn" onclick="resetDemoUser()">Reset Demo User</button>
            <button class="btn" onclick="clearSessions()">Clear All Sessions</button>
            <button class="btn btn-danger" onclick="resetDatabase()">Reset Database</button>
        </div>

        <div class="card">
            <h2>Users</h2>
            <div id="usersTable">Loading...</div>
        </div>

        <div class="card">
            <h2>Programs & Tasks</h2>
            <div id="programsTable">Loading...</div>
        </div>

        <div class="card">
            <h2>Recent Activity</h2>
            <div id="activityTable">Loading...</div>
        </div>

        <div class="card">
            <h2>API Test Results</h2>
            <div id="apiResults"></div>
        </div>
    </div>

    <script>
        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            loadPrograms();
            loadActivity();
        });

        async function testDashboardAPI() {
            const resultsDiv = document.getElementById('apiResults');
            resultsDiv.innerHTML = '<p>Testing API...</p>';
            
            try {
                const response = await fetch('api/dashboard.php?action=get_dashboard_data', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                resultsDiv.innerHTML = `
                    <h3>API Response:</h3>
                    <pre style="background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto;">${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="alert alert-error">API Error: ${error.message}</div>`;
            }
        }

        async function loadUsers() {
            try {
                const response = await fetch('admin_api.php?action=get_users');
                const data = await response.json();
                
                if (data.success) {
                    let html = '<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Program</th><th>Streak</th><th>Last Activity</th></tr>';
                    data.users.forEach(user => {
                        html += `<tr>
                            <td>${user.id}</td>
                            <td>${user.full_name}</td>
                            <td>${user.email}</td>
                            <td>${user.program_name || 'None'}</td>
                            <td>${user.current_streak}/${user.highest_streak}</td>
                            <td>${user.last_activity_date || 'Never'}</td>
                        </tr>`;
                    });
                    html += '</table>';
                    document.getElementById('usersTable').innerHTML = html;
                }
            } catch (error) {
                document.getElementById('usersTable').innerHTML = `<div class="alert alert-error">Error loading users: ${error.message}</div>`;
            }
        }

        async function loadPrograms() {
            try {
                const response = await fetch('admin_api.php?action=get_programs');
                const data = await response.json();
                
                if (data.success) {
                    let html = '<table><tr><th>ID</th><th>Name</th><th>Duration</th><th>Tasks</th><th>Status</th></tr>';
                    data.programs.forEach(program => {
                        html += `<tr>
                            <td>${program.id}</td>
                            <td>${program.icon} ${program.program_name}</td>
                            <td>${program.duration_days} days</td>
                            <td>${program.task_count} tasks</td>
                            <td><span class="status-badge ${program.is_active ? 'status-active' : 'status-inactive'}">${program.is_active ? 'Active' : 'Inactive'}</span></td>
                        </tr>`;
                    });
                    html += '</table>';
                    document.getElementById('programsTable').innerHTML = html;
                }
            } catch (error) {
                document.getElementById('programsTable').innerHTML = `<div class="alert alert-error">Error loading programs: ${error.message}</div>`;
            }
        }

        async function loadActivity() {
            try {
                const response = await fetch('admin_api.php?action=get_activity');
                const data = await response.json();
                
                if (data.success) {
                    let html = '<table><tr><th>Date</th><th>User</th><th>Activity</th><th>Details</th></tr>';
                    data.activity.forEach(item => {
                        html += `<tr>
                            <td>${item.date}</td>
                            <td>${item.user_name}</td>
                            <td>${item.activity_type}</td>
                            <td>${item.details}</td>
                        </tr>`;
                    });
                    html += '</table>';
                    document.getElementById('activityTable').innerHTML = html;
                }
            } catch (error) {
                document.getElementById('activityTable').innerHTML = `<div class="alert alert-error">Error loading activity: ${error.message}</div>`;
            }
        }

        async function resetDemoUser() {
            if (confirm('Reset demo user data?')) {
                try {
                    const response = await fetch('admin_api.php?action=reset_demo_user', {
                        method: 'POST'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Demo user reset successfully!');
                        loadUsers();
                        loadActivity();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        async function clearSessions() {
            if (confirm('Clear all user sessions?')) {
                try {
                    const response = await fetch('admin_api.php?action=clear_sessions', {
                        method: 'POST'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Sessions cleared successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        async function resetDatabase() {
            if (confirm('WARNING: This will reset all data! Are you sure?')) {
                try {
                    const response = await fetch('admin_api.php?action=reset_database', {
                        method: 'POST'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Database reset successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
    </script>
</body>
</html>