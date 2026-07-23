# TurkAi Interview Case

.env dosyası hızlı başlangıç için bilerek commit edilmiştir

## 1. Docker

```bash
docker compose up -d --build
```

```bash
docker exec -it turk-ai-php composer install
```

| | Adres |
|---|---|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000/api/v1 |
| PostgreSQL | localhost:5432 (`school_db` / `dbuser` / `dbpass`) |

## 2. Migration

```bash
docker exec -it turk-ai-php php artisan migrate --seed
```

Admin: `admin@localhost.com` / `123456789`

## 3. Mailtrap

Mail gönderiminin başarılı olması için mailtrap bilgilerinizi girmeniz gerekmektedir.

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<mailtrap inbox username>
MAIL_PASSWORD=<mailtrap inbox password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@school.test
```

```bash
docker exec -it turk-ai-php php artisan config:clear && docker compose restart php
```

## 4. Postman

`TurkAi-Interview-Case-Collection.postman_collection.json` dosyasını Postman'e import ederek endpointleri inceleyebilirsiniz.
