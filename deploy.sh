#!/bin/bash
set -e

echo "🚀 Avtomaster Production Deployment"
echo "===================================="

# Check if .env.prod exists
if [ ! -f .env.prod ]; then
    echo "❌ .env.prod not found!"
    echo "   Copy .env.prod and fill in production values."
    exit 1
fi

# Check for SSL certificates
if [ ! -d docker/nginx/ssl ]; then
    echo "❌ SSL certificates not found!"
    echo "   Create docker/nginx/ssl/ with:"
    echo "   - fullchain.pem"
    echo "   - privkey.pem"
    exit 1
fi

echo "✅ Building production images..."
docker compose -f docker-compose.prod.yml build

echo "✅ Starting services..."
docker compose -f docker-compose.prod.yml up -d

echo "✅ Running database migrations..."
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

echo "✅ Warming up cache..."
docker compose -f docker-compose.prod.yml exec php php bin/console cache:warmup --env=prod

echo "✅ Clearing old cache..."
docker compose -f docker-compose.prod.yml exec php php bin/console cache:pool:clear cache.global

echo ""
echo "🎉 Deployment complete!"
echo "   Frontend: https://avtomaster.kg"
echo "   API:      https://avtomaster.kg/api/v1/health"
