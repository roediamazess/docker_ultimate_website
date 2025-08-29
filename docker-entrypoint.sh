#!/bin/bash
set -e

# Wait for PostgreSQL to be ready using netcat
echo "Waiting for PostgreSQL to be ready..."
until nc -z db 5432; do
  echo "PostgreSQL is not ready yet. Waiting..."
  sleep 2
done
echo "PostgreSQL is ready!"

# Execute the main command
exec "$@"
