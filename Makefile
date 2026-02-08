setup:
	@echo "📄 Configuration check .env..."
	@if [ ! -f .env ]; then cp .env.example .env; echo "✅ .env created from example"; else echo "ℹ️  .env already exists"; fi
	
	@echo "🐳 Container assembly and launch Docker..."
	docker-compose up -d --build

	@echo "⏳ Waiting for MySQL to be ready (15 seconds)..."
	@sleep 15
	
	@echo "📦 Installing dependencies Composer..."
	docker exec -it test-php-fpm composer install
	
	@echo "🗄️ Setting up the database and running migrations..."
	docker exec -it test-php-fpm php bin/console doctrine:database:create --if-not-exists
	docker exec -it test-php-fpm php bin/console doctrine:migrations:migrate --no-interaction
	
	@echo "🧪 Loading test data (Fixtures)..."
	docker exec -it test-php-fpm php bin/console doctrine:fixtures:load --no-interaction
	
	@echo "🧹 Redis cleanup (so that data from fixtures appears in the cache)..."
	docker exec -it test-redis redis-cli flushall
	
	@echo "\n"
	@echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
	@echo "🎉 PROJECT SUCCESSFULLY LAUNCHED!"
	@echo "🌐 Admin Panel: http://localhost/"
	@echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
