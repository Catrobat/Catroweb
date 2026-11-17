#!/bin/bash

set -e

# Preserve infrastructure files the generator downgrades (removes return types,
# #[\Override], constructor promotion, etc.). We restore them after generation.
PRESERVE_DIR=$(mktemp -d)
cp -a src/Api/OpenAPI/Server/Service/ "$PRESERVE_DIR/Service" 2>/dev/null || true
cp -a src/Api/OpenAPI/Server/DependencyInjection/ "$PRESERVE_DIR/DependencyInjection" 2>/dev/null || true
cp src/Api/OpenAPI/Server/OpenAPIServerBundle.php "$PRESERVE_DIR/OpenAPIServerBundle.php" 2>/dev/null || true
cp src/Api/OpenAPI/Server/Controller/Controller.php "$PRESERVE_DIR/Controller.php" 2>/dev/null || true

# Clean old files
rm -rf src/Api/OpenAPI/Server/*
rm -rf src/Api/OpenAPI/Server/.* 2>/dev/null || true

# Setting up version; New versions often introduce breaking changes -> manually check each upgrade
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
rm -rf phpunit.xml.dist git_push.sh .travis.yml .php_cs.dist .gitignore .coveralls.yml \
       Tests docs autoload.php composer.json README.md \
       .openapi-generator .openapi-generator-ignore
cd - > /dev/null

# Restore infrastructure files (generator downgrades modern PHP patterns)
if [ -d "$PRESERVE_DIR/Service" ]; then
  cp -a "$PRESERVE_DIR/Service/" src/Api/OpenAPI/Server/Service/
  cp -a "$PRESERVE_DIR/DependencyInjection/" src/Api/OpenAPI/Server/DependencyInjection/
  cp "$PRESERVE_DIR/OpenAPIServerBundle.php" src/Api/OpenAPI/Server/OpenAPIServerBundle.php
  cp "$PRESERVE_DIR/Controller.php" src/Api/OpenAPI/Server/Controller/Controller.php
fi
rm -rf "$PRESERVE_DIR"

# Fix generated code: double-escaped regex, string Assert\Type refs
find src/Api/OpenAPI/Server -name '*.php' -exec sed -i '' 's/\\\\-/\\-/g' {} +
find src/Api/OpenAPI/Server -name '*.yaml' -exec sed -i '' 's/\\\\-/\\-/g' {} +
# Literal routes before placeholder routes (/projects/search before /projects/{id})
php src/Api/OpenAPI/sort-routes.php
# Model docblock annotations → attributes, the only form Symfony's validator and JMS read
bin/rector process --config src/Api/OpenAPI/rector-attributes.php --clear-cache --no-progress-bar
# Symfony 8 rejects Choice(array) (an options array); the list must be Choice(choices: array)
find src/Api/OpenAPI/Server -name '*.php' -exec sed -i '' 's/Assert\\Choice(\[/Assert\\Choice(choices: [/g' {} +
# Note: the project-wide Rector run (bin/rector process src/Api/OpenAPI/Server/, after this script) turns Assert\Type('OpenAPI\\Server\\Model\\...') into the
# fully qualified \OpenAPI\Server\Model\...::class. Do NOT sed it to an unqualified ::class:
# the controllers don't import Model classes, so it would resolve to the Controller namespace.

yarn run fix
