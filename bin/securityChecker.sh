#!/bin/bash
wget -q https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_amd64 -O local-php-security-checker
chmod +x ./local-php-security-checker
./local-php-security-checker
rm ./local-php-security-checker
