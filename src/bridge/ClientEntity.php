<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\bridge;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * Client Entity for League OAuth2 Server
 */
class ClientEntity implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    /**
     * @var string Client secret (not in trait)
     */
    private string $secret;

    /**
     * Constructor
     *
     * @param string $identifier Client identifier
     * @param string $secret Client secret
     * @param string|string[] $redirectUri Redirect URI(s)
     * @param bool $isConfidential Is confidential client
     */
    public function __construct(
        string $identifier,
        string $secret,
        string|array $redirectUri,
        bool $isConfidential = true
    ) {
        $this->identifier = $identifier;
        $this->secret = $secret;
        $this->redirectUri = is_array($redirectUri) ? $redirectUri : [$redirectUri];
        $this->isConfidential = $isConfidential;
    }

    /**
     * Get client secret
     */
    public function getSecret(): string
    {
        return $this->secret;
    }
}
