#!/bin/bash
set -e

echo "Starting Task-Rabbit application..."

# Start containers
docker-compose up -d --build

# Ensure .env exists inside backend container
echo "Checking .env file..."
if ! docker-compose exec -T backend test -f .env; then
  echo "Creating .env from .env.example..."
  docker-compose exec -T backend cp .env.example .env
  echo "Generating app key..."
  docker-compose exec -T backend php artisan key:generate
else
  echo ".env already exists, skipping copy."
fi

# Wait for DB to be ready
echo "Waiting for database to be ready..."
until docker-compose exec -T db mysqladmin ping -h "127.0.0.1" -uroot -proot >/dev/null 2>&1; do
  echo -n "."
  sleep 2
done
echo "Database is ready!"

# Run migrations
echo "Running Laravel migrations..."
docker-compose exec backend php artisan migrate --force

echo "Task-Rabbit is up and running!"
echo "Frontend: http://localhost:5173"
echo "Backend: http://localhost:8000"