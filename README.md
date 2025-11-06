# interview_06_11_25

**Laravel Finance API** — это RESTful-сервис для управления балансами пользователей и выполнения финансовых операций:
- пополнение (`deposit`);
- списание (`withdraw`);
- перевод между пользователями (`transfer`);
- Просмотр баланса (`balance`);

Проект разворачивается в **Docker-контейнерах** (PHP-FPM + Nginx + PostgreSQL) и полностью изолирован от окружения хоста.  

---

## Технологии

- **PHP 8.2 (FPM)**
- **Laravel 11**
- **PostgreSQL 15**
- **Nginx (Alpine)**
- **Docker Compose**
- **Composer 2**

---

## Развёртывание

1. **Склонировать репозиторий**
   ```bash  
   git clone https://github.com/<your-org>/<repo>.git  
   cd <repo>  
   ```

2. **Собрать и запустить контейнеры**

docker compose up -d --build  

3. **Войти в контейнер приложения**

docker compose exec app bash  

4. **Установить зависимости Laravel**

composer install  

5. **Создать .env**

Скопировать .env.example с заменой следующих ключей  
APP_NAME=Laravel  
APP_ENV=local  
APP_DEBUG=true  
APP_URL=http://localhost  

DB_CONNECTION=pgsql  
DB_HOST=db  
DB_PORT=5432  
DB_DATABASE=laravel  
DB_USERNAME=laravel  
DB_PASSWORD=secret  

SESSION_DRIVER=file  
CACHE_STORE=file  
QUEUE_CONNECTION=sync  

6. **Сгенерировать ключ приложения**
 
php artisan key:generate  
 
7. **Сгенерировать ключ приложения**
 
php artisan migrate  
 
 
---
 
## Структура проекта
  
project-root/  
├── src/                        # Laravel приложение  
│   ├── app/Http/Controllers/   # Контроллеры API  
│   │   ├── Api/  
│   │   │   ├── BaseAPIController.php  
│   │   │   ├── TransactionController.php  
│   │   │   └── UserController.php  
│   ├── database/  
│   │   └── migrations/         # Миграции users и transactions  
│   └── routes/  
│       └── api.php             # Маршруты API  
│  
├── docker-compose.yml          # Основной конфиг Docker  
├── Dockerfile                  # Образ PHP-FPM  
└── nginx/conf.d/default.conf   # Конфигурация nginx  

---
 
## API запросы и ответы
 
1. **Пополнение баланса**
**POST** `/api/deposit`  

**Тело запроса:**  
```json
{
  "user_id": 9,
  "amount": 1000,
  "comment": "Внесение денежных средств"
}
```

Возможные коды ответа:  
200 — успешно зачислено  
422 — ошибка валидации  
 
структура ответа:  
```json
{  
    "success": true,  
    "data": {  
        "user_id": 9,  
        "balance": "1000.00"  
    },  
    "message": "Баланс пользователя успешно пополнен"  
}
``` 
 
2. **Списание средств**
**POST** `/api/withdraw` 
 
Тело запроса: 
```json
{  
  "user_id": 9,  
  "amount": 100,  
  "comment": "Списание ДС"  
} 
```

Возможные коды ответа 
200 — успешно списано 
404 — пользователь не найден 
409 — недостаточно средств 
422 — ошибка валидации 
 
структура ответа:  
```json
{  
    "success": true,  
    "data": {  
        "user_id": 9,  
        "balance": "900.00"  
    },  
    "message": "Списание прошло успешно"  
}  
```

3. **Перевод между пользователями**
**POST** `/api/transfer` 
 
Тело запроса:
```json  
{  
  "from_user_id": 9,  
  "to_user_id": 15,  
  "amount": 100,  
  "comment": "Перевод другу"  
}  
```  
 
Влзможные коды ответа:  
200 — Перевод успешно выполнен 
404 — Отправитель не найден 
409 — Недостаточно средств 
422 — Ошибка валидации 
 
структура ответа:  
```json  
{  
    "success": true,  
    "data": {  
        "sender_id": 9,  
        "sender_balance": "700.00",  
        "receiver_id": 15,  
        "receiver_balance": "200.00"  
    },  
    "message": "Перевод успешно выполнен"  
}  
```  
 
4. **Вывод баланса**
**GET** `/api/balance` 
 
Пример запроса:  
"http://127.0.0.1/api/balance?user_id=15"  
 
Влзможные коды ответа:  
200 — Баланс пользователя успешно получен  
404 — Пользователь не найден  
422 — Ошибка валидации  
 
структура ответа:  
```json
{  
    "success": true,  
    "data": {  
        "balance": "200.00"  
    },  
    "message": "Баланс пользователя успешно получен"  
}  
``` 
 
## Полезные комманды:
 
php artisan migrate:rollback      # откатить миграции  
php artisan optimize:clear        # очистить кеши  
php artisan route:list            # список маршрутов  
  
 
## Структуры таблиц:
 
users  
| Поле                    | Тип           | Описание                   | 
| ----------------------- | ------------- | -------------------------- | 
| id                      | bigint (PK)   | Идентификатор пользователя | 
| balance                 | decimal(12,2) | Баланс                     | 
| created_at / updated_at | timestamp     | Метки времени              | 
 
transactions  
| Поле                    | Тип           | Описание                                        | 
| ----------------------- | ------------- | ----------------------------------------------- | 
| id                      | bigint (PK)   | Идентификатор транзакции                        | 
| sender_id               | bigint (FK)   | Отправитель                                     | 
| receiver_id             | bigint (FK)   | Получатель                                      | 
| type                    | enum          | deposit / withdraw / transfer_in / transfer_out | 
| amount                  | decimal(12,2) | Сумма операции                                  | 
| comment                 | text          | Комментарий                                     | 
| created_at / updated_at | timestamp     | Метки времени                                   | 
 
## Автор:
Шадрин Данил Евгеньевич  
Проект создан в рамках тестового задания (Laravel + Docker + PostgreSQL).  
GitHub: github.com/Shadrin-Danil  
