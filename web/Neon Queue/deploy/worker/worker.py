"""
NeonQueue Worker - Task Processing Engine (Async Version)

Processes tasks from the Redis queue and executes them concurrently.
"""
import os
import sys
import time
import json
import base64
import zlib
import random
import traceback
import asyncio
from datetime import datetime
from typing import Any, Dict, Optional

import redis.asyncio as redis
import jsonpickle
from sqlalchemy import create_engine, text
from sqlalchemy.orm import sessionmaker

# Import tasks module so jsonpickle can find the classes during deserialization
import tasks

# Database setup
DATABASE_URL = os.getenv("DATABASE_URL", "sqlite:////app/data/neonqueue.db")
engine = create_engine(
    DATABASE_URL,
    connect_args={"check_same_thread": False} if "sqlite" in DATABASE_URL else {}
)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

# Redis setup
REDIS_HOST = os.getenv("REDIS_HOST", "localhost")
REDIS_PORT = int(os.getenv("REDIS_PORT", 6379))

# Task status constants
STATUS_PENDING = "pending"
STATUS_PROCESSING = "processing"
STATUS_COMPLETED = "completed"
STATUS_FAILED = "failed"


# ========== DESERIALIZATION ==========

def deserialize_task(serialized: str) -> Any:
    """Deserialize task data from Redis."""
    try:
        # Decode and decompress
        decoded = base64.b64decode(serialized)
        decompressed = zlib.decompress(decoded)
        json_str = decompressed.decode('utf-8')
        
        task_data = jsonpickle.decode(json_str)
        
        return task_data
    except Exception as e:
        print(f"[ERROR] Deserialization failed: {e}")
        raise


# ========== TASK HANDLERS ==========

async def get_flag() -> str:
    """Read the flag from file."""
    await asyncio.sleep(random.uniform(0.2, 0.5))
    try:
        with open('/flag.txt', 'r') as f:
            return f.read().strip()
    except FileNotFoundError:
        return "ERROR: flag file missing"
    except Exception as e:
        return f"Error reading flag: {e}"


async def handle_send_email(params: dict) -> str:
    """Simulate sending an email"""
    await asyncio.sleep(random.uniform(0.5, 1.0))
    to = params.get('to', 'unknown@example.com') if isinstance(params, dict) else 'unknown@example.com'
    subject = params.get('subject', 'No subject') if isinstance(params, dict) else 'No subject'
    return f"Email queued successfully to {to}: {subject}"


async def handle_get_news() -> str:
    """Return fake news headlines"""
    await asyncio.sleep(random.uniform(0.3, 0.7))
    headlines = [
        "◈ CYBERTECH CORP UNVEILS NEW QUANTUM ENCRYPTION STANDARD",
        "◈ GLOBAL NETWORK LATENCY REDUCED BY 47% WITH NEW ROUTING PROTOCOL", 
        "◈ AI-POWERED CODE REVIEW TOOLS NOW MANDATORY FOR ENTERPRISE DEPLOYMENTS",
        "◈ ZERO-DAY VULNERABILITY IN POPULAR FRAMEWORK PATCHED WITHIN HOURS",
        "◈ DECENTRALIZED IDENTITY SYSTEMS GAIN MAINSTREAM ADOPTION",
    ]
    return "\n".join(headlines)


async def handle_system_status() -> str:
    """Return fake system status"""
    await asyncio.sleep(random.uniform(0.3, 0.7))
    return """SYSTEM STATUS REPORT
══════════════════════════════════
CPU:     ████████░░ 78.3%
MEMORY:  ██████░░░░ 62.1%  
DISK:    ███░░░░░░░ 34.7%
NETWORK: ████████░░ 81.2%
══════════════════════════════════
NODES ONLINE: 7/8
QUEUE DEPTH:  23 tasks
UPTIME:       47d 13h 22m
══════════════════════════════════"""


async def execute_task(task_data: Any) -> str:
    """Execute a task based on its type and permissions."""
    try:
        # Get task type
        task_type = getattr(task_data, 'type', None)
        if task_type is None and hasattr(task_data, 'get'):
            task_type = task_data.get('type', 'unknown')
        if task_type is None:
            task_type = 'unknown'
        
        print(f"[INFO] Executing task type: {task_type}")
        
        # Check if privileged task
        if task_type == "get_flag":
            print("[INFO] Privileged task requested, checking permissions...")
            
            # Check 1: Get meta object and call is_admin() METHOD
            meta = getattr(task_data, 'meta', None)
            if meta is None:
                return "ACCESS DENIED: No meta object found"
            
            try:
                is_admin = meta.is_admin()
            except (AttributeError, TypeError):
                return "ACCESS DENIED: meta.is_admin() method failed"
            
            if not is_admin:
                return "ACCESS DENIED: meta.is_admin() returned False"
            
            print("[INFO] ✓ Admin check passed")
            
            # Check 2: Get approval object and call is_valid() METHOD
            approval = getattr(task_data, 'approval', None)
            if approval is None:
                return "ACCESS DENIED: No approval object found"
            
            try:
                is_valid = approval.is_valid()
            except (AttributeError, TypeError):
                return "ACCESS DENIED: approval.is_valid() method failed"
            
            if not is_valid:
                return "ACCESS DENIED: approval.is_valid() returned False"
            
            print("[INFO] ✓ Approval check passed")
            print("[INFO] All checks passed! Retrieving flag...")
            
            return await get_flag()
        
        # Handle regular tasks
        elif task_type == "send_email":
            params = getattr(task_data, 'params', {})
            return await handle_send_email(params)
        
        elif task_type == "get_news":
            return await handle_get_news()
        
        elif task_type == "system_status":
            return await handle_system_status()
        
        else:
            return f"Unknown task type: {task_type}"
    
    except Exception as e:
        print(f"[ERROR] Task execution failed: {e}")
        traceback.print_exc()
        return f"Task execution error: {str(e)}"


# ========== DATABASE OPERATIONS ==========

def update_task_status(task_id: str, status: str, output: str = None):
    """Update task status in SQLite database"""
    db = SessionLocal()
    try:
        if output:
            db.execute(
                text("UPDATE tasks SET status = :status, output = :output, completed_at = :completed_at WHERE id = :id"),
                {"status": status, "output": output, "completed_at": datetime.utcnow(), "id": task_id}
            )
        else:
            db.execute(
                text("UPDATE tasks SET status = :status WHERE id = :id"),
                {"status": status, "id": task_id}
            )
        
        db.commit()
    except Exception as e:
        print(f"[ERROR] Failed to update task {task_id}: {e}")
        db.rollback()
    finally:
        db.close()


# ========== QUEUE CLEANER (DISABLED) ==========

def clear_queue():
    """Clear all pending tasks from the queue"""
    try:
        print("\n[CLEANER] Starting queue cleanup...")
        
        # Create sync redis client for cleaner
        sync_redis = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, decode_responses=True)
        
        # Get all task IDs from the queue
        task_ids = sync_redis.lrange("queue:tasks", 0, -1)
        count = len(task_ids)
        
        if count == 0:
            print("[CLEANER] Queue is empty, nothing to clear")
            return
        
        # Delete the entire queue
        sync_redis.delete("queue:tasks")
        print(f"[CLEANER] Cleared {count} tasks from queue:tasks")
        
        # Delete all task data from Redis
        deleted_tasks = 0
        for task_id in task_ids:
            if sync_redis.delete(f"task:{task_id}"):
                deleted_tasks += 1
        
        print(f"[CLEANER] Deleted {deleted_tasks} task data entries from Redis")
        
        # Update database to mark pending tasks as failed
        db = SessionLocal()
        try:
            for task_id in task_ids:
                db.execute(
                    text("UPDATE tasks SET status = :status, output = :output WHERE id = :id AND status = :pending_status"),
                    {
                        "status": STATUS_FAILED,
                        "output": "Task cancelled: Queue cleared by periodic cleanup",
                        "id": task_id,
                        "pending_status": STATUS_PENDING
                    }
                )
            db.commit()
            print(f"[CLEANER] Updated database status for cancelled tasks")
        except Exception as e:
            print(f"[CLEANER] Database update error: {e}")
            db.rollback()
        finally:
            db.close()
        
        print(f"[CLEANER] ✓ Queue cleanup completed\n")
        
        sync_redis.close()
        
    except Exception as e:
        print(f"[CLEANER] Error during queue cleanup: {e}")
        traceback.print_exc()


async def queue_cleaner_task():
    """Background coroutine that clears the queue every 60 minutes (DISABLED BY DEFAULT)"""
    print("[CLEANER] Queue cleaner task started (runs every 60 minutes)")
    
    # Wait 60 minutes before first cleanup
    interval = 60 * 60  # 60 minutes in seconds
    
    while True:
        try:
            await asyncio.sleep(interval)
            # Run in thread pool to avoid blocking
            loop = asyncio.get_event_loop()
            await loop.run_in_executor(None, clear_queue)
        except Exception as e:
            print(f"[CLEANER] Task error: {e}")
            traceback.print_exc()
            await asyncio.sleep(10)


# ========== ASYNC WORKER LOOP ==========

async def process_task(redis_client: redis.Redis, task_id: str):
    """Process a single task from Redis asynchronously"""
    print(f"\n[TASK] Processing: {task_id}")
    
    # Update status to processing
    update_task_status(task_id, STATUS_PROCESSING)
    
    # Task initialization - minimal delay
    init_delay = random.uniform(0.1, 0.3)
    print(f"[INFO] Initializing task processor ({init_delay:.1f}s)...")
    await asyncio.sleep(init_delay)
    
    try:
        # Get serialized task from Redis
        serialized = await redis_client.get(f"task:{task_id}")
        
        if not serialized:
            print(f"[ERROR] Task {task_id} not found in Redis")
            update_task_status(task_id, STATUS_FAILED, "Task data not found")
            return
        
        # Deserialize task data using jsonpickle (CPU-bound, run in executor)
        loop = asyncio.get_event_loop()
        task_data = await loop.run_in_executor(None, deserialize_task, serialized)
        
        print(f"[INFO] Deserialized task: {type(task_data).__name__}")
        
        # Execute the task
        output = await execute_task(task_data)
        
        # Update status and output
        update_task_status(task_id, STATUS_COMPLETED, output)
        print(f"[SUCCESS] Task {task_id} completed")
        
        # Clean up Redis
        await redis_client.delete(f"task:{task_id}")
        
    except Exception as e:
        print(f"[ERROR] Task {task_id} failed: {e}")
        traceback.print_exc()
        update_task_status(task_id, STATUS_FAILED, str(e))


async def worker_loop():
    """Main async worker loop - polls Redis for tasks and processes them concurrently"""
    print("=" * 60)
    print(" NEON QUEUE WORKER (ASYNC)")
    print("=" * 60)
    print(f"\n[INIT] Connecting to Redis at {REDIS_HOST}:{REDIS_PORT}")
    print(f"[INIT] Database: {DATABASE_URL}")
    print("[INIT] Worker started, polling for tasks...\n")
    
    # Create async Redis client
    redis_client = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, decode_responses=True)
    
    try:
        # Process multiple tasks concurrently
        while True:
            try:
                # Pop task from queue (blocking with timeout)
                result = await redis_client.blpop("queue:tasks", timeout=2)
                
                if result:
                    _, task_id = result
                    # Create task to process in background
                    asyncio.create_task(process_task(redis_client, task_id))
                
            except redis.ConnectionError:
                print("[ERROR] Redis connection lost, retrying in 5s...")
                await asyncio.sleep(5)
            except asyncio.CancelledError:
                print("\n[EXIT] Worker shutting down...")
                break
            except Exception as e:
                print(f"[ERROR] Unexpected error: {e}")
                traceback.print_exc()
                await asyncio.sleep(1)
    finally:
        await redis_client.close()


async def main():
    """Main entry point"""
    # Wait for other services to start
    await asyncio.sleep(3)
    
    # DISABLED: Queue cleaner - was causing legitimate tasks to be deleted
    # Uncomment only if needed for cleanup, but note it clears ALL pending tasks
    # asyncio.create_task(queue_cleaner_task())
    
    # Start the main worker loop
    await worker_loop()


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\n[EXIT] Received interrupt signal, shutting down...")
