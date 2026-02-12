"""
NeonQueue API - FastAPI Backend (Fully Async)
"""
import os
import uuid
import random
import string
import asyncio
from datetime import timedelta
from typing import Optional
from functools import partial

import redis.asyncio as redis
from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, EmailStr
from sqlalchemy.orm import Session

from database import engine, get_db, Base
from models import User, Task, TaskStatus
from auth import (
    get_password_hash,
    verify_password,
    create_access_token,
    get_current_user,
    get_current_verified_user,
    ACCESS_TOKEN_EXPIRE_MINUTES
)
from tasks import (
    validate_task_request,
    create_task_data,
    serialize_task,
    TaskType
)

# Create tables
Base.metadata.create_all(bind=engine)

# Initialize FastAPI - docs disabled for CTF
app = FastAPI(
    title="NeonQueue API",
    docs_url=None,
    redoc_url=None,
    openapi_url=None
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Redis connection (async)
REDIS_HOST = os.getenv("REDIS_HOST", "localhost")
REDIS_PORT = int(os.getenv("REDIS_PORT", 6379))

# Global async Redis client
redis_client: Optional[redis.Redis] = None


@app.on_event("startup")
async def startup_event():
    """Initialize async Redis connection on startup"""
    global redis_client
    redis_client = redis.Redis(
        host=REDIS_HOST,
        port=REDIS_PORT,
        decode_responses=True,
        socket_connect_timeout=5,
        socket_keepalive=True,
        max_connections=50
    )
    print(f"✓ Connected to Redis at {REDIS_HOST}:{REDIS_PORT}")


@app.on_event("shutdown")
async def shutdown_event():
    """Close Redis connection on shutdown"""
    global redis_client
    if redis_client:
        await redis_client.close()
        print("✓ Redis connection closed")


# ========== SCHEMAS ==========

class UserRegister(BaseModel):
    username: str
    email: EmailStr
    password: str


class OTPVerify(BaseModel):
    user_id: str
    otp: str


class TaskCreate(BaseModel):
    task_type: str
    params: Optional[dict] = None


class TokenResponse(BaseModel):
    access_token: str
    token_type: str = "bearer"
    username: str


# ========== UTILITY FUNCTIONS ==========

def generate_otp() -> str:
    """Generate a 10-digit OTP (non-bruteforceable)"""
    return ''.join(random.choices(string.digits, k=10))


async def store_otp(user_id: str, otp: str) -> None:
    """Store OTP for verification (TTL: 10 minutes)"""
    await redis_client.setex(f"otp:{user_id}", 600, otp)


async def verify_otp(user_id: str, otp: str) -> bool:
    """Verify OTP from Redis"""
    stored_otp = await redis_client.get(f"otp:{user_id}")
    if stored_otp and stored_otp == otp:
        await redis_client.delete(f"otp:{user_id}")
        return True
    return False


async def enqueue_task(task_id: str, serialized_data: str) -> None:
    """Enqueue task for worker processing."""
    # Store the serialized task data
    await redis_client.set(f"task:{task_id}", serialized_data)
    
    # Add to processing queue
    await redis_client.rpush("queue:tasks", task_id)


async def run_in_executor(func, *args):
    """Run blocking function in thread pool"""
    loop = asyncio.get_event_loop()
    return await loop.run_in_executor(None, partial(func, *args))


# ========== DATABASE HELPERS (Async wrappers) ==========

async def db_get_user_by_username(db: Session, username: str) -> Optional[User]:
    """Get user by username (run in executor to avoid blocking)"""
    return await run_in_executor(
        lambda: db.query(User).filter(User.username == username).first()
    )


async def db_get_user_by_email(db: Session, email: str) -> Optional[User]:
    """Get user by email (run in executor to avoid blocking)"""
    return await run_in_executor(
        lambda: db.query(User).filter(User.email == email).first()
    )


async def db_get_user_by_id(db: Session, user_id: str) -> Optional[User]:
    """Get user by ID (run in executor to avoid blocking)"""
    return await run_in_executor(
        lambda: db.query(User).filter(User.id == user_id).first()
    )


async def db_create_user(db: Session, username: str, email: str, password_hash: str) -> User:
    """Create user (run in executor to avoid blocking)"""
    def _create():
        user = User(
            username=username,
            email=email,
            password_hash=password_hash,
            is_verified=False,
            role="user"
        )
        db.add(user)
        db.commit()
        db.refresh(user)
        return user
    
    return await run_in_executor(_create)


async def db_update_user_verified(db: Session, user_id: str) -> None:
    """Mark user as verified (run in executor to avoid blocking)"""
    def _update():
        user = db.query(User).filter(User.id == user_id).first()
        if user:
            user.is_verified = True
            db.commit()
    
    await run_in_executor(_update)


async def db_create_task(db: Session, task_id: str, user_id: str, task_type: str, params: dict) -> Task:
    """Create task (run in executor to avoid blocking)"""
    def _create():
        task = Task(
            id=task_id,
            user_id=user_id,
            task_type=task_type,
            params=params,
            status=TaskStatus.PENDING.value
        )
        db.add(task)
        db.commit()
        return task
    
    return await run_in_executor(_create)


async def db_get_user_tasks(db: Session, user_id: str):
    """Get user tasks (run in executor to avoid blocking)"""
    def _get():
        tasks = db.query(Task).filter(
            Task.user_id == user_id
        ).order_by(Task.created_at.desc()).limit(50).all()
        return [task.to_dict() for task in tasks]
    
    return await run_in_executor(_get)


async def db_get_task(db: Session, task_id: str, user_id: str):
    """Get specific task (run in executor to avoid blocking)"""
    def _get():
        task = db.query(Task).filter(
            Task.id == task_id,
            Task.user_id == user_id
        ).first()
        return task.to_dict() if task else None
    
    return await run_in_executor(_get)


# ========== AUTH ENDPOINTS ==========

@app.post("/auth/register")
async def register(user_data: UserRegister, db: Session = Depends(get_db)):
    """Register a new user account"""
    # Check if username exists (async)
    if await db_get_user_by_username(db, user_data.username):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Username already registered"
        )
    
    # Check if email exists (async)
    if await db_get_user_by_email(db, user_data.email):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email already registered"
        )
    
    # Hash password in thread pool (CPU-intensive)
    password_hash = await run_in_executor(get_password_hash, user_data.password)
    
    # Create user (async)
    user = await db_create_user(db, user_data.username, user_data.email, password_hash)
    
    # Generate and store OTP (async)
    otp = generate_otp()
    await store_otp(user.id, otp)
    
    return {
        "message": "Registration successful. Check your email for verification code.",
        "user_id": user.id
    }


@app.post("/auth/login")
async def login(
    form_data: OAuth2PasswordRequestForm = Depends(),
    db: Session = Depends(get_db)
):
    """Login with username and password"""
    # Get user (async)
    user = await db_get_user_by_username(db, form_data.username)
    
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password"
        )
    
    # Verify password in thread pool (CPU-intensive)
    password_valid = await run_in_executor(verify_password, form_data.password, user.password_hash)
    
    if not password_valid:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password"
        )
    
    # If not verified, require OTP
    if not user.is_verified:
        # Generate new OTP (async)
        otp = generate_otp()
        await store_otp(user.id, otp)
        
        return {
            "requires_otp": True,
            "user_id": user.id,
            "message": "OTP verification required"
        }
    
    # Create token
    access_token = create_access_token(
        data={"sub": user.id, "username": user.username, "role": user.role},
        expires_delta=timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    )
    
    return TokenResponse(
        access_token=access_token,
        username=user.username
    )


@app.post("/auth/verify-otp")
async def verify_otp_endpoint(
    otp_data: OTPVerify,
    db: Session = Depends(get_db)
):
    """Verify OTP and complete authentication"""
    # Get user (async)
    user = await db_get_user_by_id(db, otp_data.user_id)
    
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="User not found"
        )
    
    # Verify OTP (async)
    if not await verify_otp(otp_data.user_id, otp_data.otp):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Invalid or expired OTP"
        )
    
    # Mark user as verified (async)
    await db_update_user_verified(db, otp_data.user_id)
    
    # Create token
    access_token = create_access_token(
        data={"sub": user.id, "username": user.username, "role": user.role},
        expires_delta=timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    )
    
    return TokenResponse(
        access_token=access_token,
        username=user.username
    )


@app.get("/auth/me")
async def get_me(current_user: User = Depends(get_current_user)):
    """Get current user info"""
    return {
        "id": current_user.id,
        "username": current_user.username,
        "email": current_user.email,
        "role": current_user.role,
        "is_verified": current_user.is_verified
    }


# ========== TASK ENDPOINTS ==========

@app.post("/tasks")
async def create_task(
    task_data: TaskCreate,
    current_user: User = Depends(get_current_verified_user),
    db: Session = Depends(get_db)
):
    """Create and queue a new task."""
    # Validate task type and permissions
    is_valid, error = validate_task_request(task_data.task_type, current_user.role)
    if not is_valid:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail=error
        )
    
    # Create task record in database (async)
    task_id = str(uuid.uuid4())
    await db_create_task(db, task_id, current_user.id, task_data.task_type, task_data.params)
    
    # Create task data object
    task_obj = create_task_data(
        task_type=task_data.task_type,
        params=task_data.params or {},
        user_role=current_user.role
    )
    
    # Serialize in thread pool (CPU-intensive)
    serialized = await run_in_executor(serialize_task, task_obj)
    
    # Enqueue (async)
    await enqueue_task(task_id, serialized)
    
    return {
        "message": "Task queued successfully",
        "task_id": task_id
    }


@app.get("/tasks")
async def list_tasks(
    current_user: User = Depends(get_current_verified_user),
    db: Session = Depends(get_db)
):
    """List all tasks for the current user"""
    return await db_get_user_tasks(db, current_user.id)


@app.get("/tasks/{task_id}")
async def get_task(
    task_id: str,
    current_user: User = Depends(get_current_verified_user),
    db: Session = Depends(get_db)
):
    """Get a specific task"""
    task = await db_get_task(db, task_id, current_user.id)
    
    if not task:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Task not found"
        )
    
    return task


# ========== HEALTH CHECK ==========

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    # Check Redis connectivity
    try:
        await redis_client.ping()
        redis_status = "connected"
    except:
        redis_status = "disconnected"
    
    return {
        "status": "healthy",
        "service": "neonqueue-api",
        "redis": redis_status
    }
