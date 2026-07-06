# Настройка OAuth2 - Финальные шаги

## Шаг 1: Создайте OAuth2 клиента в БД

Выполните SQL:

```sql
-- Удалите старые записи (если есть)
DELETE FROM oauth2_clients WHERE client_id = 'test-client';

-- Создайте нового клиента
INSERT INTO oauth2_clients
(client_id, client_secret, redirect_uri, grant_types, scope, user_id, is_confidential, created_at, updated_at)
VALUES
('test-client', 'test-secret', 'http://localhost/callback', 'password refresh_token', 'basic read write', NULL, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Проверьте
SELECT client_id, grant_types, scope, is_confidential FROM oauth2_clients WHERE client_id = 'test-client';
```

**ВАЖНО:** `grant_types` должно содержать **'password'** (через пробел, если несколько)

## Шаг 2: Проверьте, что пользователь существует

```sql
SELECT id, username, email, status FROM user_users WHERE username = 'root';
```

Если пользователя нет, создайте его или используйте существующего.

## Шаг 3: Проверьте конфигурацию

В `/workspace/app/rest/config/main.php` должно быть:

```php
'as authenticator' => [
    'except' => ['site/index', 'o-auth2/token'], // ← именно o-auth2!
],

'as access' => [
    'except' => ['site/index', 'o-auth2/token'], // ← именно o-auth2!
],

'oauth2AuthServer' => [
    'class' => \common\components\oauth2\AuthorizationServer::class,
    'privateKeyPath' => '@app/../common/components/oauth2/keys/private.key',
    'encryptionKey' => 'ваш_ключ',
    'keyPermissionsCheck' => false, // ← для WSL2/Docker
],
```

## Шаг 4: Выполните запрос

Используйте `/workspace/http/oauth2-token.http` или curl:

```bash
curl -X POST https://rest.yii2-cms.docker.localhost/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=test-client" \
  -d "client_secret=test-secret" \
  -d "username=root" \
  -d "password=ВАШ_ПАРОЛЬ_ПОЛЬЗОВАТЕЛЯ" \
  -d "scope=basic"
```

## Шаг 5: Проверьте логи

Если не работает, смотрите логи:

```bash
tail -f /workspace/app/rest/runtime/logs/rest.log
```

Ошибки теперь более информативные:
- "OAuth2 Client not found" - клиента нет в БД
- "Grant type 'password' not allowed" - grant_types не содержит 'password'
- "Client secret validation failed" - неверный client_secret

## Ожидаемый результат

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "def50200..."
}
```

## Частые ошибки

### "Grant type is not supported"

**Причины:**
1. OAuth2 клиента нет в БД
2. В `grant_types` нет 'password'
3. Используется неправильный grant_type в запросе

**Решение:**
```sql
-- Проверьте grant_types
SELECT client_id, grant_types FROM oauth2_clients WHERE client_id = 'test-client';

-- Должно вернуть: 'password refresh_token'

-- Если нет, обновите:
UPDATE oauth2_clients SET grant_types = 'password refresh_token' WHERE client_id = 'test-client';
```

### "Invalid credentials"

**Причины:**
1. Неверный username/password пользователя (НЕ client_id/client_secret!)
2. Пользователь неактивен (status != active)

**Решение:**
```sql
-- Проверьте пользователя
SELECT id, username, status FROM user_users WHERE username = 'root';

-- Если status != 10 (active), обновите:
UPDATE user_users SET status = 10 WHERE username = 'root';
```

### "Client authentication failed"

**Причины:**
1. Неверный client_secret
2. В БД client_secret захеширован, а в запросе передается plain text

**Решение:**
```sql
-- Проверьте, что client_secret НЕ захеширован (для тестов)
SELECT client_id, client_secret FROM oauth2_clients WHERE client_id = 'test-client';

-- Должно вернуть: 'test-secret' (не хеш!)

-- Если хеш, обновите:
UPDATE oauth2_clients SET client_secret = 'test-secret' WHERE client_id = 'test-client';
```

**Примечание:** В production используйте хеширование и обновите логику проверки в ClientRepository.
