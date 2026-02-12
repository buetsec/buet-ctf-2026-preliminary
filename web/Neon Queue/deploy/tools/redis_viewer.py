#!/usr/bin/env python3
"""
Redis Key Viewer for NeonQueue CTF
Usage: python redis_viewer.py [host] [port]
"""

import sys
import redis
import base64
import zlib
import json

# Default connection
HOST = sys.argv[1] if len(sys.argv) > 1 else "localhost"
PORT = int(sys.argv[2]) if len(sys.argv) > 2 else 6379


def connect():
    """Connect to Redis"""
    r = redis.Redis(host=HOST, port=PORT, decode_responses=True)
    try:
        r.ping()
        print(f"[+] Connected to Redis at {HOST}:{PORT}")
        return r
    except redis.ConnectionError as e:
        print(f"[-] Failed to connect: {e}")
        sys.exit(1)


def decode_task(data: str) -> dict:
    """Decode a serialized task payload"""
    try:
        decoded = base64.b64decode(data)
        decompressed = zlib.decompress(decoded)
        return json.loads(decompressed.decode('utf-8'))
    except Exception as e:
        return {"error": str(e), "raw": data[:100] + "..."}


def main():
    r = connect()
    
    print("\n" + "=" * 60)
    print(" REDIS KEY VIEWER")
    print("=" * 60)
    
    # Get all keys
    keys = r.keys("*")
    print(f"\n[*] Found {len(keys)} keys:\n")
    
    if not keys:
        print("    (no keys found)")
        return
    
    # Categorize keys
    otp_keys = [k for k in keys if k.startswith("otp:")]
    task_keys = [k for k in keys if k.startswith("task:")]
    queue_keys = [k for k in keys if k.startswith("queue:")]
    other_keys = [k for k in keys if not any(k.startswith(p) for p in ["otp:", "task:", "queue:"])]
    
    # Display OTP keys
    if otp_keys:
        print("\n[OTP KEYS] ========================================")
        for key in otp_keys:
            value = r.get(key)
            user_id = key.replace("otp:", "")
            print(f"  {key}")
            print(f"    └─ OTP: {value}")
            print(f"    └─ User ID: {user_id}")
    
    # Display task keys
    if task_keys:
        print("\n[TASK KEYS] =======================================")
        for key in task_keys:
            value = r.get(key)
            print(f"  {key}")
            if value:
                decoded = decode_task(value)
                print(f"    └─ Decoded: {json.dumps(decoded, indent=6)[:500]}")
    
    # Display queue keys
    if queue_keys:
        print("\n[QUEUE KEYS] ======================================")
        for key in queue_keys:
            key_type = r.type(key)
            if key_type == "list":
                length = r.llen(key)
                items = r.lrange(key, 0, 10)
                print(f"  {key} (list, {length} items)")
                for item in items:
                    print(f"    └─ {item}")
            else:
                print(f"  {key} ({key_type})")
    
    # Display other keys
    if other_keys:
        print("\n[OTHER KEYS] ======================================")
        for key in other_keys:
            key_type = r.type(key)
            print(f"  {key} ({key_type})")
    
    print("\n" + "=" * 60)
    print(" COMMANDS:")
    print("   redis-cli -h {HOST} -p {PORT}")
    print("   > KEYS *")
    print("   > GET otp:<user_id>")
    print("   > GET task:<task_id>")
    print("=" * 60 + "\n")


if __name__ == "__main__":
    main()
