CURRENT_PATH := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))
DC_FRONTEND := docker compose -p samirify-dt-lvm -f $(CURRENT_PATH)docker/docker-compose.frontend.yml --env-file $(CURRENT_PATH)app/frontend/.env
DC_BACKEND := docker compose -p samirify-dt-lvm -f $(CURRENT_PATH)docker/docker-compose.backend.yml --env-file $(CURRENT_PATH)app/backend/.env

.PHONY: up down build install logs shell sockets sockets-dev

up:
	$(DC_BACKEND) up -d --build
	$(DC_FRONTEND) up -d --build
	docker exec -it lvm-php chmod 777 /var/run/docker.sock

down:
	$(DC_BACKEND) down
	$(DC_FRONTEND) down

build:
	$(DC_BACKEND) build
	$(DC_FRONTEND) build
	docker exec -it lvm-php chmod 777 /var/run/docker.sock

install: 
	cp ./app/backend/.env.example ./app/backend/.env
	cp ./app/backend/config/project-pipelines-example.yaml ./app/backend/config/project-pipelines.yaml
	$(DC_BACKEND) up -d --build
	docker exec -it lvm-php composer install
	docker exec -it lvm-php php artisan key:generate
	sleep 10
	docker exec -it lvm-php php artisan migrate:fresh --seed
	docker exec -it lvm-php chmod 777 /var/run/docker.sock

	cp ./app/frontend/.env.example ./app/frontend/.env
	$(DC_FRONTEND) up -d --build
logs:
	$(DC_BACKEND) logs -f

shell:
	docker exec -it lvm-php bash

sockets:
	docker exec -it lvm-php chmod 777 /var/run/docker.sock
	docker exec -it lvm-php php artisan reverb:start

sockets-dev:
	docker exec -it lvm-php chmod 777 /var/run/docker.sock
	docker exec -it lvm-php php artisan reverb:start --debug
