compose_command = docker-compose run -u $(shell id -u):$(shell id -g) --rm php85
profile_command = docker-compose run -u $(shell id -u):$(shell id -g) --rm profile

build:
	docker-compose build

shell: build
	$(compose_command) bash

profile-shell: build
	$(profile_command) bash

profile-phpinfo: build
	$(profile_command) php --ri xdebug

destroy:
	docker-compose down -v

composer: build
	$(compose_command) composer install

bench: build
	$(compose_command) composer bench

bench-compare: build
	$(compose_command) composer bench:compare

bench\:compare: build
	$(compose_command) composer bench:compare

bench-cline: build
	$(compose_command) composer bench:cline

bench-cline-save: build
	$(compose_command) composer bench:cline:save

bench-cline-compare: build
	$(compose_command) composer bench:cline:compare

lint: build
	$(compose_command) composer lint

refactor: build
	$(compose_command) composer refactor

test: build
	$(compose_command) composer test

test\:lint: build
	$(compose_command) composer test:lint

test\:refactor: build
	$(compose_command) composer test:refactor

test\:type-coverage: build
	$(compose_command) composer test:type-coverage

test\:types: build
	$(compose_command) composer test:types

test\:unit: build
	$(compose_command) composer test:unit
