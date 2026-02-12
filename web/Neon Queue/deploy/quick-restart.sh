#!/bin/bash
# Quick restart without full rebuild (for minor changes)
set -e
cd "$(dirname "$0")"
echo "Quick restarting services..."
docker-compose restart api worker
echo "✓ Done! Services restarted."
docker-compose ps
