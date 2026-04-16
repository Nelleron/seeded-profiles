# 🚀 Laravel AI Seeding Project

Проект для автоматической генерации уникальных профилей пользователей с использованием AI-фотографий (HuggingFace API) и умного сидинга приглашений.

## 🛠 Быстрый старт (Deployment)

Проект полностью контейнеризирован (**PHP 8.4, MariaDB, MinIO**).

```bash
# 1. Подготовка окружения
cp .env.example .env

# 2. Запуск контейнеров
docker compose up -d --build

# 3. Установка зависимостей и настройка
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate

# 4. Базовое наполнение (Города и Типы приглашений)
docker compose exec laravel.test php artisan db:seed --class=CitySeeder
docker compose exec laravel.test php artisan db:seed --class=InvitationTypeSeeder
```

---

##  Artisan-команда для генерации фейковых профилей (Seeding)

Команда для генерации "живых" профилей. Она создает пользователей, заполняет их профили уникальными именами и био, назначает циклические типы приглашений и генерирует реальные фото через AI.

```bash
docker compose exec laravel.test php artisan seed:invitations {--city=ID} {--count=10}
```

---

##  Настройка AI (HuggingFace)

Для работы генерации фото необходимо получить бесплатный токен на [huggingface.co](https://huggingface.co/settings/tokens) и добавить его в `.env`:

```env
HUGGINGFACE_API_KEY=your_token_here
```

### Расширенные настройки
Вы можете настроить параметры генерации в `.env` файле:
- `HUGGINGFACE_MODEL`: Используемая модель (по умолчанию Stable Diffusion 3 Medium).
- `HUGGINGFACE_IMAGE_WIDTH` / `HEIGHT`: Размер генерируемых фото (по умолчанию 256x256).
- `HUGGINGFACE_NEGATIVE_PROMPT`: Исключаемые объекты на фото (качество, артефакты).

Фотографии сохраняются в **MinIO** по пути `users/{user_id}/photos/`.

---

##  Тестирование CRUD без AI (MinIO)

Для проверки работы удаления и загрузки фото без генерации через AI:

```bash
# Создать тестовых юзеров с пустыми фото в MinIO
docker compose exec laravel.test php artisan test:minio --create --count=5

# Показать статус (юзеры, фото в БД и MinIO)
docker compose exec laravel.test php artisan test:minio --status

# Удалить всех сидированных юзеров и их фото из MinIO
docker compose exec laravel.test php artisan test:minio --delete

# Удалить юзеров конкретного города
docker compose exec laravel.test php artisan test:minio --city=1

# Полная очистка MinIO и БД
docker compose exec laravel.test php artisan test:minio --clean

# Полный цикл тестирования (очистка → создание → удаление → проверка)
docker compose exec laravel.test php artisan test:minio
```

---

## ✅ Тестирование и Качество

Мы используем **Codeception 5** для обеспечения надежности.

### Запуск тестов:
```bash
# Все тесты (Unit + Functional)
docker compose exec laravel.test vendor/bin/codecept run --steps

# Только функциональные (проверка Artisan команды и БД)
docker compose exec laravel.test vendor/bin/codecept run Functional

# Phpstan тесты 
docker compose exec laravel.test vendor/bin/phpstan analyze
```

### Стандарты:
- **PHPStan**: Строгий анализ кода (уровень 5) с использованием Larastan.
- **Laravel Pint**: Единообразный стиль кода + `strict_types`.
- **Локализация**: Тесты и сообщения в консоли полностью на русском языке.

---

##  CRUD для Seeded-профилей

Управление сгенерированными профилями через веб-интерфейс:

```
GET  /seeded              # Список сидированных профилей (с фильтрацией по городу)
DELETE /seeded             # Удаление всех сидированных профилей
DELETE /seeded/city/{id}   # Удаление профилей конкретного города
GET  /storage/avatars/{path}  # Отдача фото из MinIO
```

Веб-интерфейс: `/seeded` — отображает профили с фильтрацией по городу и возможностью массового удаления.

---

##  Технологический стек
- **PHP 8.4** & **Laravel 12**
- **MariaDB 11** (База данных)
- **MinIO** (S3-хранилище для фото)
- **HuggingFace API** (AI генерация изображений)
