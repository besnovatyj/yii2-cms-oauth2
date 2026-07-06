<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Oauth2\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/**
 * Таблица OAuth2-клиентов (client_id + secret + разрешённые grant types / scopes).
 * Исходник: docs/schema.sql (таблица `oauth2_clients`).
 *
 * 'm<YYMMDD_HHMMSS>_<Name>'
 */
class m260706_120000_create_oauth2_clients_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%oauth2_clients}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'client_id' => $this->string(80)->notNull()
                ->comment('Идентификатор клиента (PK).'),
            'client_secret' => $this->string(255)->notNull()
                ->comment('Секрет клиента (хэш).'),
            'redirect_uri' => $this->string(2000)->null()
                ->comment('Redirect URI (может быть несколько через пробел).'),
            'grant_types' => $this->text()->null()
                ->comment('Разрешённые grant types (через пробел).'),
            'scope' => $this->text()->null()
                ->comment('Разрешённые scopes (через пробел).'),
            'user_id' => $this->integer(11)->null()
                ->comment('Владелец клиента (опционально).'),
            'is_confidential' => $this->tinyInteger(1)->notNull()->defaultValue(1)
                ->comment('Конфиденциальный клиент (проверяется secret).'),
            'created_at' => $this->integer(11)->notNull()
                ->comment('Время создания (unix timestamp).'),
            'updated_at' => $this->integer(11)->notNull()
                ->comment('Время обновления (unix timestamp).'),
            'PRIMARY KEY ([[client_id]])',
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'OAuth2: клиенты');

        $this->createIndexes(static::TABLE_NAME, ['user_id']);
    }

    /**
     * @throws NotSupportedException
     */
    public function safeDown(): void
    {
        parent::safeDown();
    }
}
