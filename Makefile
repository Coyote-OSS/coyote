install: install-server install-client
install-server: file-permission composer-install migration es-create seed es-index
install-client: yarn-install yarn-prod install-passport install-push

up:
	docker compose up -d

bash:
	docker compose exec -u nginx php bash

bash-root:
	docker compose exec -u root php bash

composer-install:
	docker compose exec -T -u nginx php composer install

file-permission:
	docker compose exec -T php chmod -R 777 storage/
	docker compose exec -T php chmod 777 bootstrap/cache/

migration:
	docker compose exec -T -u nginx php php artisan migrate --force

seed:
	docker compose exec -T -u nginx php php artisan db:seed

yarn-install:
	docker compose exec -T -u nginx php yarn install

yarn-prod:
	docker compose exec -T -u nginx php yarn run prod

yarn-watch:
	docker compose exec -T -u nginx php yarn run watch

cache-config:
	docker compose exec -T -u nginx php php artisan config:cache
	docker compose exec -T -u nginx php php artisan route:cache

es-create:
	docker compose exec -T -u nginx php php artisan es:create --force

es-index:
	docker compose exec -T -u nginx php php artisan es:index --force

install-passport:
	docker compose exec -T -u nginx php php artisan passport:install

install-push:
	docker compose exec -T -u nginx php php artisan webpush:vapid

redis-close-idle:
	docker compose exec redis redis-cli CONFIG SET timeout 60
