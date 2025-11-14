<?php
// Professional VPS Control Panel - PHP Frontend
// Enterprise-grade with file editing and fail-safe features

session_start();

// Configuration
$python_api_url = "http://127.0.0.1:5000/api";
$valid_username = 'admin';
$valid_password = 'Iv@n0772717963';

// Enhanced session management
if ($_POST['login'] ?? false) {
    if ($_POST['username'] === $valid_username && $_POST['password'] === $valid_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = $_POST['password'];
        $_SESSION['last_activity'] = time();
    } else {
        $error = "Invalid credentials!";
    }
}

// Session timeout (1 hour)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    session_destroy();
    header('Location: ?');
    exit;
}
$_SESSION['last_activity'] = time();

// Handle logout
if ($_GET['logout'] ?? false) {
    session_destroy();
    header('Location: ?');
    exit;
}

$authenticated = $_SESSION['authenticated'] ?? false;

// Enhanced API call function with error handling
function call_python_api($endpoint, $data = []) {
    global $python_api_url;
    
    $ch = curl_init($python_api_url . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response && $http_code === 200) {
        return json_decode($response, true);
    }
    
    return ['success' => false, 'error' => $error ?: 'API connection failed'];
}

// Initialize variables
$api_result = null;
$current_dir = $_GET['dir'] ?? '/';
$system_health = null;
$directory_contents = null;
$file_content = null;
$editing_file = null;

if ($authenticated) {
    $auth_data = [
        'username' => $_SESSION['username'],
        'password' => $_SESSION['password']
    ];
    
    // Get system health
    if (!isset($_GET['action'])) {
        $system_health = call_python_api('/health', $auth_data);
    }
    
    // List directory
    $directory_contents = call_python_api('/ls', array_merge($auth_data, [
        'path' => $current_dir
    ]));
    
    // Handle actions
    if (isset($_POST['command'])) {
        $api_result = call_python_api('/command', array_merge($auth_data, [
            'command' => $_POST['command']
        ]));
    }
    
    // File operations
    if (isset($_GET['delete'])) {
        $delete_path = $current_dir . '/' . $_GET['delete'];
        $api_result = call_python_api('/delete', array_merge($auth_data, [
            'path' => $delete_path
        ]));
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
    
    if (isset($_GET['edit'])) {
        $editing_file = $current_dir . '/' . $_GET['edit'];
        $file_content = call_python_api('/read', array_merge($auth_data, [
            'path' => $editing_file
        ]));
    }
    
    if (isset($_POST['save_file'])) {
        $api_result = call_python_api('/write', array_merge($auth_data, [
            'path' => $_POST['file_path'],
            'content' => $_POST['file_content']
        ]));
        if ($api_result['success']) {
            header('Location: ?dir=' . urlencode(dirname($_POST['file_path'])));
            exit;
        }
    }
    
    if (isset($_POST['create_item'])) {
        $create_path = $current_dir . '/' . $_POST['item_name'];
        $api_result = call_python_api('/create', array_merge($auth_data, [
            'path' => $create_path,
            'is_dir' => ($_POST['item_type'] === 'directory')
        ]));
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
}

// Function to get file icon
function get_file_icon($file_type, $is_dir) {
    if ($is_dir) return '📁';
    switch ($file_type) {
        case 'html': return '🌐';
        case 'code': return '📝';
        case 'image': return '🖼️';
        case 'text': return '📄';
        default: return '📄';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional VPS Control Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --dark: #1a1a1a;
            --light: #ecf0f1;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--dark); color: var(--light); line-height: 1.6; }
        
        .container { max-width: 1400px; margin: 0 auto; background: #2d2d2d; border-radius: 10px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 25px; text-align: center; }
        
        .navbar { background: var(--primary); padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: var(--light); text-decoration: none; margin: 0 15px; padding: 8px 15px; border-radius: 5px; transition: background 0.3s; }
        .nav-links a:hover { background: rgba(255,255,255,0.1); }
        
        .content { padding: 25px; }
        .panel { background: #3d3d3d; border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid var(--secondary); }
        .panel-header { display: flex; justify-content: between; align-items: center; margin-bottom: 15px; }
        .panel-header h3 { color: var(--secondary); margin: 0; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
        
        .btn { display: inline-block; padding: 10px 20px; background: var(--secondary); color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; transition: background 0.3s; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: var(--success); }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: var(--danger); }
        .btn-danger:hover { background: #c0392b; }
        .btn-warning { background: var(--warning); }
        .btn-warning:hover { background: #e67e22; }
        
        input, textarea, select { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #555; border-radius: 5px; background: #4d4d4d; color: var(--light); font-size: 14px; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--secondary); }
        
        .file-table { width: 100%; border-collapse: collapse; background: #4d4d4d; border-radius: 5px; overflow: hidden; }
        .file-table th, .file-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #555; }
        .file-table th { background: var(--primary); color: var(--light); font-weight: 600; }
        .file-table tr:hover { background: #5d5d5d; }
        
        .status-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
        .status-active { background: var(--success); }
        .status-inactive { background: var(--danger); }
        .status-unknown { background: var(--warning); }
        
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 15px 0; }
        .quick-action { background: #4d4d4d; padding: 15px; border-radius: 5px; text-align: center; transition: transform 0.2s; }
        .quick-action:hover { transform: translateY(-2px); background: #5d5d5d; }
        
        .editor-container { background: #2d2d2d; border-radius: 5px; overflow: hidden; }
        .editor-toolbar { background: var(--primary); padding: 10px; display: flex; justify-content: space-between; }
        .editor-content { padding: 0; }
        textarea.code-editor { width: 100%; height: 400px; font-family: 'Courier New', monospace; background: #1a1a1a; color: #f8f8f2; border: none; padding: 15px; resize: vertical; }
        
        .alert { padding: 15px; border-radius: 5px; margin: 15px 0; }
        .alert-success { background: rgba(39, 174, 96, 0.2); border-left: 4px solid var(--success); }
        .alert-error { background: rgba(231, 76, 60, 0.2); border-left: 4px solid var(--danger); }
        .alert-warning { background: rgba(243, 156, 18, 0.2); border-left: 4px solid var(--warning); }
        
        .system-health { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .health-item { background: #4d4d4d; padding: 15px; border-radius: 5px; }
        
        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
            .navbar { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-server"></i> Professional VPS Control Panel</h1>
            <p>Enterprise-grade server management with full file editing capabilities</p>
        </div>
        
        <?php if (!$authenticated): ?>
        <div class="content">
            <div class="panel" style="max-width: 400px; margin: 50px auto;">
                <h3><i class="fas fa-lock"></i> Secure Login</h3>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    <input type="text" name="username" value="admin" placeholder="Username" required>
                    <input type="password" name="password" value="Iv@n0772717963" placeholder="Password" required>
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login to Control Panel
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="navbar">
            <div class="nav-links">
                <a href="?"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="?dir=/"><i class="fas fa-folder"></i> File Manager</a>
                <a href="?"><i class="fas fa-terminal"></i> Terminal</a>
                <a href="?"><i class="fas fa-database"></i> Databases</a>
            </div>
            <div style="color: var(--light);">
                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                <a href="?logout=1" class="btn btn-danger" style="margin-left: 15px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="content">
            <!-- System Health Dashboard -->
            <?php if ($system_health && $system_health['success']): ?>
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-heartbeat"></i> System Health</h3>
                    <span class="btn" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Refresh</span>
                </div>
                
                <div class="system-health">
                    <div class="health-item">
                        <h4><i class="fas fa-microchip"></i> System Load</h4>
                        <div class="output" style="font-size: 12px; margin-top: 10px;">
                            <?= nl2br(htmlspecialchars($system_health['health']['load'] ?? 'N/A')) ?>
                        </div>
                    </div>
                    
                    <div class="health-item">
                        <h4><i class="fas fa-hdd"></i> Disk Usage</h4>
                        <div class="output" style="font-size: 12px; margin-top: 10px;">
                            <?= nl2br(htmlspecialchars(substr($system_health['health']['disk'] ?? 'N/A', 0, 200))) ?>
                        </div>
                    </div>
                    
                    <div class="health-item">
                        <h4><i class="fas fa-memory"></i> Memory</h4>
                        <div class="output" style="font-size: 12px; margin-top: 10px;">
                            <?= nl2br(htmlspecialchars($system_health['health']['memory'] ?? 'N/A')) ?>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <h4><i class="fas fa-globe"></i> Detected Websites</h4>
                    <div class="quick-actions">
                        <?php foreach (array_slice($system_health['health']['websites'] ?? [], 0, 6) as $website): ?>
                            <?php if (!empty(trim($website))): ?>
                                <a href="?dir=<?= urlencode($website) ?>" class="quick-action">
                                    <i class="fas fa-folder"></i> <?= htmlspecialchars(basename($website)) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- File Editor -->
            <?php if ($editing_file && $file_content): ?>
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-edit"></i> Editing: <?= htmlspecialchars(basename($editing_file)) ?></h3>
                    <div>
                        <a href="?dir=<?= urlencode($current_dir) ?>" class="btn btn-warning">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
                
                <?php if ($file_content['success']): ?>
                <form method="POST">
                    <input type="hidden" name="file_path" value="<?= htmlspecialchars($editing_file) ?>">
                    <div class="editor-container">
                        <div class="editor-toolbar">
                            <span style="color: var(--light);">
                                <i class="fas fa-file-code"></i> <?= htmlspecialchars($editing_file) ?>
                                (<?= number_format($file_content['size']) ?> bytes)
                            </span>
                            <button type="submit" name="save_file" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                        <div class="editor-content">
                            <textarea name="file_content" class="code-editor" placeholder="File content..."><?= htmlspecialchars($file_content['content']) ?></textarea>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?= htmlspecialchars($file_content['error'] ?? 'Could not read file') ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Command Terminal -->
            <div class="panel">
                <h3><i class="fas fa-terminal"></i> Command Terminal</h3>
                <form method="POST">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="command" placeholder="Enter command (e.g., ls -la /var/www)" 
                               value="<?= htmlspecialchars($_POST['command'] ?? '') ?>" style="flex: 1;">
                        <button type="submit" class="btn">
                            <i class="fas fa-play"></i> Execute
                        </button>
                    </div>
                </form>
                
                <?php if ($api_result && isset($_POST['command'])): ?>
                    <div style="margin-top: 15px;">
                        <div class="alert <?= $api_result['success'] ? 'alert-success' : 'alert-error' ?>">
                            <strong>Command:</strong> <?= htmlspecialchars($_POST['command']) ?><br>
                            <?php if ($api_result['success']): ?>
                                <pre style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 3px; margin-top: 10px; overflow-x: auto;"><?= htmlspecialchars($api_result['output'] ?? 'No output') ?></pre>
                            <?php else: ?>
                                <strong>Error:</strong> <?= htmlspecialchars($api_result['error'] ?? 'Unknown error') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- File Manager -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-folder"></i> File Manager</h3>
                    <div>
                        <button onclick="document.getElementById('createModal').style.display='block'" class="btn btn-success">
                            <i class="fas fa-plus"></i> New
                        </button>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <div style="background: #4d4d4d; padding: 10px 15px; border-radius: 5px; margin-bottom: 15px;">
                    <?php
                    $path_parts = [];
                    $temp_path = $current_dir;
                    while ($temp_path !== '/' && $temp_path !== dirname($temp_path)) {
                        $path_parts[] = ['name' => basename($temp_path) ?: '/', 'path' => $temp_path];
                        $temp_path = dirname($temp_path);
                    }
                    $path_parts[] = ['name' => 'Root', 'path' => '/'];
                    $path_parts = array_reverse($path_parts);
                    ?>
                    <?php foreach ($path_parts as $index => $part): ?>
                        <?php if ($index > 0): ?> / <?php endif; ?>
                        <?php if ($part['path'] !== $current_dir): ?>
                            <a href="?dir=<?= urlencode($part['path']) ?>" style="color: var(--secondary); text-decoration: none;">
                                <?= htmlspecialchars($part['name']) ?>
                            </a>
                        <?php else: ?>
                            <strong style="color: var(--light);"><?= htmlspecialchars($part['name']) ?></strong>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="?dir=/" class="quick-action">
                        <i class="fas fa-home"></i> Root /
                    </a>
                    <a href="?dir=/var/www" class="quick-action">
                        <i class="fas fa-globe"></i> /var/www
                    </a>
                    <a href="?dir=/home" class="quick-action">
                        <i class="fas fa-users"></i> /home
                    </a>
                    <a href="?dir=/etc" class="quick-action">
                        <i class="fas fa-cog"></i> /etc
                    </a>
                </div>

                <!-- File List -->
                <?php if ($directory_contents): ?>
                    <?php if ($directory_contents['success'] && !empty($directory_contents['items'])): ?>
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Permissions</th>
                                    <th>Owner</th>
                                    <th>Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($current_dir !== '/'): ?>
                                    <tr>
                                        <td colspan="6">
                                            <a href="?dir=<?= urlencode(dirname($current_dir)) ?>" style="color: var(--secondary); text-decoration: none;">
                                                <i class="fas fa-level-up-alt"></i> Parent Directory
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php foreach ($directory_contents['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <?= get_file_icon($item['file_type'], $item['is_dir']) ?>
                                            <?php if ($item['is_dir']): ?>
                                                <a href="?dir=<?= urlencode($item['path']) ?>" style="color: var(--secondary); text-decoration: none; font-weight: 500;">
                                                    <?= htmlspecialchars($item['name']) ?>/
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--light);">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (!$item['readable']): ?>
                                                <span style="color: var(--warning); margin-left: 5px;" title="Not readable">
                                                    <i class="fas fa-eye-slash"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['is_dir']): ?>
                                                <span style="color: #888;">DIR</span>
                                            <?php else: ?>
                                                <?= number_format($item['size']) ?> B
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($item['perms']) ?></code></td>
                                        <td><?= htmlspecialchars($item['owner']) ?></td>
                                        <td><?= htmlspecialchars($item['modified']) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <?php if (!$item['is_dir'] && $item['readable']): ?>
                                                    <a href="?dir=<?= urlencode($current_dir) ?>&edit=<?= urlencode($item['name']) ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                <?php endif; ?>
                                                <a href="?dir=<?= urlencode($current_dir) ?>&delete=<?= urlencode($item['name']) ?>" 
                                                   class="btn btn-danger" 
                                                   style="padding: 5px 10px; font-size: 12px;"
                                                   onclick="return confirm('Delete <?= htmlspecialchars($item['name']) ?>?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-folder-open"></i> 
                            <?= htmlspecialchars($directory_contents['error'] ?? 'Directory is empty or inaccessible') ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> Failed to load directory contents
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Create New Item Modal -->
        <div id="createModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #3d3d3d; padding: 25px; border-radius: 8px; width: 90%; max-width: 400px;">
                <h3 style="margin-bottom: 20px; color: var(--secondary);">
                    <i class="fas fa-plus"></i> Create New
                </h3>
                <form method="POST">
                    <input type="hidden" name="create_item" value="1">
                    <input type="text" name="item_name" placeholder="Name" required style="margin-bottom: 15px;">
                    <select name="item_type" required style="margin-bottom: 15px;">
                        <option value="file">File</option>
                        <option value="directory">Directory</option>
                    </select>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-warning" style="flex: 1;" onclick="document.getElementById('createModal').style.display='none'">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success" style="flex: 1;">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <script>
            // Close modal when clicking outside
            document.getElementById('createModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
            
            // Auto-focus command input
            document.querySelector('input[name="command"]')?.focus();
        </script>
        <?php endif; ?>
    </div>
</body>
</html>