
.PHONY: test clean coverage

vendor: composer.json
	composer install --dev

build:
	mkdir --parents -- $@

test: vendor build
	phpunit

coverage: vendor build
	phpunit --coverage-html build/html/coverage

clean:
	rm --recursive --force -- vendor
	rm --recursive --force -- build
