#!/bin/sh
set -e

# Render supplies $PORT; make Apache listen on it.
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Import the schema on first start (when the database is still empty).
php /opt/pawpaws/init_db.php || echo "init_db failed (continuing)"

exec "$@"
