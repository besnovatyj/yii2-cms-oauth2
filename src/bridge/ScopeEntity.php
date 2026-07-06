<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\bridge;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

/**
 * Scope Entity for League OAuth2 Server
 */
class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait, ScopeTrait;

    /**
     * Serialize the scope to a string for JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): mixed
    {
        return $this->getIdentifier();
    }
}
