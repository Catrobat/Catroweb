#!/bin/bash

# Clean old files
rm -rf src/Api/OpenAPI/Server/*
rm -rf src/Api/OpenAPI/Server/.* # hidden files also

# Setting up version; New versions often introduce breaking changes -> manually check each upgrade
# npm install
openapi-generator-cli version-manager set 7.5.0

# Generate the code for symfony projects
openapi-generator-cli generate \
  -i src/Api/OpenAPI/specification.yaml \
  -g php-symfony \
  -p sortParamsByRequiredFlag=true \
  -p skipFormModel=true \
  -p variableNamingConvention=snake_case \
  -p phpLegacySupport=false \
  -o src/Api/OpenAPI/Server


# Remove files we do not need
cd src/Api/OpenAPI/Server || { echo "OpenAPI directory not found - Are you calling this script from the project root?"; exit 1; }
rm -rf phpunit.xml.dist git_push.sh .travis.yml .php_cs.dist .gitignore .coveralls.yml Tests docs autoload.php composer.json

npm run fix