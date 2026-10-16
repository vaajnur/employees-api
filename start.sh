#!/usr/bin/env bash
# Запуск локального тестового стенда (встроенный PHP-сервер)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
HOST="${HOST:-0.0.0.0}"
PORT="${PORT:-8080}"

if ! command -v php >/dev/null 2>&1; then
  echo "Ошибка: PHP не установлен. Установите php-cli и повторите."
  exit 1
fi

echo "Employees API: http://${HOST}:${PORT}/employees"
echo "Health:        http://${HOST}:${PORT}/health"
echo "Auth: Basic reader / ReadOnly2026!  или  Bearer emp-read-token-9f3a2c"
echo "Остановка: Ctrl+C"
exec php -S "${HOST}:${PORT}" -t "${ROOT}/public" "${ROOT}/router.php"
