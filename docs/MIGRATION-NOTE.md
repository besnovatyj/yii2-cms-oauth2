# OAuth2 — задача переноса в composer-пакет

**Статус: ОТЛОЖЕНО** (решение 2026-07-02).

Компонент временно остаётся в `app/common/components/oauth2`
(namespace `common\components\oauth2`). Остальные SDK-слайсы `common/components`
уже вынесены в пакеты (`yii2-cms-contracts`, `yii2-cms-kernel`, `yii2-cms-themes`,
`debug-panel-modules`) — этот пока нет.

## Задача

Вынести OAuth2 в отдельный composer-пакет (условно `besnovatyj/yii2-cms-oauth2`,
namespace `Besnovatyj\OAuth2\`), как остальные модули: свой `composer.json`,
свой git-репозиторий, зависимости — `php` + `yii2` + `league/oauth2-server`
(+ `yii2-cms-contracts` для инверсии, см. ниже).

## Почему отложено

1. **Прямая связанность с внутренним `modules\user`** — пакет не может зависеть
   ни от `@common`, ни от внутреннего модуля. Мешающие точки:
   - `repositories/UserRepository.php` — `use modules\user\repositories\UserReadRepository;`
     и рантайм-резолв `Yii::$container->get(UserReadRepository::class)` (строка ~34).
   - `create-test-user.php` (тест-скрипт) — `use modules\user\entities\User;`.
2. **Фича nascent** — уровень rest-примера, в проде ещё не задействована
   (API пока нет, см. `AGENTS.md`). Вкладываться в вынос раньше реальной
   потребности нецелесообразно.

## Когда возвращаться

Когда `modules\user` станет пакетом **или** OAuth2 реально понадобится (появится API).

## План выноса (эскиз)

- Инвертировать зависимость от юзера через контракт **`UserProvider`** в
  `yii2-cms-contracts` (метод вида `getUserByCredentials(...)` / `findIdentity(...)`).
  `UserRepository` в oauth2 зависит от интерфейса; `modules\user` его реализует и
  регистрирует в DIC.
- Тест-скрипты (`create-test-user.php`, `create-test-client.php`) — вынести в
  `dev`-раздел пакета или переписать на контракт, убрав прямой `modules\user\entities\User`.
- Ключи (`keys/private.key`, `keys/public.key`) и БД-схема (`docs/schema.sql`) —
  не в пакет: генерируются/накатываются при установке (см. `docs/`).

Полная документация по самому OAuth2 — в `docs/`.
