# Symfony 6 Item Manager Admin Panel

Test task: Admin panel for managing the list of items

## 🚀 Quick start (One-command Setup)

If you have **Make** and **Docker** installed, the project is built with a single command:

```bash
make setup


🛠 Manual Setup

# 1. Prepare environment
cp .env.example .env

# 2. Build and start containers
docker-compose up -d --build

# 3. Wait for MySQL initialization
sleep 15

# 4. Install dependencies
docker exec -it test-php-fpm composer install

# 5. Database setup & Migrations
docker exec -it test-php-fpm php bin/console doctrine:database:create --if-not-exists
docker exec -it test-php-fpm php bin/console doctrine:migrations:migrate --no-interaction

# 6. Load Test Data & Sync Cache
docker exec -it test-php-fpm php bin/console doctrine:fixtures:load --no-interaction
docker exec -it test-redis redis-cli flushall

Project URL: http://localhost/


