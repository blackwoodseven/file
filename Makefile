all: clean test coverage

clean:
	rm -rf build/artifacts/*

test:
	vendor/bin/phpunit --testsuite=file $(TEST)

coverage:
	vendor/bin/phpunit --testsuite=file --coverage-html=build/artifacts/coverage $(TEST)

coverage-show:
	open build/artifacts/coverage/index.html
