#!/bin/bash

cd tests
../vendor/bin/phpunit --coverage-text=./coverage/res.txt --whitelist . --testdox-text ./coverage/log.txt -v ./ *Test.php
