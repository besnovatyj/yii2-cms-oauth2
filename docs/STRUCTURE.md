# Структура OAuth2 Server для Yii2

## Полная структура файлов

```
/app/common/components/oauth2/
│
├── entities/                           # ActiveRecord сущности для хранения данных
│   ├── OAuth2Client.php               # Клиенты OAuth2 (приложения)
│   ├── OAuth2AccessToken.php          # Access токены
│   ├── OAuth2RefreshToken.php         # Refresh токены
│   └── OAuth2Scope.php                # Scopes (разрешения)
│
├── repositories/                       # Репозитории для League OAuth2 Server
│   ├── ClientRepository.php           # Управление клиентами
│   ├── AccessTokenRepository.php      # Управление access токенами
│   ├── RefreshTokenRepository.php     # Управление refresh токенами
│   ├── ScopeRepository.php            # Управление scopes
│   └── UserRepository.php             # Валидация учетных данных пользователей
│
├── bridge/                            # Мосты между Yii2 и PSR-7/League
│   ├── ClientEntity.php               # Entity клиента для League
│   ├── AccessTokenEntity.php          # Entity access токена для League
│   ├── RefreshTokenEntity.php         # Entity refresh токена для League
│   ├── ScopeEntity.php                # Entity scope для League
│   ├── UserEntity.php                 # Entity пользователя для League
│   └── Psr7Factory.php                # Конвертер Yii2 Request/Response <-> PSR-7
│
├── filters/                           # Фильтры аутентификации
│   └── OAuth2BearerAuth.php           # Bearer токен аутентификация
│
├── AuthorizationServer.php            # Yii2 компонент Authorization Server
├── ResourceServer.php                 # Yii2 компонент Resource Server
│
├── keys/                              # JWT ключи (не коммитятся!)
│   ├── private.key                    # Приватный ключ для подписи токенов
│   └── public.key                     # Публичный ключ для валидации токенов
│
├── schema.sql                         # SQL схема таблиц БД
├── generate-keys.sh                   # Скрипт генерации ключей
├── create-test-client.php             # Скрипт создания тестового клиента
│
├── README.md                          # Основная документация
├── INSTALLATION.md                    # Подробная инструкция установки
├── QUICKSTART.md                      # Быстрый старт
└── STRUCTURE.md                       # Этот файл
```

## Назначение каждого компонента

### Entities (ActiveRecord модели)

**OAuth2Client** - хранит информацию о клиентских приложениях:
- client_id, client_secret
- redirect_uri (для Authorization Code Grant)
- grant_types (какие типы грантов разрешены)
- scopes (какие разрешения доступны)

**OAuth2AccessToken** - хранит выданные access токены:
- access_token (JWT токен)
- client_id, user_id
- expires_at (время истечения)
- scope (активные разрешения)

**OAuth2RefreshToken** - хранит refresh токены:
- refresh_token
- связь с access_token
- expires_at (обычно дольше, чем у access token)

**OAuth2Scope** - определяет доступные разрешения:
- scope (название: basic, read, write и т.д.)
- description (описание разрешения)
- is_default (выдается ли по умолчанию)

### Repositories (Интерфейсы League OAuth2 Server)

Репозитории имплементируют интерфейсы League OAuth2 Server и адаптируют их к Yii2 ActiveRecord.

**ClientRepository** - проверяет и получает клиентов
**AccessTokenRepository** - создает и управляет access токенами
**RefreshTokenRepository** - создает и управляет refresh токенами
**ScopeRepository** - управляет scopes и определяет дефолтные
**UserRepository** - валидирует учетные данные для Password Grant

### Bridge (Адаптеры)

**Entity классы** - реализуют интерфейсы League OAuth2 Server
**Psr7Factory** - конвертирует между Yii2 Request/Response и PSR-7

### Компоненты Yii2

**AuthorizationServer** - основной компонент для выдачи токенов:
- Регистрируется как `oauth2AuthServer` в конфиге
- Настраивает Grant Types (Password, Refresh Token)
- Управляет временем жизни токенов

**ResourceServer** - компонент для валидации токенов:
- Регистрируется как `oauth2ResourceServer` в конфиге
- Проверяет валидность JWT токенов
- Используется в Identity для аутентификации

### Контроллеры

**OAuth2Controller** (в /app/rest/controllers/):
- Обрабатывает POST /oauth2/token
- Выдает access и refresh токены
- Обрабатывает все типы grant'ов

## Поток данных

### 1. Получение токена (Password Grant)

```
Client -> POST /oauth2/token
    ↓
OAuth2Controller::actionToken()
    ↓
AuthorizationServer->respondToAccessTokenRequest()
    ↓
UserRepository->getUserEntityByUserCredentials()
    ↓ (валидация)
UserReadRepository->findActiveByUsername()
    ↓
AccessTokenRepository->persistNewAccessToken()
    ↓
RefreshTokenRepository->persistNewRefreshToken()
    ↓
Response: { access_token, refresh_token }
```

### 2. Валидация токена

```
Client -> GET /api/endpoint (Authorization: Bearer xxx)
    ↓
Yii2 Auth Filter
    ↓
User->loginByAccessToken()
    ↓
Identity::findIdentityByAccessToken()
    ↓
ResourceServer->validateAuthenticatedRequest()
    ↓ (проверка JWT)
AccessTokenRepository->isAccessTokenRevoked()
    ↓
Identity возвращается, пользователь аутентифицирован
```

## База данных

### Таблицы

```
oauth2_clients          - Клиентские приложения
oauth2_access_tokens    - Выданные access токены
oauth2_refresh_tokens   - Выданные refresh токены
oauth2_scopes           - Доступные разрешения
```

### Индексы

Созданы индексы на:
- client_id, user_id (для быстрого поиска)
- expires_at (для очистки истекших токенов)

## Безопасность

1. **Приватный ключ** - используется только в AuthorizationServer для подписи токенов
2. **Публичный ключ** - используется в ResourceServer для валидации токенов
3. **Encryption key** - для шифрования Refresh токенов
4. **Client secrets** - хешируются через Yii::$app->security->generatePasswordHash()

## Масштабирование

### Для высоконагруженных систем:

1. **Кеширование публичного ключа** - ResourceServer может закешировать ключ
2. **Очистка старых токенов** - добавьте cron job для удаления истекших токенов
3. **Redis для сессий** - если используете stateful аутентификацию
4. **Load balancing** - JWT токены stateless, работают на любом сервере

### Пример cron задачи для очистки:

```php
// console/controllers/OAuth2Controller.php
public function actionCleanupTokens(): void
{
    $now = time();

    OAuth2AccessToken::deleteAll(['<', 'expires_at', $now]);
    OAuth2RefreshToken::deleteAll(['<', 'expires_at', $now]);

    echo "Expired tokens cleaned up.\n";
}
```

## Расширение функционала

### Добавление нового Grant Type (например, Authorization Code):

```php
// В AuthorizationServer::initializeServer()

use League\OAuth2\Server\Grant\AuthCodeGrant;

$authCodeGrant = new AuthCodeGrant(
    $authCodeRepository,  // нужно создать AuthCodeRepository
    $refreshTokenRepository,
    new \DateInterval('PT10M') // authorization code TTL
);
$authCodeGrant->setRefreshTokenTTL(new \DateInterval('P1M'));

$this->_server->enableGrantType(
    $authCodeGrant,
    new \DateInterval('PT1H') // access token TTL
);
```

### Добавление кастомных scopes:

```sql
INSERT INTO oauth2_scopes (scope, description, is_default, created_at)
VALUES ('admin', 'Administrative access', 0, UNIX_TIMESTAMP());
```

## Мониторинг

### Логирование:

Все ошибки OAuth2 логируются в:
- `/app/rest/runtime/logs/rest.log`

### Метрики для мониторинга:

1. Количество выданных токенов за период
2. Количество неудачных попыток аутентификации
3. Количество истекших токенов
4. Использование разных Grant Types

```sql
-- Статистика выданных токенов
SELECT DATE(FROM_UNIXTIME(created_at)) as date, COUNT(*) as count
FROM oauth2_access_tokens
GROUP BY date
ORDER BY date DESC;
```

## Дополнительные ресурсы

- [League OAuth2 Server Docs](https://oauth2.thephpleague.com/)
- [RFC 6749 - OAuth 2.0](https://tools.ietf.org/html/rfc6749)
- [JWT.io - JWT Inspector](https://jwt.io/)
