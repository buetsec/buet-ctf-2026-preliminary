"""
SQLAlchemy models for NeonQueue
"""
import uuid
from datetime import datetime
from sqlalchemy import Column, String, DateTime, Text, Boolean, Enum as SQLEnum
from sqlalchemy.dialects.sqlite import JSON
import enum

from database import Base


class TaskStatus(enum.Enum):
    PENDING = "pending"
    PROCESSING = "processing"
    COMPLETED = "completed"
    FAILED = "failed"


class User(Base):
    __tablename__ = "users"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    username = Column(String(50), unique=True, nullable=False, index=True)
    email = Column(String(100), unique=True, nullable=False)
    password_hash = Column(String(128), nullable=False)
    is_verified = Column(Boolean, default=False)
    role = Column(String(20), default="user")  # user, admin
    created_at = Column(DateTime, default=datetime.utcnow)

    def __repr__(self):
        return f"<User {self.username}>"


class Task(Base):
    __tablename__ = "tasks"

    id = Column(String(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    user_id = Column(String(36), nullable=False, index=True)
    task_type = Column(String(50), nullable=False)
    params = Column(JSON, nullable=True)
    status = Column(String(20), default=TaskStatus.PENDING.value)
    output = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    completed_at = Column(DateTime, nullable=True)

    def __repr__(self):
        return f"<Task {self.id} - {self.task_type}>"

    def to_dict(self):
        return {
            "id": self.id,
            "user_id": self.user_id,
            "task_type": self.task_type,
            "params": self.params,
            "status": self.status,
            "output": self.output,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "completed_at": self.completed_at.isoformat() if self.completed_at else None,
        }
