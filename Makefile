setup:
	@make build
	@make up
	@make data
build:
	docker compose up --build 
	docker exec laravel-docker bash -c "composer install"
	docker exec laravel-docker bash -c "php artisan key:generate"
	docker exec laravel-docker bash -c "php artisan config:clear"
	docker exec laravel-docker bash -c "php artisan migrate"
	docker exec laravel-docker bash -c "php artisan optimize"
down:
	docker compose down
up:
	docker compose up -d
data:
	docker exec laravel-docker bash -c "php artisan migrate"
	docker exec laravel-docker bash -c "php artisan db:seed"
	docker exec laravel-docker bash -c "php artisan storage:link"