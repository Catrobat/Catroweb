#!/usr/bin/env bash

# Jwt tokens need openssl keys with full access
mkdir -p .jwt
if [ ! -f ".jwt/private.pem" ]; then
  openssl genpkey -out .jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:catroweb
fi
if [ ! -f ".jwt/public.pem" ]; then
  openssl pkey -in .jwt/private.pem -out .jwt/public.pem -pubout -passin pass:catroweb
fi
chmod -R 777 .jwt