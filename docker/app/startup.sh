#!/usr/bin/env bash
set -e

log() {
  echo "[startup-dev.sh] $*"
}

WAIT_SCRIPT="./docker/app/wait-for-it.sh"

# === Wait for MariaDB ===
DB_HOST="db.catroweb.test"
DB_PORT="3306"
log "Waiting for MariaDB at $DB_HOST:$DB_PORT..."
if "$WAIT_SCRIPT" "$DB_HOST:$DB_PORT" -t 60; then
  log "MariaDB is available."
else
  log "MariaDB not available after timeout. Exiting."
  exit 1
fi

DB_HOST="db.catroweb.dev"
DB_PORT="3306"
log "Waiting for MariaDB at $DB_HOST:$DB_PORT..."
if "$WAIT_SCRIPT" "$DB_HOST:$DB_PORT" -t 60; then
  log "MariaDB is available."
else
  log "MariaDB not available after timeout. Exiting."
  exit 1
fi

# === Wait for Headless Chrome ===
CHROME_HOST="chrome.catroweb"
CHROME_PORT="9222"
log "Waiting for Chrome at $CHROME_HOST:$CHROME_PORT..."
if "$WAIT_SCRIPT" "$CHROME_HOST:$CHROME_PORT" -t 60; then
  log "Chrome is available."
else
  log "Chrome not available after timeout. Exiting."
  exit 1
fi

# === Wait for Elasticsearch ===
ELASTIC_HOST="elasticsearch"
ELASTIC_PORT="9200"
log "Waiting for Elasticsearch at $ELASTIC_HOST:$ELASTIC_PORT..."
if "$WAIT_SCRIPT" "$ELASTIC_HOST:$ELASTIC_PORT" -t 60; then
  log "Elasticsearch is available."
else
  log "Elasticsearch not available after timeout. Exiting."
  exit 1
fi

# === Start Apache ===
log "All services are ready. Starting Apache..."
exec /usr/sbin/apache2ctl -D FOREGROUND
