<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2;

use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesMigrations;

/**
 * Модуль OAuth2-сервера (фундамент REST-слоя).
 *
 * Предоставляет: ActiveRecord-сущности и репозитории токенов/клиентов/scopes, bridge-адаптеры
 * под league/oauth2-server, компоненты {@see AuthorizationServer} (выдача токенов) и
 * {@see ResourceServer} (валидация), а также фильтр {@see filters\OAuth2BearerAuth}.
 *
 * Компоненты `oauth2AuthServer` / `oauth2ResourceServer` монтируются в конфиге приложения
 * (composition root, напр. `app/rest/config/main.php`) — модуль сам их не регистрирует.
 * Модуль объявляет только миграции БД (таблицы oauth2_*) для установки через modman.
 */
class Module extends CmsModule implements
    DeclaresModule, ProvidesMigrations
{
    public const bool EDITABLE = true;
    public const string MODULE_ID = 'Oauth2';

    public static function moduleId(): string { return self::MODULE_ID; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function moduleConfig(): array { return require __DIR__ . '/config/config.php'; }
    public static function migrationPath(): string { return __DIR__ . '/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__ . '\\migrations'; }
}
