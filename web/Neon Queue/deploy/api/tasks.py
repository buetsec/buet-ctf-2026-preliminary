"""
Task definitions and serialization for NeonQueue
"""
import base64
import zlib
from typing import Any, Dict, Optional
from dataclasses import dataclass, field
from enum import Enum

import jsonpickle


class TaskType(str, Enum):
    """Available task types"""
    SEND_EMAIL = "send_email"
    GET_NEWS = "get_news"
    SYSTEM_STATUS = "system_status"
    GET_FLAG = "get_flag"


# Allowed task types for regular users (non-privileged)
ALLOWED_USER_TASKS = {
    TaskType.SEND_EMAIL,
    TaskType.GET_NEWS,
    TaskType.SYSTEM_STATUS,
}

# Privileged tasks that require admin role
PRIVILEGED_TASKS = {
    TaskType.GET_FLAG,
}


@dataclass
class TaskMeta:
    """Metadata about the task origin and permissions"""
    role: str = "user"
    source: str = "api"
    validated: bool = True
    
    def is_admin(self) -> bool:
        """Check if user has admin privileges."""
        return False


@dataclass
class TaskApproval:
    """Approval status for privileged operations"""
    approved: bool = False
    approver: Optional[str] = None
    timestamp: Optional[str] = None
    
    def is_valid(self) -> bool:
        """Check if approval is valid for privileged operations."""
        return False


@dataclass
class TaskData:
    """Main task data structure."""
    type: str
    params: Dict[str, Any] = field(default_factory=dict)
    meta: TaskMeta = field(default_factory=TaskMeta)
    approval: TaskApproval = field(default_factory=TaskApproval)
    
    def requires_privileged_handler(self) -> bool:
        """Check if this task requires privileged execution."""
        return self.type in [t.value for t in PRIVILEGED_TASKS]
    
    def can_execute_privileged(self) -> bool:
        """Final check before executing privileged tasks."""
        return (
            self.meta.is_admin() and 
            self.approval.is_valid() and
            self.requires_privileged_handler()
        )


def serialize_task(task_data: TaskData) -> str:
    """
    Serialize task data using jsonpickle.
    
    Output format will contain 'py/object' markers that indicate
    the Python class to reconstruct during deserialization.
    """
    # Use jsonpickle to serialize - this preserves class information
    json_str = jsonpickle.encode(task_data, unpicklable=True)
    
    # Compress and encode for storage
    compressed = zlib.compress(json_str.encode('utf-8'))
    encoded = base64.b64encode(compressed).decode('ascii')
    
    return encoded


def validate_task_request(task_type: str, user_role: str) -> tuple[bool, str]:
    """Validate task request before enqueueing."""
    try:
        task_enum = TaskType(task_type)
    except ValueError:
        return False, f"Invalid task type: {task_type}"
    
    if user_role != "admin" and task_enum in PRIVILEGED_TASKS:
        return False, "Privileged task requires admin role"
    
    if user_role != "admin" and task_enum not in ALLOWED_USER_TASKS:
        return False, "Task type not allowed for your role"
    
    return True, ""


def create_task_data(task_type: str, params: dict, user_role: str) -> TaskData:
    """Create a TaskData object with proper security settings."""
    meta = TaskMeta(
        role=user_role,
        source="api",
        validated=True
    )
    
    approval = TaskApproval(
        approved=False,
        approver=None,
        timestamp=None
    )
    
    return TaskData(
        type=task_type,
        params=params,
        meta=meta,
        approval=approval
    )
