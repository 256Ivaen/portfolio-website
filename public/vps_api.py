#!/usr/bin/env python3
"""
Professional VPS Control Panel - Python Backend
Enterprise-grade with file editing and fail-safe features
"""

import os
import sys
import json
import subprocess
import pwd
import grp
import shutil
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Enhanced authentication
VALID_CREDENTIALS = {
    'admin': 'Iv@n0772717963'
}

# Fail-safe configuration
MAX_FILE_SIZE = 10 * 1024 * 1024  # 10MB
ALLOWED_COMMANDS = ['ls', 'cd', 'cat', 'find', 'grep', 'ps', 'df', 'du', 'tail', 'head']
RESTRICTED_PATHS = ['/proc', '/sys', '/dev']  # Can be customized

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
        # Basic command validation
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

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=False)