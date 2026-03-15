set shell := ["bash", "-uc"]

compose_command := `echo docker-compose run -u $(id -u):$(id -g) --rm php85`
profile_command := `echo docker-compose run -u $(id -u):$(id -g) --rm profile`

build:
    docker-compose build

shell: build
    {{ compose_command }} bash

profile-shell: build
    {{ profile_command }} bash

profile-phpinfo: build
    {{ profile_command }} php --ri xdebug

destroy:
    docker-compose down -v

composer: build
    {{ compose_command }} composer install

bench: build
    {{ compose_command }} composer bench

bench-save: build
    {{ compose_command }} composer bench:save

bench-compare: build
    {{ compose_command }} composer bench:compare

lint: build
    {{ compose_command }} composer lint

refactor: build
    {{ compose_command }} composer refactor

test: build
    {{ compose_command }} composer test

test-lint: build
    {{ compose_command }} composer test:lint

test-refactor: build
    {{ compose_command }} composer test:refactor

test-type-coverage: build
    {{ compose_command }} composer test:type-coverage

test-types: build
    {{ compose_command }} composer test:types

test-unit: build
    {{ compose_command }} composer test:unit
