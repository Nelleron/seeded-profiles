# Laravel Project (Dockerized Setup)

Этот проект настроен для быстрой работы через Docker с использованием Laravel Sail. Все необходимые зависимости (PHP 8.4, MariaDB, Redis, MinIO, Mailpit) уже включены в конфигурацию.

## 🚀 Быстрый старт (Deployment)

Проект полностью контейнеризирован и автономен. Его можно запустить любым удобным способом:

### Способ А: Через стандартный Docker Compose (Рекомендуется)
Этот способ работает даже если у вас **не установлены** PHP и Composer локально. Все конфигурации (Dockerfile, скрипты БД) вынесены в папку `docker/`.

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
```

### Способ Б: Через Laravel Sail
Если вы предпочитаете использовать стандартный воркфлоу Laravel:

```bash
cp .env.example .env
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

*Примечание: Флаг `--build` обязателен только при первом запуске.*

---

## 🛠 Доступные сервисы

После успешного запуска проект будет доступен по следующим адресам:

- **Приложение:** [http://localhost](http://localhost)
- **Почта (Mailpit):** [http://localhost:8025](http://localhost:8025) (интерфейс для просмотра исходящих писем)
- **Хранилище (MinIO):** [http://localhost:8900](http://localhost:8900) (логин: `sail`, пароль: `password`)
- **База данных (MariaDB):** `localhost:3306` (тестовая БД: `laravel_test`)

---

## ✅ Качество и тестирование

- **Тестирование:** Используется **Codeception 5** (Unit, Functional). Тесты запускаются на MariaDB (`laravel_test`).
  - `docker compose exec laravel.test vendor/bin/codecept run`
- **Статический анализ:** Используется **PHPStan (Larastan)** на 7-м уровне строгости.
  - `docker compose exec laravel.test vendor/bin/phpstan analyze`
- **Стиль кода:** Используется **Laravel Pint**. Автоматически добавляется `declare(strict_types=1)`.
  - `docker compose exec laravel.test vendor/bin/pint`
- **Frontend:** **Tailwind CSS 4.0** (Vite).
  - `docker compose exec laravel.test npm run dev`

---

## Технологический стек
- **PHP 8.4**
- **Laravel 12**
- **MariaDB 11** (База данных)
- **Redis** (Кэш и сессии)
- **MinIO** (S3-совместимое хранилище)
- **Mailpit** (Отладка почты)
