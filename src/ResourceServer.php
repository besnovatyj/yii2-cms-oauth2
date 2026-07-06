<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2;

use Besnovatyj\Oauth2\repositories\AccessTokenRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\ResourceServer as LeagueResourceServer;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Resource Server Component for Yii2 (валидация токенов)
 *
 * @property-read LeagueResourceServer $server
 */
class ResourceServer extends Component
{
    /**
     * @var string Path to public key
     */
    public string $publicKeyPath;

    /**
     * @var bool Check key file permissions (disable for WSL2/Docker)
     */
    public bool $keyPermissionsCheck = true;

    /**
     * @var LeagueResourceServer
     */
    private LeagueResourceServer $_server;

    /**
     * @var AccessTokenRepository
     */
    private AccessTokenRepository $_accessTokenRepository;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->publicKeyPath)) {
            throw new InvalidConfigException('Public key path must be set');
        }

        $this->_accessTokenRepository = new AccessTokenRepository();

        // Create CryptKey with optional permissions check
        $publicKey = new CryptKey(
            \Yii::getAlias($this->publicKeyPath),
            null, // no passphrase for public key
            $this->keyPermissionsCheck
        );

        $this->_server = new LeagueResourceServer(
            $this->_accessTokenRepository,
            $publicKey
        );
    }

    /**
     * Get League Resource Server instance
     */
    public function getServer(): LeagueResourceServer
    {
        return $this->_server;
    }

    /**
     * Get Access Token Repository
     */
    public function getAccessTokenRepository(): AccessTokenRepository
    {
        return $this->_accessTokenRepository;
    }
}
