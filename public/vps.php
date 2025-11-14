<?php
// VPS File Browser - FOR TESTING ONLY - FULL ACCESS
// WARNING: This is extremely insecure for production!

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

// REMOVED SECURITY RESTRICTIONS FOR FULL ACCESS
$home_dir = '/';

// Handle file operations
if ($authenticated) {
    // Navigate to directory
    if (isset($_GET['cd'])) {
        $new_dir = realpath($current_dir . '/' . $_GET['cd']);
        if ($new_dir && is_dir($new_dir)) {
            $current_dir = $new_dir;
            header('Location: ?dir=' . urlencode($current_dir));
            exit;
        }
    }
    
    // Go up one directory
    if (isset($_GET['up'])) {
        $parent_dir = dirname($current_dir);
        if (is_dir($parent_dir)) {
            $current_dir = $parent_dir;
            header('Location: ?dir=' . urlencode($current_dir));
            exit;
        }
    }
    
    // Read directory contents
    $files = [];
    if (is_dir($current_dir) && is_readable($current_dir)) {
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
                    'modified' => date('Y-m-d H:i:s', filemtime($full_path)),
                    'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($full_path))['name'] : 'Unknown',
                    'readable' => is_readable($full_path)
                ];
            }
        }
    }
    
    // Sort: directories first, then files
    usort($files, function($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) return -1;
        if (!$a['is_dir'] && $b['is_dir']) return 1;
        return strcmp($a['name'], $b['name']);
    });
    
    // System information
    $system_info = [
        'PHP Version' => phpversion(),
        'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'Current User' => get_current_user(),
        'System Load' => function_exists('sys_getloadavg') ? implode(', ', sys_getloadavg()) : 'N/A',
        'Memory Usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'Disk Free Space' => round(disk_free_space($current_dir) / (1024 * 1024 * 1024), 2) . ' GB',
        'Disk Total Space' => round(disk_total_space($current_dir) / (1024 * 1024 * 1024), 2) . ' GB'
    ];
    
    // Quick access directories
    $quick_dirs = [
        '/' => 'Root',
        '/home' => 'Home Directories',
        '/etc' => 'System Configuration',
        '/var' => 'Variable Data',
        '/var/www' => 'Websites',
        '/var/log' => 'Log Files',
        '/etc/nginx' => 'Nginx Config',
        '/etc/httpd' => 'Apache Config',
        '/etc/mysql' => 'MySQL Config',
        '/etc/ssl' => 'SSL Certificates',
        '/var/lib/mysql' => 'MySQL Data',
        '/root' => 'Root Home',
        '/tmp' => 'Temporary Files'
    ];
    
    // Try to get database info
    $databases = [];
    if (function_exists('shell_exec')) {
        // Try to get MySQL databases
        $mysql_output = @shell_exec('mysql -e "SHOW DATABASES;" 2>/dev/null');
        if ($mysql_output) {
            $db_lines = explode("\n", $mysql_output);
            foreach ($db_lines as $line) {
                if (trim($line) && !in_array(trim($line), ['Database', 'information_schema', 'performance_schema', 'mysql'])) {
                    $databases[] = trim($line);
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPS File Browser - Full Access</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .login-form { max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2980b9; }
        .error { background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .file-browser { margin-top: 20px; }
        .breadcrumb { background: #ecf0f1; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .file-table { width: 100%; border-collapse: collapse; }
        .file-table th, .file-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .file-table th { background: #34495e; color: white; }
        .file-table tr:hover { background: #f8f9fa; }
        .dir { color: #3498db; font-weight: bold; }
        .file { color: #2c3e50; }
        .size { text-align: right; font-family: monospace; }
        .actions { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
        .logout { float: right; background: #e74c3c; }
        .logout:hover { background: #c0392b; }
        .system-info, .quick-access, .database-info { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px; }
        .quick-link { background: #3498db; color: white; padding: 10px; border-radius: 4px; text-decoration: none; text-align: center; }
        .quick-link:hover { background: #2980b9; }
        .unreadable { color: #e74c3c; font-style: italic; }
        .database-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .db-tag { background: #27ae60; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>VPS File Browser - Full System Access</h1>
            <p>⚠️ WARNING: This provides full system access - For testing only!</p>
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
                        <button type="submit">Login</button>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- File Browser -->
                <div class="actions">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="?logout=1" class="logout">Logout</a>
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
                
                <!-- Database Information -->
                <?php if (!empty($databases)): ?>
                <div class="database-info">
                    <h3>🗃️ MySQL Databases</h3>
                    <div class="database-list">
                        <?php foreach ($databases as $db): ?>
                            <span class="db-tag"><?php echo htmlspecialchars($db); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
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
                                <th>Type</th>
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
                                        <?php if (!$file['readable']): ?>
                                            <span class="unreadable"> (no access)</span>
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
                                    <td>
                                        <?php if ($file['is_dir']): ?>
                                            <span style="color: #3498db;">Directory</span>
                                        <?php else: ?>
                                            <?php
                                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                            echo htmlspecialchars($ext ? $ext . ' file' : 'File');
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($files)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #666;">
                                        This directory is empty or not accessible
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>