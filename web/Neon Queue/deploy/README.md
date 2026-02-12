# NEON QUEUE

```
 ███╗   ██╗███████╗ ██████╗ ███╗   ██╗     ██████╗ ██╗   ██╗███████╗██╗   ██╗███████╗
 ████╗  ██║██╔════╝██╔═══██╗████╗  ██║    ██╔═══██╗██║   ██║██╔════╝██║   ██║██╔════╝
 ██╔██╗ ██║█████╗  ██║   ██║██╔██╗ ██║    ██║   ██║██║   ██║█████╗  ██║   ██║█████╗  
 ██║╚██╗██║██╔══╝  ██║   ██║██║╚██╗██║    ██║▄▄ ██║██║   ██║██╔══╝  ██║   ██║██╔══╝  
 ██║ ╚████║███████╗╚██████╔╝██║ ╚████║    ╚██████╔╝╚██████╔╝███████╗╚██████╔╝███████╗
 ╚═╝  ╚═══╝╚══════╝ ╚═════╝ ╚═╝  ╚═══╝     ╚══▀▀═╝  ╚═════╝ ╚══════╝ ╚═════╝ ╚══════╝
                                                                                      
```

## Challenge Description

Welcome to **NeonQueue**, the next-generation task processing system used by elite hackers worldwide.

NeonQueue provides a secure interface for submitting background tasks. Our multi-layer validation ensures that only authorized operations can be executed.

**Your mission:** Find a way to retrieve the flag.

## Services

| Service | Port | Description |
|---------|------|-------------|
| Frontend | 8080 | Web interface |
| API | 8000 | Backend API |
| Redis | 6379 | Task queue |

## Getting Started

```bash
docker-compose up --build
```

Then visit http://localhost:8080

## Features

- **User Registration & Login** - Create an account and authenticate
- **Email Verification** - Secure OTP-based verification
- **Task Submission** - Queue background tasks for processing
- **Real-time Status** - Monitor your task execution

## Available Tasks

- **Send Email** - Queue an email for delivery
- **Get Recent News** - Fetch the latest headlines
- **System Status** - View system health metrics

## Architecture Notes

The system uses a worker-based architecture:

1. Users submit tasks via the web interface
2. API validates and queues tasks
3. Worker processes tasks asynchronously
4. Results are stored and displayed

All tasks are validated by the API before being queued for processing.

---

*"Trust the system. The system is secure."*

— NeonQueue Development Team
