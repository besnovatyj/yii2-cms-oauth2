<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\entities;

use yii\db\ActiveRecord;

/**
 * OAuth2 Client Entity
 *
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 * @property string|null $grant_types
 * @property string|null $scope
 * @property int|null $user_id
 * @property int $is_confidential
 * @property int $created_at
 * @property int $updated_at
 */
class OAuth2Client extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%oauth2_clients}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['client_id', 'client_secret'], 'required'],
            [['user_id', 'is_confidential', 'created_at', 'updated_at'], 'integer'],
            [['client_id'], 'string', 'max' => 80],
            [['client_secret'], 'string', 'max' => 255],
            [['redirect_uri'], 'string', 'max' => 2000],
            [['grant_types', 'scope'], 'string'],
            [['is_confidential'], 'default', 'value' => 1],
        ];
    }

    /**
     * Check if client is confidential
     */
    public function isConfidential(): bool
    {
        return (bool)$this->is_confidential;
    }

    /**
     * Get grant types as array
     */
    public function getGrantTypesArray(): array
    {
        if (empty($this->grant_types)) {
            return [];
        }
        return explode(' ', $this->grant_types);
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
