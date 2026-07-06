<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\entities;

use yii\db\ActiveRecord;

/**
 * OAuth2 Access Token Entity
 *
 * @property string $access_token
 * @property string $client_id
 * @property int|null $user_id
 * @property int $expires_at
 * @property string|null $scope
 * @property int $created_at
 */
class OAuth2AccessToken extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%oauth2_access_tokens}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['access_token', 'client_id', 'expires_at'], 'required'],
            [['user_id', 'expires_at', 'created_at'], 'integer'],
            [['access_token'], 'string', 'max' => 255],
            [['client_id'], 'string', 'max' => 80],
            [['scope'], 'string'],
        ];
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < time();
    }

    /**
     * Get scopes as array
     */
    public function getScopesArray(): array
    {
        if (empty($this->scope)) {
            return [];
        }
        return explode(' ', $this->scope);
    }
}
