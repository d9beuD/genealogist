#!/bin/sh
set -eu

APP_RUNTIME_ENV="${APP_ENV:-prod}"

mkdir -p \
	/app/var/cache/${APP_RUNTIME_ENV}/pools/system \
	/app/var/log \
	/app/var/sass

php /app/bin/console cache:warmup --env="${APP_RUNTIME_ENV}" --no-debug --no-interaction

exec "$@"
