<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\repositories;

use Besnovatyj\Oauth2\bridge\ClientEntity;
use Besnovatyj\Oauth2\entities\OAuth2Client;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

/**
 * Client Repository for League OAuth2 Server
 */
class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        $client = OAuth2Client::findOne(['client_id' => $clientIdentifier]);

        if ($client === null) {
            return null;
        }

        return new ClientEntity(
            $client->client_id,
            $client->client_secret,
            $client->redirect_uri,
            (bool)$client->is_confidential
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        $client = OAuth2Client::findOne(['client_id' => $clientIdentifier]);

        if ($client === null) {
            \Yii::error("OAuth2 Client not found: {$clientIdentifier}", __METHOD__);
            return false;
        }

        // Check if grant type is allowed for this client
        if ($grantType !== null && !empty($client->grant_types)) {
            $allowedGrants = $client->getGrantTypesArray();
            if (!in_array($grantType, $allowedGrants, true)) {
                \Yii::error("Grant type '{$grantType}' not allowed for client '{$clientIdentifier}'. Allowed: " . implode(', ', $allowedGrants), __METHOD__);
                return false;
            }
        }

        // If client is confidential, validate secret
        if ($client->isConfidential()) {
            $isValid = $clientSecret !== null && hash_equals($client->client_secret, $clientSecret);
            if (!$isValid) {
                \Yii::error("Client secret validation failed for: {$clientIdentifier}", __METHOD__);
            }
            return $isValid;
        }

        // Public client
        return true;
    }
}
