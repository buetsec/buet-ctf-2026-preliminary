# CTF Challenge Makefile
# Use: make <target>

.PHONY: build run stop logs shell clean solve

# Build the Docker image
build:
	docker-compose build

# Run the challenge
run:
	docker-compose up -d
	@echo ""
	@echo "Challenge running at http://localhost:5050"
	@echo ""

# Stop the challenge
stop:
	docker-compose down

# View logs
logs:
	docker-compose logs -f

# Shell into container
shell:
	docker-compose exec shadownet sh

# Clean up everything
clean:
	docker-compose down -v --rmi local
	rm -rf vendor

# Generate a solve URL (for testing)
solve:
	@php scripts/generate_signed_url.php http://localhost:5050

# Local development with PHP built-in server
dev:
	@cp .env.docker .env 2>/dev/null || true
	@composer install
	@php artisan serve --port=5050

# Install dependencies
install:
	composer install
