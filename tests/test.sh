#!/bin/sh

./vendor/bin/phpunit --teamcity > output.txt

if ! match.sh output.txt expected_output_template.txt; then
	git diff --no-index --word-diff --word-diff-regex=. --color=always expected_output_template.txt output.txt
	exit 1
fi
