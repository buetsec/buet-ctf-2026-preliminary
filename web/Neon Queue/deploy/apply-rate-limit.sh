#!/bin/bash
set -e

echo "🛡️  Applying Rate Limit Protection"
echo "=================================="
echo ""
echo "This will stop OTP brute force attacks by:"
echo "  • Limiting OTP attempts to 3/minute per IP"
echo "  • Limiting OTP attempts to 5 per user per 5 minutes"
echo "  • Rate limiting all other endpoints"
echo ""

cd "$(dirname "$0")"

echo "[1/3] Stopping API..."
docker-compose stop api

echo ""
echo "[2/3] Rebuilding API with rate limiting..."
docker-compose build --no-cache api

echo ""
echo "[3/3] Starting API..."
docker-compose up -d api

echo ""
echo "✅ Rate limiting applied!"
echo ""
echo "Test it:"
echo "  # Should succeed 3 times, then get 429"
echo "  for i in {1..5}; do curl -s -o /dev/null -w '%{http_code}\n' -X POST http://localhost:8000/auth/verify-otp -H 'Content-Type: application/json' -d '{\"user_id\":\"test\",\"otp\":\"1234567890\"}'; done"
echo ""
echo "Monitor attacks being blocked:"
echo "  docker-compose logs -f api | grep '429'"
echo ""
