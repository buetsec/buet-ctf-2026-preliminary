#!/bin/bash
# NeonQueue Complete Async Fix - Restart Script

set -e

echo "========================================================"
echo "  NeonQueue ASYNC Fix - Full Rebuild & Restart"
echo "========================================================"
echo ""
echo "This will apply:"
echo "  ✓ Async API (no more blocking!)"
echo "  ✓ Async Worker (concurrent processing)"
echo "  ✓ 4 API workers (parallel requests)"
echo "  ✓ 3 Task workers (parallel tasks)"
echo "  ✓ Increased Redis memory (256MB)"
echo "  ✓ Connection pooling"
echo "  ✓ Queue cleaner DISABLED"
echo ""

cd "$(dirname "$0")"

echo "[1/6] Stopping existing containers..."
docker-compose down -v  # -v removes volumes for clean start

echo ""
echo "[2/6] Removing old containers and images..."
docker-compose rm -f
docker rmi $(docker images | grep neonqueue | awk '{print $3}') 2>/dev/null || true

echo ""
echo "[3/6] Rebuilding API with full async support..."
docker-compose build --no-cache api

echo ""
echo "[4/6] Rebuilding Worker with async processing..."
docker-compose build --no-cache worker

echo ""
echo "[5/6] Starting all services (Redis, API, Workers)..."
docker-compose up -d

echo ""
echo "[6/6] Waiting for services to initialize..."
sleep 5

echo ""
echo "========================================================"
echo "  ✓ All Services Started Successfully!"
echo "========================================================"
echo ""

# Check status
docker-compose ps

echo ""
echo "Quick Tests:"
echo "  1. Health Check:"
echo "     curl http://localhost:8000/health"
echo ""
echo "  2. Test Registration:"
echo "     curl -X POST http://localhost:8000/auth/register \\"
echo "       -H 'Content-Type: application/json' \\"
echo "       -d '{\"username\":\"testuser\",\"email\":\"test@test.com\",\"password\":\"pass123\"}'"
echo ""
echo "Monitor Logs:"
echo "  • All services: docker-compose logs -f"
echo "  • API only:     docker-compose logs -f api"
echo "  • Worker only:  docker-compose logs -f worker"
echo ""
echo "Check Performance:"
echo "  • Redis queue:  redis-cli -h localhost -p 6379 LLEN queue:tasks"
echo "  • API stats:    docker stats neonqueue-api-1"
echo ""
echo "========================================================"
