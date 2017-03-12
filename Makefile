.PHONY: all clean install test test-70 test-71 test-composer test-readme
TEST_CMD := vendor/phpunit/phpunit/phpunit --colors=always

all: install test

clean:
	rm -fr build/ vendor/ composer.lock

install:
	composer install

PHP70 = $(shell command -v php70x 2>/dev/null)
PHP71 = $(shell command -v php71x 2>/dev/null)
test:
ifeq ($(and $(PHP70),$(PHP71)),)
	make -j2 -O test-readme test-composer
else
	make -j3 -O test-readme test-70 test-71
endif

test-70:
	php70x $(TEST_CMD) --no-coverage

test-71:
	php71x $(TEST_CMD) --coverage-html build/logs/coverage

test-composer:
	composer test

test-readme:
	bin/readme-test
	@echo README code blocks are valid
