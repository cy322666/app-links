#!/bin/bash

run_server() {
	# Run Swoole
    php artisan octane:start --server="swoole" --host="0.0.0.0" --workers=${SWOOLE_WORKERS} --task-workers=${SWOOLE_TASK_WORKERS} --max-requests=${SWOOLE_MAX_REQUESTS} --watch ;
}


if run_server; then
    echo Server OK.
else
#  composer install
  php artisan key:generate

	php artisan octane:install --server="swoole"
    run_server;
fi
