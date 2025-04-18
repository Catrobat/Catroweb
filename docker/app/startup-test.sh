#!/usr/bin/env bash
set -e

# Optional: log function for consistent output
log() {
  echo "[start.sh] $*"
}

DB_HOST="db.catroweb.test"
DB_PORT="3306"
WAIT_SCRIPT="./docker/app/wait-for-it.sh"

log "Waiting for database at $DB_HOST:$DB_PORT..."
if "$WAIT_SCRIPT" "$DB_HOST:$DB_PORT" -t 60; then
  log "Database is available. Starting Apache..."
  exec /usr/sbin/apache2ctl -D FOREGROUND
else
  log "Database not available after timeout. Exiting."
  exit 1
fi
