#!/usr/bin/env bash

# Jwt tokens need openssl keys with full access
mkdir -p .jwt
if [ -f ".jwt/private.pem" ] && [ -f ".jwt/public.pem" ]; then
  echo "JWT already initialized"
else
  openssl genpkey -out .jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:catroweb
  openssl pkey -in .jwt/private.pem -out .jwt/public.pem -pubout -passin pass:catroweb
  echo "JWT keys initialized"
fi
chmod -R 777 .jwt