#!/bin/sh
# Builds dist-infinityfree/: upload its contents to htdocs/ and import import.sql via phpMyAdmin.
set -e
cd "$(dirname "$0")/.."
OUT=dist-infinityfree
rm -rf "$OUT"
mkdir -p "$OUT/htdocs/uploads"

cp *.php "$OUT/htdocs/"
rm -f "$OUT/htdocs/config.local.php" "$OUT/htdocs/config.local.example.php" "$OUT/htdocs/seed_orders.php"
cp -R assets "$OUT/htdocs/assets"
cp config.local.example.php "$OUT/htdocs/config.local.php"

# php_flag in .htaccess causes HTTP 500 on FastCGI hosts, so use a safe variant.
cat > "$OUT/htdocs/uploads/.htaccess" <<'HT'
<FilesMatch "\.(php|phtml|phar)$">
  Require all denied
</FilesMatch>
HT

# One import file, without DROP/CREATE DATABASE/USE (InfinityFree creates the DB for you).
cat database/database.sql database/add_pets_20.sql \
  | grep -v -i -E '^\s*(DROP DATABASE|CREATE DATABASE|USE) ' \
  | sed 's/ENGINE=InnoDB/ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci/' > "$OUT/import.sql"

echo "Done -> $OUT/"
