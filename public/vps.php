<?php
// VPS Control Panel - PHP Frontend
// Uses Python backend for actual operations

session_start();

// Configuration
$python_api_url = "http://127.0.0.1:5000/api";
$valid_username = 'admin';
$valid_password = 'Iv@n0772717963';

// Handle login
if ($_POST['login'] ?? false) {
    if ($_POST['username'] === $valid_username && $_POST['password'] === $valid_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = $_POST['password']; // Store for API calls
    } else {
        $error = "Invalid credentials!";
    }
}

// Handle logout
if ($_GET['logout'] ?? false) {
    session_destroy();
    header('Location: ?');
    exit;
}

// Check if authenticated
$authenticated = $_SESSION['authenticated'] ?? false;

// Make API call to Python backend
function call_python_api($endpoint, $data = []) {
    global $python_api_url;
    
    $ch = curl_init($python_api_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $http_code === 200) {
        return json_decode($response, true);
    }
    
    return ['success' => false, 'error' => 'API connection failed'];
}

// Handle actions if authenticated
$api_result = null;
$current_dir = $_GET['dir'] ?? '/';
$system_info = null;
$directory_contents = null;

if ($authenticated) {
    $auth_data = [
        'username' => $_SESSION['username'],
        'password' => $_SESSION['password']
    ];
    
    // Execute command
    if (isset($_POST['command']) && !empty($_POST['command'])) {
        $api_result = call_python_api('/command', array_merge($auth_data, [
            'command' => $_POST['command']
        ]));
    }
    
    // Get system info
    if (!isset($_GET['action'])) {
        $system_info = call_python_api('/system-info', $auth_data);
    }
    
    // List directory
    $directory_contents = call_python_api('/ls', array_merge($auth_data, [
        'path' => $current_dir
    ]));
    
    // Handle file deletion
    if (isset($_GET['delete'])) {
        $delete_path = $current_dir . '/' . $_GET['delete'];
        $api_result = call_python_api('/delete', array_merge($auth_data, [
            'path' => $delete_path
        ]));
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPS Control Panel - Python Backend</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: white; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: #2d2d2d; border-radius: 8px; box-shadow: 0 2px 20px rgba(0,0,0,0.5); }
        .header { background: #27ae60; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .login-form { max-width: 400px; margin: 50px auto; padding: 20px; }
        input, button, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #555; border-radius: 4px; background: #3d3d3d; color: white; }
        button { background: #3498db; cursor: pointer; }
        button:hover { background: #2980b9; }
        .danger { background: #e74c3c; }
        .success { background: #27ae60; }
        .output { background: black; color: #0f0; padding: 15px; border-radius: 4px; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
        .panel { background: #3d3d3d; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 10px 0; }
        .quick-link { background: #3498db; color: white; padding: 10px; border-radius: 4px; text-decoration: none; text-align: center; }
        .file-table { width: 100%; border-collapse: collapse; }
        .file-table th, .file-table td { padding: 10px; border-bottom: 1px solid #555; text-align: left; }
        .file-table th { background: #34495e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐍 VPS Control Panel - Python Backend</h1>
            <p>PHP Frontend + Python Backend = Full System Access</p>
        </div>
        
        <div class="content">
            <?php if (!$authenticated): ?>
                <!-- Login Form -->
                <div class="login-form">
                    <?php if (isset($error)): ?>
                        <div class="output" style="color: #e74c3c;"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="login" value="1">
                        <input type="text" name="username" value="admin" placeholder="Username" required>
                        <input type="password" name="password" value="Iv@n0772717963" placeholder="Password" required>
                        <button type="submit" class="success">Login</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Control Panel -->
                <div style="text-align: right; margin-bottom: 20px;">
                    Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
                    <a href="?logout=1" style="color: #e74c3c; margin-left: 20px;">Logout</a>
                </div>

                <!-- System Information -->
                <?php if ($system_info && $system_info['success']): ?>
                <div class="panel">
                    <h3>📊 System Information</h3>
                    <div class="output">
                        <strong>Hostname:</strong> <?= htmlspecialchars($system_info['info']['hostname'] ?? 'N/A') ?><br>
                        <strong>Uptime:</strong> <?= htmlspecialchars($system_info['info']['uptime'] ?? 'N/A') ?><br>
                        <strong>User:</strong> <?= htmlspecialchars($system_info['info']['current_user'] ?? 'N/A') ?><br>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Command Execution -->
                <div class="panel">
                    <h3>💻 Execute Commands</h3>
                    <form method="POST">
                        <input type="text" name="command" placeholder="ls -la /var/www" value="<?= htmlspecialchars($_POST['command'] ?? '') ?>">
                        <button type="submit">Execute</button>
                    </form>
                    
                    <?php if ($api_result): ?>
                        <div class="output">
                            <?= htmlspecialchars($api_result['output'] ?? $api_result['error'] ?? 'No output') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- File Browser -->
                <div class="panel">
                    <h3>📁 File Browser</h3>
                    <div class="quick-links">
                        <a href="?dir=/" class="quick-link">Root /</a>
                        <a href="?dir=/var/www" class="quick-link">/var/www</a>
                        <a href="?dir=/home" class="quick-link">/home</a>
                        <a href="?dir=/etc" class="quick-link">/etc</a>
                    </div>

                    <?php if ($directory_contents): ?>
                        <?php if ($directory_contents['success'] && !empty($directory_contents['items'])): ?>
                            <table class="file-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Permissions</th>
                                        <th>Owner</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($directory_contents['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if ($item['is_dir']): ?>
                                                    📁 <a href="?dir=<?= urlencode($item['path']) ?>"><?= htmlspecialchars($item['name']) ?>/</a>
                                                <?php else: ?>
                                                    📄 <?= htmlspecialchars($item['name']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $item['is_dir'] ? 'DIR' : number_format($item['size']) ?> bytes</td>
                                            <td><code><?= htmlspecialchars($item['perms']) ?></code></td>
                                            <td><?= htmlspecialchars($item['owner']) ?></td>
                                            <td>
                                                <?php if (!$item['is_dir']): ?>
                                                    <a href="?dir=<?= urlencode($current_dir) ?>&delete=<?= urlencode($item['name']) ?>" 
                                                       class="danger" 
                                                       onclick="return confirm('Delete <?= htmlspecialchars($item['name']) ?>?')">Delete</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="output">
                                <?= htmlspecialchars($directory_contents['error'] ?? 'Directory is empty or inaccessible') ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>