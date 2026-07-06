<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\repositories;

use Besnovatyj\Oauth2\bridge\RefreshTokenEntity;
use Besnovatyj\Oauth2\entities\OAuth2RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

/**
 * Refresh Token Repository for League OAuth2 Server
 */
class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $token = new OAuth2RefreshToken();
        $token->refresh_token = $refreshTokenEntity->getIdentifier();
        $token->access_token = $refreshTokenEntity->getAccessToken()->getIdentifier();
        $token->client_id = $refreshTokenEntity->getAccessToken()->getClient()->getIdentifier();
        $token->user_id = $refreshTokenEntity->getAccessToken()->getUserIdentifier();
        $token->expires_at = $refreshTokenEntity->getExpiryDateTime()->getTimestamp();

        $scopes = [];
        foreach ($refreshTokenEntity->getAccessToken()->getScopes() as $scope) {
            $scopes[] = $scope->getIdentifier();
        }
        $token->scope = implode(' ', $scopes);
        $token->created_at = time();

        $token->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken(string $tokenId): void
    {
        OAuth2RefreshToken::deleteAll(['refresh_token' => $tokenId]);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked(string $tokenId): bool
    {
        $token = OAuth2RefreshToken::findOne(['refresh_token' => $tokenId]);

        if ($token === null) {
            return true;
        }

        return $token->isExpired();
    }
}
