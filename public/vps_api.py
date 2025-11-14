#!/usr/bin/env python3
"""
VPS Control API - Python Backend
Provides full system access through a secure API
"""

import os
import sys
import json
import subprocess
import pwd
import grp
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP requests

# Authentication - CHANGE THESE!
VALID_USERNAME = 'admin'
VALID_PASSWORD = 'Iv@n0772717963'

def authenticate(username, password):
    """Simple authentication"""
    return username == VALID_USERNAME and password == VALID_PASSWORD

def run_command(cmd):
    """Execute system command safely"""
    try:
        result = subprocess.run(
            cmd, 
            shell=True, 
            capture_output=True, 
            text=True, 
            timeout=30
        )
        return {
            'success': True,
            'output': result.stdout + result.stderr,
            'return_code': result.returncode
        }
    except Exception as e:
        return {'success': False, 'error': str(e)}

def list_directory(path):
    """List directory contents with full details"""
    try:
        if not os.path.exists(path):
            return {'success': False, 'error': 'Directory does not exist'}
        
        items = []
        for item in os.listdir(path):
            full_path = os.path.join(path, item)
            try:
                stat = os.stat(full_path)
                items.append({
                    'name': item,
                    'path': full_path,
                    'is_dir': os.path.isdir(full_path),
                    'size': stat.st_size,
                    'perms': oct(stat.st_mode)[-3:],
                    'owner': pwd.getpwuid(stat.st_uid).pw_name,
                    'group': grp.getgrgid(stat.st_gid).gr_name,
                    'modified': datetime.fromtimestamp(stat.st_mtime).isoformat(),
                    'readable': os.access(full_path, os.R_OK)
                })
            except Exception as e:
                items.append({
                    'name': item,
                    'path': full_path,
                    'is_dir': False,
                    'size': 0,
                    'perms': '???',
                    'owner': 'Unknown',
                    'group': 'Unknown',
                    'modified': 'Unknown',
                    'readable': False,
                    'error': str(e)
                })
        
        return {'success': True, 'items': items}
    except Exception as e:
        return {'success': False, 'error': str(e)}

def get_system_info():
    """Get comprehensive system information"""
    try:
        info = {}
        
        # Basic system info
        info['hostname'] = run_command('hostname')['output'].strip()
        info['uptime'] = run_command('uptime')['output'].strip()
        info['current_user'] = run_command('whoami')['output'].strip()
        
        # Disk usage
        info['disk_usage'] = run_command('df -h')['output'].strip()
        
        # Memory info
        info['memory'] = run_command('free -h')['output'].strip()
        
        # Find websites
        info['websites'] = {}
        web_dirs = ['/var/www', '/home', '/var/www/html', '/usr/share/nginx/html']
        for web_dir in web_dirs:
            if os.path.exists(web_dir):
                result = run_command(f"find {web_dir} -maxdepth 2 -type d -name 'public_html' -o -name 'www' -o -name 'html' 2>/dev/null")
                if result['success']:
                    info['websites'][web_dir] = result['output'].strip().split('\n')
        
        return {'success': True, 'info': info}
    except Exception as e:
        return {'success': False, 'error': str(e)}

# API Routes
@app.route('/api/login', methods=['POST'])
def api_login():
    """Login endpoint"""
    data = request.get_json()
    if authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': True, 'message': 'Login successful'})
    return jsonify({'success': False, 'error': 'Invalid credentials'})

@app.route('/api/command', methods=['POST'])
def api_command():
    """Execute system command"""
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    result = run_command(data.get('command', ''))
    return jsonify(result)

@app.route('/api/ls', methods=['POST'])
def api_list_directory():
    """List directory contents"""
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '/')
    result = list_directory(path)
    return jsonify(result)

@app.route('/api/system-info', methods=['POST'])
def api_system_info():
    """Get system information"""
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    result = get_system_info()
    return jsonify(result)

@app.route('/api/upload', methods=['POST'])
def api_upload():
    """Handle file upload"""
    # This would need multipart form handling
    return jsonify({'success': False, 'error': 'Not implemented in this example'})

@app.route('/api/delete', methods=['POST'])
def api_delete():
    """Delete file or directory"""
    data = request.get_json()
    if not authenticate(data.get('username'), data.get('password')):
        return jsonify({'success': False, 'error': 'Authentication required'})
    
    path = data.get('path', '')
    if not path:
        return jsonify({'success': False, 'error': 'No path specified'})
    
    if os.path.isdir(path):
        result = run_command(f"rm -rf '{path}'")
    else:
        result = run_command(f"rm -f '{path}'")
    
    return jsonify(result)

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=False)