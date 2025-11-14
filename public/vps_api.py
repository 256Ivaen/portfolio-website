#!/usr/bin/env python3
"""
Professional VPS Control Panel - Python Backend
Enterprise-grade with Database Management & phpMyAdmin Integration
"""

import os
import sys
import json
import subprocess
import pwd
import grp
import shutil
import MySQLdb
import psutil
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS
import urllib.parse

app = Flask(__name__)
CORS(app)

# Enhanced authentication
VALID_CREDENTIALS = {
    'admin': 'Iv@n0772717963'
}

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'passwd': '',  # You'll need to set this
    'charset': 'utf8mb4'
}

# Fail-safe configuration
MAX_FILE_SIZE = 10 * 1024 * 1024  # 10MB
ALLOWED_COMMANDS = ['ls', 'cd', 'cat', 'find', 'grep', 'ps', 'df', 'du', 'tail', 'head']
RESTRICTED_PATHS = ['/proc', '/sys', '/dev']

def authenticate(username, password):
    return VALID_CREDENTIALS.get(username) == password

def is_safe_path(path):
    """Prevent path traversal attacks"""
    try:
        absolute_path = os.path.abspath(path)
        for restricted in RESTRICTED_PATHS:
            if absolute_path.startswith(restricted):
                return False
        return True
    except:
        return False

def run_command_safe(cmd):
    """Safe command execution with timeout and limits"""
    try:
        if any(blocked in cmd for blocked in ['rm -rf /', 'dd if=', 'mkfs', ':(){:|:&};:']):
            return {'success': False, 'error': 'Dangerous command blocked'}
        
        result = subprocess.run(
            cmd, 
            shell=True, 
            capture_output=True, 
            text=True, 
            timeout=30,
            cwd='/'
        )
        return {
            'success': True,
            'output': result.stdout + result.stderr,
            'return_code': result.returncode
        }
    except subprocess.TimeoutExpired:
        return {'success': False, 'error': 'Command timed out'}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def get_mysql_connection():
    """Get MySQL connection with error handling"""
    try:
        # Try to get MySQL root password from common locations
        password = None
        
        # Try to read from /etc/my.cnf or /etc/mysql/my.cnf
        for config_file in ['/etc/my.cnf', '/etc/mysql/my.cnf', '/etc/mysql/mysql.conf.d/mysqld.cnf']:
            if os.path.exists(config_file):
                with open(config_file, 'r') as f:
                    content = f.read()
                    if 'password' in content:
                        # Extract password from config (simplified)
                        for line in content.split('\n'):
                            if 'password' in line and '=' in line:
                                password = line.split('=')[1].strip()
                                break
        
        config = DB_CONFIG.copy()
        if password:
            config['passwd'] = password
            
        connection = MySQLdb.connect(**config)
        return connection
    except Exception as e:
        return None

def list_databases():
    """List all MySQL databases"""
    try:
        connection = get_mysql_connection()
        if not connection:
            return {'success': False, 'error': 'Could not connect to MySQL'}
        
        cursor = connection.cursor()
        cursor.execute("SHOW DATABASES")
        databases = [row[0] for row in cursor.fetchall() if row[0] not in ['information_schema', 'performance_schema', 'mysql', 'sys']]
        
        cursor.close()
        connection.close()
        
        return {'success': True, 'databases': databases}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def get_database_tables(database_name):
    """Get tables for a specific database"""
    try:
        connection = get_mysql_connection()
        if not connection:
            return {'success': False, 'error': 'Could not connect to MySQL'}
        
        cursor = connection.cursor()
        cursor.execute(f"USE `{database_name}`")
        cursor.execute("SHOW TABLES")
        tables = [row[0] for row in cursor.fetchall()]
        
        # Get table info
        table_info = []
        for table in tables:
            cursor.execute(f"SHOW TABLE STATUS LIKE '{table}'")
            status = cursor.fetchone()
            cursor.execute(f"SELECT COUNT(*) FROM `{table}`")
            row_count = cursor.fetchone()[0]
            
            table_info.append({
                'name': table,
                'rows': row_count,
                'engine': status[1] if status else 'Unknown',
                'size': status[6] if status else 0
            })
        
        cursor.close()
        connection.close()
        
        return {'success': True, 'tables': table_info, 'database': database_name}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def get_table_data(database_name, table_name, limit=100):
    """Get data from a specific table"""
    try:
        connection = get_mysql_connection()
        if not connection:
            return {'success': False, 'error': 'Could not connect to MySQL'}
        
        cursor = connection.cursor()
        cursor.execute(f"USE `{database_name}`")
        
        # Get column names
        cursor.execute(f"DESCRIBE `{table_name}`")
        columns = [row[0] for row in cursor.fetchall()]
        
        # Get data
        cursor.execute(f"SELECT * FROM `{table_name}` LIMIT {limit}")
        rows = cursor.fetchall()
        
        # Convert to list of dictionaries
        data = []
        for row in rows:
            data.append(dict(zip(columns, row)))
        
        cursor.close()
        connection.close()
        
        return {
            'success': True, 
            'columns': columns, 
            'data': data, 
            'database': database_name, 
            'table': table_name,
            'total_rows': len(rows)
        }
    except Exception as e:
        return {'success': False, 'error': str(e)}

def create_database(database_name):
    """Create a new database"""
    try:
        connection = get_mysql_connection()
        if not connection:
            return {'success': False, 'error': 'Could not connect to MySQL'}
        
        cursor = connection.cursor()
        cursor.execute(f"CREATE DATABASE `{database_name}`")
        connection.commit()
        
        cursor.close()
        connection.close()
        
        return {'success': True, 'message': f'Database {database_name} created successfully'}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def delete_database(database_name):
    """Delete a database"""
    try:
        connection = get_mysql_connection()
        if not connection:
            return {'success': False, 'error': 'Could not connect to MySQL'}
        
        cursor = connection.cursor()
        cursor.execute(f"DROP DATABASE `{database_name}`")
        connection.commit()
        
        cursor.close()
        connection.close()
        
        return {'success': True, 'message': f'Database {database_name} deleted successfully'}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def get_phpmyadmin_url():
    """Get or create phpMyAdmin access URL"""
    try:
        # Check if phpMyAdmin is installed
        possible_paths = [
            '/usr/share/phpmyadmin',
            '/var/www/phpmyadmin',
            '/usr/share/nginx/phpmyadmin',
            '/var/www/html/phpmyadmin'
        ]
        
        phpmyadmin_path = None
        for path in possible_paths:
            if os.path.exists(path):
                phpmyadmin_path = path
                break
        
        if phpmyadmin_path:
            # Create a symbolic link if needed
            web_root = '/var/www/html'
            if os.path.exists(web_root):
                link_path = os.path.join(web_root, 'phpmyadmin')
                if not os.path.exists(link_path):
                    os.symlink(phpmyadmin_path, link_path)
                return '/phpmyadmin'
        
        return None
    except Exception as e:
        return None

def list_directory_enhanced(path):
    """Enhanced directory listing with file types"""
    if not is_safe_path(path):
        return {'success': False, 'error': 'Access to this path is restricted'}
    
    try:
        if not os.path.exists(path):
            return {'success': False, 'error': 'Directory does not exist'}
        
        items = []
        for item in os.listdir(path):
            full_path = os.path.join(path, item)
            try:
                stat = os.stat(full_path)
                is_dir = os.path.isdir(full_path)
                
                # Determine file type
                file_type = 'directory' if is_dir else 'file'
                if not is_dir:
                    ext = os.path.splitext(item)[1].lower()
                    if ext in ['.html', '.htm']:
                        file_type = 'html'
                    elif ext in ['.php', '.py', '.js', '.css']:
                        file_type = 'code'
                    elif ext in ['.jpg', '.png', '.gif', '.svg']:
                        file_type = 'image'
                    elif ext in ['.log', '.txt']:
                        file_type = 'text'
                
                items.append({
                    'name': item,
                    'path': full_path,
                    'is_dir': is_dir,
                    'file_type': file_type,
                    'size': stat.st_size,
                    'perms': oct(stat.st_mode)[-3:],
                    'owner': pwd.getpwuid(stat.st_uid).pw_name,
                    'group': grp.getgrgid(stat.st_gid).gr_name,
                    'modified': datetime.fromtimestamp(stat.st_mtime).strftime('%Y-%m-%d %H:%M:%S'),
                    'readable': os.access(full_path, os.R_OK),
                    'writable': os.access(full_path, os.W_OK)
                })
            except Exception as e:
                items.append({
                    'name': item,
                    'path': full_path,
                    'is_dir': False,
                    'file_type': 'unknown',
                    'size': 0,
                    'perms': '???',
                    'owner': 'Unknown',
                    'group': 'Unknown',
                    'modified': 'Unknown',
                    'readable': False,
                    'writable': False,
                    'error': str(e)
                })
        
        return {'success': True, 'items': items, 'path': path}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def read_file_content(path):
    """Read file content for editing"""
    if not is_safe_path(path):
        return {'success': False, 'error': 'Access to this path is restricted'}
    
    try:
        if not os.path.isfile(path):
            return {'success': False, 'error': 'Not a file'}
        
        file_size = os.path.getsize(path)
        if file_size > MAX_FILE_SIZE:
            return {'success': False, 'error': f'File too large ({file_size} bytes)'}
        
        with open(path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        return {'success': True, 'content': content, 'size': file_size}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def write_file_content(path, content):
    """Write file content with backup"""
    if not is_safe_path(path):
        return {'success': False, 'error': 'Access to this path is restricted'}
    
    try:
        # Create backup
        if os.path.exists(path):
            backup_path = path + '.backup'
            shutil.copy2(path, backup_path)
        
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        
        return {'success': True, 'message': 'File saved successfully'}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def get_system_health():
    """Comprehensive system health check"""
    try:
        health = {}
        
        # System load
        health['load'] = run_command_safe('uptime')['output'].strip()
        
        # Disk usage
        health['disk'] = run_command_safe('df -h')['output'].strip()
        
        # Memory usage
        health['memory'] = run_command_safe('free -h')['output'].strip()
        
        # Services status
        health['services'] = {}
        services = ['httpd', 'nginx', 'mysql', 'mariadb', 'postgresql']
        for service in services:
            result = run_command_safe(f'systemctl is-active {service} 2>/dev/null || echo "not-found"')
            health['services'][service] = result['output'].strip()
        
        # Website detection
        health['websites'] = []
        web_dirs = ['/var/www', '/home', '/var/www/html']
        for web_dir in web_dirs:
            if os.path.exists(web_dir):
                result = run_command_safe(f"find {web_dir} -maxdepth 2 -type d -name 'public_html' -o -name 'www' -o -name 'html' 2>/dev/null")
                if result['success'] and result['output']:
                    health['websites'].extend(result['output'].strip().split('\n'))
        
        # Database status
        db_status = list_databases()
        health['database_status'] = {
            'connected': db_status['success'],
            'database_count': len(db_status.get('databases', [])) if db_status['success'] else 0
        }
        
        # phpMyAdmin status
        health['phpmyadmin'] = get_phpmyadmin_url() is not None
        
        return {'success': True, 'health': health}
    except Exception as e:
        return {'success': False, 'error': str(e)}

# API Routes
@app.route('/api/login', methods=['POST'])
def api_login():
    data = request.get_json()
    if authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': True, 'message': 'Login successful'})
    return jsonify({'success': False, 'error': 'Invalid credentials'})

@app.route('/api/command', methods=['POST'])
def api_command():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    result = run_command_safe(data.get('command', ''))
    return jsonify(result)

@app.route('/api/ls', methods=['POST'])
def api_list_directory():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '/')
    result = list_directory_enhanced(path)
    return jsonify(result)

@app.route('/api/read', methods=['POST'])
def api_read_file():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '')
    result = read_file_content(path)
    return jsonify(result)

@app.route('/api/write', methods=['POST'])
def api_write_file():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '')
    content = data.get('content', '')
    result = write_file_content(path, content)
    return jsonify(result)

@app.route('/api/health', methods=['POST'])
def api_system_health():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    result = get_system_health()
    return jsonify(result)

@app.route('/api/delete', methods=['POST'])
def api_delete():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '')
    if not is_safe_path(path):
        return jsonify({'success': False, 'error': 'Access restricted'})
    
    try:
        if os.path.isdir(path):
            shutil.rmtree(path)
        else:
            os.remove(path)
        return jsonify({'success': True, 'message': 'Deleted successfully'})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

@app.route('/api/create', methods=['POST'])
def api_create():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '')
    is_dir = data.get('is_dir', False)
    
    if not is_safe_path(path):
        return jsonify({'success': False, 'error': 'Access restricted'})
    
    try:
        if is_dir:
            os.makedirs(path, exist_ok=True)
        else:
            with open(path, 'w') as f:
                f.write('')
        return jsonify({'success': True, 'message': 'Created successfully'})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

# Database Management Routes
@app.route('/api/databases/list', methods=['POST'])
def api_list_databases():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    result = list_databases()
    return jsonify(result)

@app.route('/api/databases/tables', methods=['POST'])
def api_get_database_tables():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    database_name = data.get('database', '')
    result = get_database_tables(database_name)
    return jsonify(result)

@app.route('/api/databases/table-data', methods=['POST'])
def api_get_table_data():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    database_name = data.get('database', '')
    table_name = data.get('table', '')
    limit = data.get('limit', 100)
    
    result = get_table_data(database_name, table_name, limit)
    return jsonify(result)

@app.route('/api/databases/create', methods=['POST'])
def api_create_database():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    database_name = data.get('database_name', '')
    result = create_database(database_name)
    return jsonify(result)

@app.route('/api/databases/delete', methods=['POST'])
def api_delete_database():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    database_name = data.get('database_name', '')
    result = delete_database(database_name)
    return jsonify(result)

@app.route('/api/phpmyadmin/url', methods=['POST'])
def api_get_phpmyadmin_url():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    phpmyadmin_url = get_phpmyadmin_url()
    if phpmyadmin_url:
        return jsonify({'success': True, 'url': phpmyadmin_url})
    else:
        return jsonify({'success': False, 'error': 'phpMyAdmin not found'})

@app.route('/api/phpmyadmin/install', methods=['POST'])
def api_install_phpmyadmin():
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    try:
        # Install phpMyAdmin
        result = run_command_safe('yum install phpmyadmin -y || apt install phpmyadmin -y')
        if result['success']:
            # Ensure it's accessible via web
            phpmyadmin_url = get_phpmyadmin_url()
            return jsonify({'success': True, 'url': phpmyadmin_url, 'message': 'phpMyAdmin installed successfully'})
        else:
            return jsonify({'success': False, 'error': 'Failed to install phpMyAdmin'})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=False)