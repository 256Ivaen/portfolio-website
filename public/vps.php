<?php
// Simple VPS File Browser - FOR TESTING ONLY
// WARNING: This is not secure for production use!

session_start();

// Simple authentication - CHANGE THESE CREDENTIALS!
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
$current_dir = $_GET['dir'] ?? getcwd();
$current_dir = realpath($current_dir) ?: getcwd();

// Security: Prevent directory traversal outside home directory
$home_dir = realpath($_SERVER['HOME'] ?? getcwd());
if (strpos($current_dir, $home_dir) !== 0) {
    $current_dir = $home_dir;
}

// Handle file operations
if ($authenticated) {
    // Navigate to directory
    if (isset($_GET['cd'])) {
        $new_dir = realpath($current_dir . '/' . $_GET['cd']);
        if ($new_dir && strpos($new_dir, $home_dir) === 0 && is_dir($new_dir)) {
            $current_dir = $new_dir;
            header('Location: ?dir=' . urlencode($current_dir));
            exit;
        }
    }
    
    // Go up one directory
    if (isset($_GET['up'])) {
        $parent_dir = dirname($current_dir);
        if (strpos($parent_dir, $home_dir) === 0) {
            $current_dir = $parent_dir;
            header('Location: ?dir=' . urlencode($current_dir));
            exit;
        }
    }
    
    // Read directory contents
    $files = [];
    if (is_dir($current_dir) && is_readable($current_dir)) {
        $items = scandir($current_dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $full_path = $current_dir . '/' . $item;
            $files[] = [
                'name' => $item,
                'path' => $full_path,
                'is_dir' => is_dir($full_path),
                'size' => is_file($full_path) ? filesize($full_path) : 0,
                'perms' => substr(sprintf('%o', fileperms($full_path)), -4),
                'modified' => date('Y-m-d H:i:s', filemtime($full_path))
            ];
        }
    }
    
    // Sort: directories first, then files
    usort($files, function($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) return -1;
        if (!$a['is_dir'] && $b['is_dir']) return 1;
        return strcmp($a['name'], $b['name']);
    });
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPS File Browser</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .login-form { max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2980b9; }
        .error { background: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #27ae60; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
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
        .system-info { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>VPS File Browser</h1>
            <p>Simple file management interface</p>
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
                            <input type="password" id="password" name="password" value="password123" required>
                        </div>
                        <button type="submit">Login</button>
                    </form>
                    <p style="margin-top: 15px; font-size: 12px; color: #666;">
                        Default credentials: admin / password123<br>
                        <strong>Change these in the PHP code!</strong>
                    </p>
                </div>
                
            <?php else: ?>
                <!-- File Browser -->
                <div class="actions">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="?logout=1" class="logout">Logout</a>
                </div>
                
                <!-- System Information -->
                <div class="system-info">
                    <h3>System Information</h3>
                    <div class="info-grid">
                        <div><strong>Server:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></div>
                        <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                        <div><strong>Current Directory:</strong> <?php echo htmlspecialchars($current_dir); ?></div>
                        <div><strong>Free Space:</strong> <?php echo round(disk_free_space($current_dir) / (1024 * 1024 * 1024), 2); ?> GB free</div>
                    </div>
                </div>
                
                <!-- Breadcrumb Navigation -->
                <div class="breadcrumb">
                    <?php
                    $path_parts = [];
                    $temp_path = $current_dir;
                    while ($temp_path !== $home_dir && $temp_path !== dirname($temp_path)) {
                        $path_parts[] = ['name' => basename($temp_path), 'path' => $temp_path];
                        $temp_path = dirname($temp_path);
                    }
                    $path_parts[] = ['name' => 'Home', 'path' => $home_dir];
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
                                <th>Modified</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($current_dir !== $home_dir): ?>
                                <tr>
                                    <td colspan="5">
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
                                    <td colspan="5" style="text-align: center; color: #666;">
                                        This directory is empty
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