#!/bin/sh
set -e

echo "=== [1/4] Menjalankan Migrasi Database ==="
php artisan migrate --force || true

echo "=== [2/4] Menjalankan Seeder Data Awal (Jika Perlu) ==="
php artisan db:seed --force || true

echo "=== [3/4] Mengoptimasi Cache Konfigurasi ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== [4/4] Menjalankan RoK Joki Server di Port ${PORT:-10000} ==="
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
