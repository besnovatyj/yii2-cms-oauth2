# besnovatyj/yii2-cms-oauth2

OAuth2-сервер для REST-слоя Yii2 CMS — тонкая интеграция библиотеки
[`league/oauth2-server`](https://oauth2.thephpleague.com/) в Yii2. Отвечает за **выдачу**
токенов (grant types: `password`, `refresh_token`) и **валидацию** Bearer-токенов на входящих
REST-запросах.

Это инфраструктурный фундамент REST: от него зависят REST-контроллеры любых модулей
(по аналогии с тем, как backend/frontend-контроллеры зависят от своих приложений).

## Состав

- `AuthorizationServer` / `ResourceServer` — Yii-компоненты-обёртки над league (выдача / валидация).
- `bridge/` — адаптеры под интерфейсы league (`AccessTokenEntity`, `ClientEntity`,
  `RefreshTokenEntity`, `ScopeEntity`, `UserEntity`, `Psr7Factory` — мост Yii ↔ PSR-7).
- `entities/` — ActiveRecord под таблицы `oauth2_*` (клиенты, access/refresh токены, scopes).
- `repositories/` — реализации репозиториев league.
- `filters/OAuth2BearerAuth` — Yii-фильтр авторизации для контроллеров.
- `migrations/` — схема БД (таблицы `oauth2_*` + дефолтные scopes), устанавливается через modman.

## Установка

```bash
composer require besnovatyj/yii2-cms-oauth2
```

Миграции накатываются менеджером модулей (modman) при установке. Затем:

1. **Сгенерировать ключи** (в репозиторий не входят, см. `docs/keys/README.md`):
   ```bash
   bash docs/generate-keys.sh
   ```
2. **Смонтировать компоненты в конфиге приложения** (composition root, напр. `app/rest/config/main.php`):
   ```php
   'components' => [
       'oauth2AuthServer' => [
           'class' => \Besnovatyj\Oauth2\AuthorizationServer::class,
           'privateKeyPath' => '@app/.../keys/private.key',
           'encryptionKey'  => '...', // base64_encode(random_bytes(32)); в прод — Docker Secrets
           'accessTokenTTL' => 3600,
           'refreshTokenTTL' => 2592000,
           'keyPermissionsCheck' => !YII_DEBUG,
       ],
       'oauth2ResourceServer' => [
           'class' => \Besnovatyj\Oauth2\ResourceServer::class,
           'publicKeyPath' => '@app/.../keys/public.key',
           'keyPermissionsCheck' => !YII_DEBUG,
       ],
   ],
   ```
3. Endpoint выдачи токена (`POST /oauth2/token`) — контроллер в REST-приложении
   (см. пример `app/rest/controllers/OAuth2Controller.php`).

Полная документация, примеры запросов, SQL и TypeScript-клиент — в `docs/`.

## ⚠️ Остаточная зависимость от модуля User

`src/repositories/UserRepository.php` (реализация «password grant») пока ссылается напрямую на
`Besnovatyj\User\repositories\UserReadRepository` — резолвится через DI-контейнер
(`Yii::$container->get(...)`). Это единственная точка связи с ядром/модулем User.

**План развязки** (см. `docs/MIGRATION-NOTE.md` и общий отчёт `GITHUB_MIGRATION_READINESS.md`, раздел B6):
инвертировать через контракт `UserProvider` в `yii2-cms-contracts` (метод вида
`findActiveByUsername()` / `validateCredentials()`), либо перенести этот репозиторий в пакет
`besnovatyj/yii2-cms-user` при его выделении. До этого пакет корректно работает в составе
приложения, где `Besnovatyj\User` доступен, но **автономно (без User) собирается, но не функционирует
password-grant**.

Тот же residual-момент в dev-скрипте `docs/dev/create-test-user.php` (`Besnovatyj\User\entities\User`).

## Зависимости

`php >=8.4`, `yiisoft/yii2`, `league/oauth2-server ^9`, `nyholm/psr7`, `nyholm/psr7-server`,
`psr/http-message`, `besnovatyj/yii2-cms-kernel`, `besnovatyj/yii2-cms-contracts`.

## Лицензия

MIT.
