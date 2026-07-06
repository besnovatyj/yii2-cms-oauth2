# OAuth2 Server - Быстрый старт

## Подготовка (5 минут)

### 1. Установите зависимости

```bash
cd /workspace/app
composer require league/oauth2-server:^9.0 nyholm/psr7:^1.8 nyholm/psr7-server:^1.1
```

### 2. Создайте БД таблицы

```bash
# Если используете Docker
docker exec -i $(docker ps -qf "name=mysql") mysql -u root -pYOUR_PASSWORD yii2cms < /workspace/app/common/components/oauth2/schema.sql

# Или локально
mysql -u root -p yii2cms < /workspace/app/common/components/oauth2/schema.sql
```

### 3. Сгенерируйте ключи

```bash
cd /workspace/app/common/components/oauth2
chmod +x generate-keys.sh
./generate-keys.sh ./keys
```

Добавьте в `.gitignore`:
```
app/common/components/oauth2/keys/
```

### 4. Сгенерируйте encryption key

```bash
php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"
```

Скопируйте результат.

## Настройка (3 минуты)

### 5. Обновите REST конфиг

Отредактируйте `/workspace/app/rest/config/main.php`:

```php
return [
    // ... существующий код ...
    'components' => [
        // ДОБАВЬТЕ ЭТИ ДВА КОМПОНЕНТА:
        'oauth2AuthServer' => [
            'class' => \common\components\oauth2\AuthorizationServer::class,
            'privateKeyPath' => '@app/../common/components/oauth2/keys/private.key',
            'privateKeyPassphrase' => null,
            'encryptionKey' => 'ВСТАВЬТЕ_СЮДА_КЛЮЧ_ИЗ_ШАГА_4',
            'accessTokenTTL' => 3600,
            'refreshTokenTTL' => 2592000,
        ],
        'oauth2ResourceServer' => [
            'class' => \common\components\oauth2\ResourceServer::class,
            'publicKeyPath' => '@app/../common/components/oauth2/keys/public.key',
        ],
        // ... остальные компоненты ...
    ],
];
```

### 6. Обновите URL Manager

В `/workspace/app/rest/config/url-manager.php`:

```php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'POST oauth2/token' => 'oauth2/token', // ДОБАВЬТЕ ЭТО
        // ... остальные роуты ...
    ],
];
```

### 7. Удалите старый OAuth2 модуль

Из `/workspace/app/rest/config/main.php` **УДАЛИТЕ**:

```php
// УДАЛИТЕ ЭТО:
'modules' => [
    'oauth2' => [
        'class' => 'filsh\yii2\oauth2server\Module',
        // ...
    ]
],
```

И **УДАЛИТЕ** в `as authenticator`:

```php
// УДАЛИТЕ ЭТО:
'as exceptionFilter' => [
    'class' => 'filsh\yii2\oauth2server\filters\ErrorToExceptionFilter',
],
```

## Тестирование (2 минуты)

### 8. Создайте тестового клиента

```bash
cd /workspace/app
php common/components/oauth2/create-test-client.php
```

### 9. Получите токен

Замените `YOUR_USERNAME` и `YOUR_PASSWORD` на реальные данные пользователя:

```bash
curl -X POST http://localhost:8080/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=test-client" \
  -d "client_secret=test-secret" \
  -d "username=YOUR_USERNAME" \
  -d "password=YOUR_PASSWORD" \
  -d "scope=basic"
```

**Успешный ответ:**

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1Qi...",
  "refresh_token": "def50200..."
}
```

### 10. Используйте токен

```bash
curl -X GET http://localhost:8080/api/your-endpoint \
  -H "Authorization: Bearer ВСТАВЬТЕ_ACCESS_TOKEN_СЮДА"
```

## Готово! 🎉

Ваш OAuth2 сервер работает. Теперь вы можете:

1. **Создавать новых клиентов** через БД или админ-панель
2. **Настроить дополнительные Grant Types** (см. README.md)
3. **Добавить RBAC** для контроля доступа
4. **Удалить старый модуль**: `composer remove filsh/yii2-oauth2-server`

## Частые проблемы

**401 Unauthorized при запросе токена:**
- Проверьте, что роут `oauth2/token` добавлен в `except` в `as authenticator`
- Убедитесь, что username/password правильные (username - это имя ПОЛЬЗОВАТЕЛЯ, не client_id!)
- Проверьте, что пользователь активен в БД (status = active)
- Убедитесь, что OAuth2 клиент создан в БД

**"Key file permissions are not correct (777)":**
- Это проблема WSL2/Docker - права доступа определяются неправильно
- Добавьте в конфиг: `'keyPermissionsCheck' => false` или `'keyPermissionsCheck' => !YII_DEBUG`

**"Cannot load private key":**
- Проверьте путь к ключу в конфиге
- Убедитесь, что файл существует: `ls -la app/common/components/oauth2/keys/`
- Проверьте, что алиас `@app` правильно резолвится

**"Invalid encryption key":**
- Ключ должен быть base64-encoded, длина 44 символа
- Заново сгенерируйте: `php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"`

**Поддерживаемые форматы:**
- ✅ **JSON** (как в старом filsh): `Content-Type: application/json`
- ✅ **Form-urlencoded** (стандарт OAuth2): `Content-Type: application/x-www-form-urlencoded`
- Оба формата работают одинаково!
- Примеры для TypeScript: см. `typescript-examples.ts`

## Документация

- Полная документация: [README.md](README.md)
- Детальная установка: [INSTALLATION.md](INSTALLATION.md)
- League OAuth2 Server: https://oauth2.thephpleague.com/
