#!/usr/bin/env bash

# Jwt tokens need openssl keys with full access
# Uses JWT_PASSPHRASE from environment, falls back to dev default "catroweb"
JWT_PASS="${JWT_PASSPHRASE:-catroweb}"

mkdir -p .jwt
if [ -f ".jwt/private.pem" ] && [ -f ".jwt/public.pem" ]; then
  echo "JWT already initialized"
else
  openssl genpkey -out .jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass "pass:${JWT_PASS}"
  openssl pkey -in .jwt/private.pem -out .jwt/public.pem -pubout -passin "pass:${JWT_PASS}"
  echo "JWT keys initialized"
fi
chmod -R 777 .jwt