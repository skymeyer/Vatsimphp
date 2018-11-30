SHELL := /bin/bash

.PHONY: all
all: install test test-examples

.PHONY: install
install:
	composer install

.PHONY: test
test:
	vendor/bin/phpunit

.PHONY: test-examples
test-examples:
	rm -rf app/cache/*.txt
	rm -rf app/logs/*.log
	cd examples && php easy_api_examples.php
	test -f app/cache/status.txt
	test -f app/cache/vatsim-data.txt
	test -f app/cache/metar-KSFO.txt
	test -f app/logs/vatsimphp.log
	cd examples && php custom_logger.php
	test -f app/logs/vatsimphp_custom.log
