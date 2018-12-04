start:
	docker-compose -f docker/docker-compose.yml up -d

start-test-db:
	docker-compose -f docker/docker-compose.test.yml up -d postgres-testing

stop:
	docker-compose -f docker/docker-compose.yml -f docker/docker-compose.test.yml kill

logs:
	docker-compose -f docker/docker-compose.yml logs -f

test:
	./scripts/test.sh

attach:
	docker exec -it caronae-backend sh

