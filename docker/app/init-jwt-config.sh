#!/usr/bin/env bash

# Jwt tokens need openssl keys with full access
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:catroweb
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:catroweb
chmod -R 777 config/jwt
