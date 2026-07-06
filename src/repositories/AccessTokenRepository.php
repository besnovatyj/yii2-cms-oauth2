<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\repositories;

use Besnovatyj\Oauth2\bridge\AccessTokenEntity;
use Besnovatyj\Oauth2\entities\OAuth2AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

/**
 * Access Token Repository for League OAuth2 Server
 */
class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        mixed $userIdentifier = null
    ): AccessTokenEntityInterface {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $token = new OAuth2AccessToken();
        $token->access_token = $accessTokenEntity->getIdentifier();
        $token->client_id = $accessTokenEntity->getClient()->getIdentifier();
        $token->user_id = $accessTokenEntity->getUserIdentifier();
        $token->expires_at = $accessTokenEntity->getExpiryDateTime()->getTimestamp();

        $scopes = [];
        foreach ($accessTokenEntity->getScopes() as $scope) {
            $scopes[] = $scope->getIdentifier();
        }
        $token->scope = implode(' ', $scopes);
        $token->created_at = time();

        $token->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken(string $tokenId): void
    {
        OAuth2AccessToken::deleteAll(['access_token' => $tokenId]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked(string $tokenId): bool
    {
        $token = OAuth2AccessToken::findOne(['access_token' => $tokenId]);

        if ($token === null) {
            return true;
        }

        return $token->isExpired();
    }
}
