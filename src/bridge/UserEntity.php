<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\bridge;

use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * User Entity for League OAuth2 Server
 */
class UserEntity implements UserEntityInterface
{
    /**
     * @var string|int
     */
    private string|int $identifier;

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Set the user identifier
     */
    public function setIdentifier(string|int $identifier): void
    {
        $this->identifier = $identifier;
    }
}
