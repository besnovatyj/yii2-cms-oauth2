<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\repositories;

use Besnovatyj\Oauth2\bridge\ScopeEntity;
use Besnovatyj\Oauth2\entities\OAuth2Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

/**
 * Scope Repository for League OAuth2 Server
 */
class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        $scope = OAuth2Scope::findOne(['scope' => $identifier]);

        if ($scope === null) {
            return null;
        }

        $scopeEntity = new ScopeEntity();
        $scopeEntity->setIdentifier($identifier);

        return $scopeEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeScopes(
        array $scopes,
        string $grantType,
        ClientEntityInterface $clientEntity,
        ?string $userIdentifier = null,
        ?string $authCodeId = null
    ): array {
        // If no scopes are requested, return default scopes
        if (empty($scopes)) {
            $defaultScopes = OAuth2Scope::find()
                ->where(['is_default' => 1])
                ->all();

            $scopes = [];
            foreach ($defaultScopes as $scope) {
                $scopeEntity = new ScopeEntity();
                $scopeEntity->setIdentifier($scope->scope);
                $scopes[] = $scopeEntity;
            }
        }

        return $scopes;
    }
}
