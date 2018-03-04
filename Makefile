.DEFAULT_GOAL := help
.PHONY: help
.SILENT:

GREEN  := $(shell tput -Txterm setaf 2)
WHITE  := $(shell tput -Txterm setaf 7)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

test: lint phpcs phpunit phpstan

deploy: up install

up:
	docker-compose up -d

install:
	composer install --prefer-dist --no-interaction --no-suggest

## Run PHP unit tests
phpunit:
	@echo "${GREEN}Unit tests${RESET}"
	@php bin/phpunit

## Run PHP code sniffer
phpcs:
	@echo "${GREEN}PHP Code Sniffer${RESET}"
	@php bin/phpcs -p --standard=psr2 --colors src/

## Run PHPStan
phpstan:
	@echo "${GREEN}PHPStan${RESET}"
	@php bin/phpstan analyse -l 0 src/

## PHP Parallel Lint
lint:
	@echo "${GREEN}PHP Parallel Lint${RESET}"
	@php bin/parallel-lint src/ tests/

## Fix PHP syntax with code sniffer
fix:
	@php bin/php-cs-fixer --no-interaction fix

## Test Coverage HTML
coverage:
	@echo "${GREEN}Tests with coverage${RESET}"
	@php bin/phpunit --coverage-clover=coverage.xml

## Prints this help
help:
	@echo "\nUsage: make ${YELLOW}<target>${RESET}\n\nThe following targets are available:\n";
	@awk -v skip=1 \
		'/^##/ { sub(/^[#[:blank:]]*/, "", $$0); doc_h=$$0; doc=""; skip=0; next } \
		 skip  { next } \
		 /^#/  { doc=doc "\n" substr($$0, 2); next } \
		 /:/   { sub(/:.*/, "", $$0); printf "\033[34m%-30s\033[0m\033[1m%s\033[0m %s\n", $$0, doc_h, doc; skip=1 }' \
		$(MAKEFILE_LIST)
