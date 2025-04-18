#!/usr/bin/env bash
set -e

log() {
  echo "[start-with-migrations.sh] $*"
}

DB_HOST="db.catroweb.dev"
DB_PORT="3306"
WAIT_SCRIPT="./docker/app/wait-for-it.sh"

log "Waiting for database at $DB_HOST:$DB_PORT..."
if "$WAIT_SCRIPT" "$DB_HOST:$DB_PORT" -t 60; then
  log "Database is available. Running migrations..."

  if bin/console doctrine:migrations:migrate --no-interaction; then
    log "Migrations successful."

    if ! pgrep -x apache2 > /dev/null; then
      log "Apache not running. Starting Apache in foreground..."
      exec /usr/sbin/apache2ctl -D FOREGROUND
    else
      log "Apache is already running. Keeping container alive..."
      tail -f /dev/null
    fi
  else
    log "Migration command failed!"
    exit 2
  fi
else
  log "Database not available after timeout. Exiting."
  exit 1
fi
