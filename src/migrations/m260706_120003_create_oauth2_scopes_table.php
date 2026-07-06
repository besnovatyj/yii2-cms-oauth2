<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Oauth2\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/**
 * Таблица scopes OAuth2 + сидирование дефолтных областей (basic/read/write).
 * Исходник: docs/schema.sql (таблица `oauth2_scopes` + INSERT дефолтных scopes).
 *
 * 'm<YYMMDD_HHMMSS>_<Name>'
 */
class m260706_120003_create_oauth2_scopes_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%oauth2_scopes}}';

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
            'scope' => $this->string(80)->notNull()
                ->comment('Идентификатор области доступа (PK).'),
            'description' => $this->string(255)->null()
                ->comment('Человекочитаемое описание.'),
            'is_default' => $this->tinyInteger(1)->notNull()->defaultValue(0)
                ->comment('Выдаётся ли по умолчанию.'),
            'created_at' => $this->integer(11)->notNull()
                ->comment('Время создания (unix timestamp).'),
            'PRIMARY KEY ([[scope]])',
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'OAuth2: области доступа (scopes)');

        // Дефолтные scopes (перенос INSERT из docs/schema.sql).
        $now = time();
        $this->batchInsert(static::TABLE_NAME, ['scope', 'description', 'is_default', 'created_at'], [
            ['basic', 'Basic access to user profile', 1, $now],
            ['read', 'Read access to resources', 0, $now],
            ['write', 'Write access to resources', 0, $now],
        ]);
    }

    /**
     * @throws NotSupportedException
     */
    public function safeDown(): void
    {
        parent::safeDown();
    }
}
