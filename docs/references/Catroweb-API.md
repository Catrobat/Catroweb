# Catroweb API

The OpenAPI 3 specification lives in this repo at `src/Api/OpenAPI/specification.yaml`; server code is generated from it with `yarn run generate-api` (see `src/Api/OpenAPI/open-api.md`). Pushing a spec change to `main` publishes Swagger UI to <https://developer.catrobat.org/Catroweb/> (`.github/workflows/deploy_api_spec.yaml`).
