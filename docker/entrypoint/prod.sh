#!/bin/sh
set -eu

APP_RUNTIME_ENV="${APP_ENV:-prod}"
export TMPDIR=/app/tmp
APP_RUN_USER="${APP_RUN_USER:-appuser}"

mkdir -p \
	"/app/var/cache/${APP_RUNTIME_ENV}/pools/system" \
	/app/var/log \
	/app/var/sass \
	/app/config/jwt \
	/app/tmp

if [ "$(id -u)" = "0" ]; then
	chown -R "${APP_RUN_USER}:${APP_RUN_USER}" \
		/app/var \
		/app/config/jwt \
		/app/tmp

	exec gosu "${APP_RUN_USER}" "$0" "$@"
fi

php /app/bin/console cache:warmup --env="${APP_RUNTIME_ENV}" --no-debug --no-interaction
php /app/bin/console lexik:jwt:generate-keypair --skip-if-exists
php /app/bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"
