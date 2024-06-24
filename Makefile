.PHONY: up down build logs shell migrate seed simulate status restart clean

up: ## Поднять весь стек одной командой
	@test -f .env || cp .env.example .env
	docker compose up -d --build
	@echo ""
	@echo "Access Control System is up"
	@echo "  CRM:              http://localhost:$${APP_PORT:-8000}"
	@echo "  QR display:       http://localhost:$${APP_PORT:-8000}/qr/show"
	@echo "  RabbitMQ UI:      http://localhost:$${RABBITMQ_MANAGEMENT_PORT:-15672}  (access / secret)"
	@echo "  Login:            admin@diplom.ru / pa\$$\$$w0rd123"

down: ## Остановить контейнеры
	docker compose down

build: ## Пересобрать образы
	docker compose build --no-cache

logs: ## Логи всех сервисов
	docker compose logs -f --tail=200

shell: ## Shell в контейнере приложения
	docker compose exec app sh

migrate: ## Миграции + сиды
	docker compose exec app php artisan migrate --force
	docker compose exec app php artisan db:seed --force

simulate: ## Одноразово опубликовать IoT-события
	docker compose exec app php artisan rabbitmq:simulate-access-events --once

status: ## Статус контейнеров
	docker compose ps

restart: ## Перезапуск
	docker compose restart

clean: ## Полная очистка (включая volumes)
	docker compose down -v --remove-orphans
