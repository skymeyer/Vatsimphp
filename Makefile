
all: update test test-examples

update:
	composer update

test:
	vendor/bin/phpunit

test-examples:
	build/run.sh test-examples

docker-all: docker-update docker-test docker-test-examples

docker-update:
	build/run.sh docker::composer update

docker-test:
	export PHP_VERSION=7.2 && build/run.sh docker::run vendor/bin/phpunit
	export PHP_VERSION=7.3 && build/run.sh docker::run vendor/bin/phpunit
	export PHP_VERSION=7.4 && build/run.sh docker::run vendor/bin/phpunit

docker-test-examples:
	export PHP_VERSION=7.2 && build/run.sh docker::run build/run.sh test-examples keep
	export PHP_VERSION=7.3 && build/run.sh docker::run build/run.sh test-examples keep
	export PHP_VERSION=7.4 && build/run.sh docker::run build/run.sh test-examples keep

docker-shell:
	build/run.sh docker::run /bin/bash

.PHONY: all update test test-examples
.PHONY: docker-all docker-update docker-test docker-test-examples docker-shell
