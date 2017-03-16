.PHONY: all clean install test test-70 test-71

all: install test

clean:
	rm -fr build/ vendor/ composer.lock

install:
	composer install

PHP70 = $(shell command -v php70x 2>/dev/null)
PHP71 = $(shell command -v php71x 2>/dev/null)
test:
ifeq ($(and $(PHP70),$(PHP71)),)
	composer test
else
	make -j2 -O test-70 test-71
endif

TEST_CMD := vendor/phpunit/phpunit/phpunit --colors=always

test-70:
	php70x -v
	php70x $(TEST_CMD) --no-coverage

test-71:
	php71x -v
	php71x $(TEST_CMD) --coverage-html build/logs/coverage
