#!/usr/bin/env python3
"""
Real-time Redis Monitor for NeonQueue CTF
Shows keys as they are created/modified

Usage: python redis_monitor.py [host] [port]
"""

import sys
import time
import redis
import base64
import zlib
import json
from datetime import datetime

# Default connection
HOST = sys.argv[1] if len(sys.argv) > 1 else "localhost"
PORT = int(sys.argv[2]) if len(sys.argv) > 2 else 6379

# Colors for terminal
class Colors:
    GREEN = '\033[92m'
    CYAN = '\033[96m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    MAGENTA = '\033[95m'
    RESET = '\033[0m'
    BOLD = '\033[1m'


def decode_task(data: str) -> dict:
    """Decode a serialized task payload"""
    try:
        decoded = base64.b64decode(data)
        decompressed = zlib.decompress(decoded)
        return json.loads(decompressed.decode('utf-8'))
    except:
        return None


def format_value(key: str, value: str) -> str:
    """Format value based on key type"""
    if key.startswith("otp:"):
        return f"{Colors.YELLOW}OTP: {value}{Colors.RESET}"
    elif key.startswith("task:"):
        decoded = decode_task(value)
        if decoded:
            task_type = decoded.get('data', {}).get('type', 'unknown')
            return f"{Colors.CYAN}Task Type: {task_type}{Colors.RESET}\n         {json.dumps(decoded, indent=2)[:300]}"
        return value[:100]
    return value[:100] if value else "(empty)"


def main():
    r = redis.Redis(host=HOST, port=PORT, decode_responses=True)
    
    try:
        r.ping()
    except redis.ConnectionError as e:
        print(f"{Colors.RED}[-] Failed to connect to {HOST}:{PORT}: {e}{Colors.RESET}")
        sys.exit(1)
    
    print(f"""
{Colors.GREEN}╔══════════════════════════════════════════════════════════════╗
║           NEON QUEUE - REAL-TIME REDIS MONITOR               ║
╠══════════════════════════════════════════════════════════════╣
║  Host: {HOST:15}  Port: {PORT:<5}                         ║
║  Press Ctrl+C to exit                                        ║
╚══════════════════════════════════════════════════════════════╝{Colors.RESET}
""")
    
    # Track known keys
    known_keys = {}
    
    # Initial scan
    for key in r.keys("*"):
        key_type = r.type(key)
        if key_type == "string":
            known_keys[key] = r.get(key)
        elif key_type == "list":
            known_keys[key] = r.lrange(key, 0, -1)
    
    print(f"{Colors.CYAN}[*] Monitoring {len(known_keys)} existing keys...{Colors.RESET}\n")
    
    try:
        while True:
            current_keys = r.keys("*")
            
            # Check for new keys
            for key in current_keys:
                key_type = r.type(key)
                
                if key_type == "string":
                    value = r.get(key)
                    
                    if key not in known_keys:
                        # New key!
                        timestamp = datetime.now().strftime("%H:%M:%S")
                        print(f"{Colors.GREEN}[{timestamp}] NEW KEY: {Colors.BOLD}{key}{Colors.RESET}")
                        print(f"         {format_value(key, value)}\n")
                        known_keys[key] = value
                    
                    elif known_keys[key] != value:
                        # Modified key!
                        timestamp = datetime.now().strftime("%H:%M:%S")
                        print(f"{Colors.MAGENTA}[{timestamp}] MODIFIED: {Colors.BOLD}{key}{Colors.RESET}")
                        print(f"         OLD: {known_keys[key][:50] if known_keys[key] else '(none)'}...")
                        print(f"         NEW: {format_value(key, value)}\n")
                        known_keys[key] = value
                
                elif key_type == "list":
                    items = r.lrange(key, 0, -1)
                    
                    if key not in known_keys:
                        timestamp = datetime.now().strftime("%H:%M:%S")
                        print(f"{Colors.GREEN}[{timestamp}] NEW QUEUE: {Colors.BOLD}{key}{Colors.RESET}")
                        print(f"         Items: {items}\n")
                        known_keys[key] = items
                    
                    elif known_keys[key] != items:
                        timestamp = datetime.now().strftime("%H:%M:%S")
                        old_len = len(known_keys[key]) if known_keys[key] else 0
                        new_len = len(items)
                        
                        if new_len > old_len:
                            new_items = [i for i in items if i not in known_keys[key]]
                            print(f"{Colors.CYAN}[{timestamp}] QUEUE PUSH: {Colors.BOLD}{key}{Colors.RESET}")
                            print(f"         Added: {new_items}\n")
                        else:
                            print(f"{Colors.YELLOW}[{timestamp}] QUEUE POP: {Colors.BOLD}{key}{Colors.RESET}")
                            print(f"         Remaining: {len(items)} items\n")
                        
                        known_keys[key] = items
            
            # Check for deleted keys
            for key in list(known_keys.keys()):
                if key not in current_keys:
                    timestamp = datetime.now().strftime("%H:%M:%S")
                    print(f"{Colors.RED}[{timestamp}] DELETED: {Colors.BOLD}{key}{Colors.RESET}\n")
                    del known_keys[key]
            
            time.sleep(0.5)  # Poll every 500ms
    
    except KeyboardInterrupt:
        print(f"\n{Colors.YELLOW}[*] Monitoring stopped.{Colors.RESET}")


if __name__ == "__main__":
    main()
