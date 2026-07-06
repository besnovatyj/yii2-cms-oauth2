# OAuth2 Server для Yii2

Обертка для League/OAuth2-Server, адаптированная для использования с Yii2.

## Установка

### 1. Установите зависимости

Убедитесь, что установлены следующие пакеты:

```bash
composer require league/oauth2-server
composer require nyholm/psr7
composer require nyholm/psr7-server
```

### 2. Создайте таблицы БД

Выполните SQL из файла `schema.sql`:

```bash
mysql -u your_user -p your_database < app/common/components/oauth2/schema.sql
```

### 3. Генерация ключей

Сгенерируйте приватный и публичный ключи для JWT:

```bash
cd app/common/components/oauth2
chmod +x generate-keys.sh
./generate-keys.sh /path/to/keys
```

**ВАЖНО:** Добавьте директорию с ключами в `.gitignore`!

### 4. Конфигурация REST приложения

Обновите `app/rest/config/main.php`:

```php
return [
    // ...
    'components' => [
        // OAuth2 Authorization Server
        'oauth2AuthServer' => [
            'class' => \common\components\oauth2\AuthorizationServer::class,
            'privateKeyPath' => '@app/../common/components/oauth2/keys/private.key',
            'privateKeyPassphrase' => null, // если ключ без пароля
            'encryptionKey' => 'YOUR_ENCRYPTION_KEY_HERE', // генерируйте: base64_encode(random_bytes(32))
            'accessTokenTTL' => 3600, // 1 час
            'refreshTokenTTL' => 2592000, // 30 дней
        ],

        // OAuth2 Resource Server
        'oauth2ResourceServer' => [
            'class' => \common\components\oauth2\ResourceServer::class,
            'publicKeyPath' => '@app/../common/components/oauth2/keys/public.key',
        ],

        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => \modules\user\entities\Identity::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
    ],
];
```

### 5. Настройка URL Manager

Добавьте роут для OAuth2 контроллера в `app/rest/config/url-manager.php`:

```php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'POST oauth2/token' => 'oauth2/token',
        // ... другие роуты
    ],
];
```

### 6. Удалите старый модуль OAuth2

Из `app/rest/config/main.php` удалите:

```php
'modules' => [
    'oauth2' => [
        'class' => 'filsh\yii2\oauth2server\Module',
        // ...
    ]
],
```

И удалите фильтры из `as authenticator` и `as exceptionFilter`, которые используют старый модуль.

## Использование

### Создание OAuth2 клиента

```php
use common\components\oauth2\entities\OAuth2Client;
use Yii;

$client = new OAuth2Client();
$client->client_id = 'my-app';
$client->client_secret = Yii::$app->security->generatePasswordHash('secret');
$client->redirect_uri = 'https://example.com/callback';
$client->grant_types = 'password refresh_token';
$client->scope = 'basic read write';
$client->is_confidential = 1;
$client->created_at = time();
$client->updated_at = time();
$client->save();
```

### Получение access token (Password Grant)

```bash
curl -X POST http://your-api.com/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=my-app" \
  -d "client_secret=secret" \
  -d "username=user@example.com" \
  -d "password=password" \
  -d "scope=basic"
```

Ответ:

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "def50200..."
}
```

### Обновление access token (Refresh Token Grant)

```bash
curl -X POST http://your-api.com/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=refresh_token" \
  -d "client_id=my-app" \
  -d "client_secret=secret" \
  -d "refresh_token=def50200..."
```

### Использование access token

```bash
curl -X GET http://your-api.com/api/protected-resource \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

Или через query параметр:

```bash
curl -X GET "http://your-api.com/api/protected-resource?accessToken=eyJ0eXAiOiJKV1QiLCJhbGc..."
```

## Структура

```
/app/common/components/oauth2/
├── entities/              # ActiveRecord сущности
│   ├── OAuth2Client.php
│   ├── OAuth2AccessToken.php
│   ├── OAuth2RefreshToken.php
│   └── OAuth2Scope.php
├── repositories/          # Репозитории для League OAuth2 Server
│   ├── ClientRepository.php
│   ├── AccessTokenRepository.php
│   ├── RefreshTokenRepository.php
│   ├── ScopeRepository.php
│   └── UserRepository.php
├── bridge/                # Мосты между Yii2 и PSR-7
│   ├── ClientEntity.php
│   ├── AccessTokenEntity.php
│   ├── RefreshTokenEntity.php
│   ├── ScopeEntity.php
│   ├── UserEntity.php
│   └── Psr7Factory.php
├── AuthorizationServer.php  # Yii2 компонент для Authorization Server
├── ResourceServer.php        # Yii2 компонент для Resource Server
├── schema.sql               # SQL схема таблиц
├── generate-keys.sh         # Скрипт генерации ключей
└── README.md                # Документация
```

## Дополнительные Grant Types

Для добавления других типов грантов (Authorization Code, Client Credentials, Implicit) см. документацию League OAuth2 Server:
https://oauth2.thephpleague.com/

## Безопасность

1. **Храните ключи в безопасности** - никогда не коммитьте их в репозиторий
2. **Используйте HTTPS** - OAuth2 требует защищенного соединения
3. **Генерируйте надежные encryption ключи** - используйте `base64_encode(random_bytes(32))`
4. **Хешируйте client secrets** - используйте `Yii::$app->security->generatePasswordHash()`
5. **Ротируйте refresh токены** - настроено по умолчанию

## Поддержка

Для вопросов и проблем обращайтесь к документации:
- [League OAuth2 Server](https://oauth2.thephpleague.com/)
- [Yii2 Framework](https://www.yiiframework.com/doc/guide/2.0/en)
