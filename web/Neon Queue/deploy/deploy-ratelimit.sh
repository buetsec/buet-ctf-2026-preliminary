#!/bin/bash
# Emergency OTP Brute Force Protection Deployment

set -e

echo "🛡️  DEPLOYING OTP BRUTE FORCE PROTECTION"
echo "========================================"
echo ""

cd "$(dirname "$0")"

echo "[1/4] Stopping API container..."
docker-compose stop api

echo ""
echo "[2/4] Rebuilding API with rate limiting..."
docker-compose build --no-cache api

echo ""
echo "[3/4] Starting API..."
docker-compose up -d api

echo ""
echo "[4/4] Waiting for API to start..."
sleep 3

echo ""
echo "✅ Rate limiting deployed!"
echo ""
echo "Testing rate limit..."
echo "Making 4 OTP requests (should block after 3):"
echo ""

for i in {1..4}; do
  echo -n "Request $i: "
  response=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://localhost:8000/auth/verify-otp \
    -H 'Content-Type: application/json' \
    -d '{"user_id":"test123","otp":"1234567890"}')
  
  if [ "$response" == "429" ]; then
    echo "❌ BLOCKED (429 Too Many Requests) ✅ Rate limit working!"
  else
    echo "✓ $response"
  fi
  sleep 0.5
done

echo ""
echo "========================================"
echo "Protection Status:"
echo "========================================"
docker logs neonqueue-api-1 --tail 5 | grep -E "(verify-otp|rate limit)" || echo "No recent OTP attempts"

echo ""
echo "Monitor attacks with:"
echo "  docker logs -f neonqueue-api-1 | grep '429'"
echo ""
echo "🎯 Attackers will now be rate-limited!"
