<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Oauth2\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/**
 * Таблица access-токенов OAuth2.
 * Исходник: docs/schema.sql (таблица `oauth2_access_tokens`).
 *
 * 'm<YYMMDD_HHMMSS>_<Name>'
 */
class m260706_120001_create_oauth2_access_tokens_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%oauth2_access_tokens}}';

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
            'access_token' => $this->string(255)->notNull()
                ->comment('Значение access-токена (PK).'),
            'client_id' => $this->string(80)->notNull()
                ->comment('Клиент, которому выдан токен.'),
            'user_id' => $this->integer(11)->null()
                ->comment('Пользователь (null для client credentials).'),
            'expires_at' => $this->integer(11)->notNull()
                ->comment('Время истечения (unix timestamp).'),
            'scope' => $this->text()->null()
                ->comment('Выданные scopes (через пробел).'),
            'created_at' => $this->integer(11)->notNull()
                ->comment('Время создания (unix timestamp).'),
            'PRIMARY KEY ([[access_token]])',
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'OAuth2: access-токены');

        $this->createIndexes(static::TABLE_NAME, ['client_id']);
        $this->createIndexes(static::TABLE_NAME, ['user_id']);
        $this->createIndexes(static::TABLE_NAME, ['expires_at']);
    }

    /**
     * @throws NotSupportedException
     */
    public function safeDown(): void
    {
        parent::safeDown();
    }
}
