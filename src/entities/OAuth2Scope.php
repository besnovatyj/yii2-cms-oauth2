<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\entities;

use yii\db\ActiveRecord;

/**
 * OAuth2 Scope Entity
 *
 * @property string $scope
 * @property string|null $description
 * @property int $is_default
 * @property int $created_at
 */
class OAuth2Scope extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%oauth2_scopes}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['scope'], 'required'],
            [['scope'], 'string', 'max' => 80],
            [['description'], 'string', 'max' => 255],
            [['is_default', 'created_at'], 'integer'],
            [['is_default'], 'default', 'value' => 0],
        ];
    }

    /**
     * Check if scope is default
     */
    public function isDefault(): bool
    {
        return (bool)$this->is_default;
    }
}
