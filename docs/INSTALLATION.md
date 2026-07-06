# Установка OAuth2 Server для Yii2

## Шаг 1: Установите зависимости

Выполните команду из директории `/app`:

```bash
cd /workspace/app
composer require league/oauth2-server:^9.0
composer require nyholm/psr7:^1.8
composer require nyholm/psr7-server:^1.1
```

**Примечание:** Эти пакеты совместимы с psr/http-message v2.0, поэтому конфликтов быть не должно.

## Шаг 2: Создайте таблицы БД

Выполните SQL из файла `schema.sql`:

```bash
# Войдите в контейнер MySQL или выполните локально
mysql -u root -p yii2cms < /workspace/app/common/components/oauth2/schema.sql
```

Или через Docker:

```bash
docker exec -i $(docker ps -qf "name=mysql") mysql -u root -p yii2cms < /workspace/app/common/components/oauth2/schema.sql
```

## Шаг 3: Сгенерируйте ключи для JWT

```bash
cd /workspace/app/common/components/oauth2
chmod +x generate-keys.sh
./generate-keys.sh ./keys
```

Добавьте в `.gitignore`:

```
app/common/components/oauth2/keys/
```

## Шаг 4: Сгенерируйте encryption key

Выполните в PHP:

```php
echo base64_encode(random_bytes(32));
```

Или в командной строке:

```bash
php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"
```

Сохраните полученный ключ для конфигурации.

## Шаг 5: Обновите конфигурацию REST приложения

Скопируйте пример конфигурации:

```bash
cp /workspace/app/rest/config/main-oauth2-example.php /workspace/app/rest/config/main.php
```

**ВАЖНО:** Замените в конфиге:
1. `encryptionKey` на сгенерированный на шаге 4
2. Удалите старую конфигурацию `oauth2` модуля от filsh

## Шаг 6: Обновите URL Manager

Добавьте в `app/rest/config/url-manager.php`:

```php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'POST oauth2/token' => 'oauth2/token',
        // ... остальные роуты
    ],
];
```

## Шаг 7: Создайте тестового клиента

```bash
cd /workspace/app
php common/components/oauth2/create-test-client.php
```

## Шаг 8: Тестирование

Получите access token:

```bash
curl -X POST http://localhost:8080/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=test-client" \
  -d "client_secret=test-secret" \
  -d "username=admin" \
  -d "password=admin" \
  -d "scope=basic"
```

Если все настроено правильно, вы получите ответ:

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "def50200..."
}
```

Используйте токен для доступа к защищенным ресурсам:

```bash
curl -X GET http://localhost:8080/api/protected \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

## Возможные проблемы

### Ошибка "Cannot load private key"

- Убедитесь, что файл `private.key` существует и доступен для чтения
- Проверьте права доступа: `chmod 600 keys/private.key`

### Ошибка "Invalid encryption key"

- Encryption key должен быть base64-encoded строкой длиной 44 символа
- Используйте `base64_encode(random_bytes(32))` для генерации

### Ошибка "Table 'oauth2_clients' doesn't exist"

- Выполните SQL из `schema.sql`
- Проверьте подключение к БД

### Конфликты зависимостей

Если возникают конфликты с psr/http-message:
- Убедитесь, что используете league/oauth2-server >= 9.0
- Проверьте версии nyholm пакетов (должны быть >= 1.8 для psr7, >= 1.1 для psr7-server)

## Удаление старого модуля

После успешной настройки можно удалить старый модуль:

```bash
composer remove filsh/yii2-oauth2-server
```

Удалите из кода все импорты и использования:
- `filsh\yii2\oauth2server\Module`
- `filsh\yii2\oauth2server\filters\*`
- `OAuth2\Storage\UserCredentialsInterface`

## Поддержка

При возникновении проблем:
1. Проверьте логи: `app/rest/runtime/logs/rest.log`
2. Включите debug режим в конфиге
3. Обратитесь к документации League OAuth2 Server: https://oauth2.thephpleague.com/
