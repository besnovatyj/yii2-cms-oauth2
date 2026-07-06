<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2;

use Besnovatyj\Oauth2\repositories\AccessTokenRepository;
use Besnovatyj\Oauth2\repositories\ClientRepository;
use Besnovatyj\Oauth2\repositories\RefreshTokenRepository;
use Besnovatyj\Oauth2\repositories\ScopeRepository;
use Besnovatyj\Oauth2\repositories\UserRepository;
use DateInterval;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Authorization Server Component for Yii2 (выдача токенов)
 *
 * @property-read LeagueAuthorizationServer $server
 */
class AuthorizationServer extends Component
{
    /**
     * @var string Path to private key
     */
    public string $privateKeyPath;

    /**
     * @var string|null Private key passphrase
     */
    public ?string $privateKeyPassphrase = null;

    /**
     * @var string Encryption key (base64 encoded)
     */
    public string $encryptionKey;

    /**
     * @var int Access token TTL in seconds (default: 1 hour)
     */
    public int $accessTokenTTL = 3600;

    /**
     * @var int Refresh token TTL in seconds (default: 30 days)
     */
    public int $refreshTokenTTL = 2592000;

    /**
     * @var bool Check key file permissions (disable for WSL2/Docker)
     */
    public bool $keyPermissionsCheck = true;

    /**
     * @var LeagueAuthorizationServer
     */
    private LeagueAuthorizationServer $_server;

    /**
     * @var ClientRepository
     */
    private ClientRepository $_clientRepository;

    /**
     * @var AccessTokenRepository
     */
    private AccessTokenRepository $_accessTokenRepository;

    /**
     * @var RefreshTokenRepository
     */
    private RefreshTokenRepository $_refreshTokenRepository;

    /**
     * @var ScopeRepository
     */
    private ScopeRepository $_scopeRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $_userRepository;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->privateKeyPath)) {
            throw new InvalidConfigException('Private key path must be set');
        }

        if (empty($this->encryptionKey)) {
            throw new InvalidConfigException('Encryption key must be set');
        }

        $this->_clientRepository = new ClientRepository();
        $this->_accessTokenRepository = new AccessTokenRepository();
        $this->_refreshTokenRepository = new RefreshTokenRepository();
        $this->_scopeRepository = new ScopeRepository();
        $this->_userRepository = new UserRepository();

        $this->initializeServer();
    }

    /**
     * Initialize League OAuth2 Server
     */
    private function initializeServer(): void
    {
        \Yii::info('Initializing OAuth2 Authorization Server', __METHOD__);

        // Create CryptKey with optional permissions check
        $privateKey = new CryptKey(
            \Yii::getAlias($this->privateKeyPath),
            $this->privateKeyPassphrase,
            $this->keyPermissionsCheck
        );

        $this->_server = new LeagueAuthorizationServer(
            $this->_clientRepository,
            $this->_accessTokenRepository,
            $this->_scopeRepository,
            $privateKey,
            $this->encryptionKey,
            null // response type (null = default)
        );

        // Enable Password Grant
        \Yii::info('Enabling Password Grant', __METHOD__);
        $passwordGrant = new PasswordGrant(
            $this->_userRepository,
            $this->_refreshTokenRepository
        );
        $passwordGrant->setRefreshTokenTTL(new DateInterval('PT' . $this->refreshTokenTTL . 'S'));
        $this->_server->enableGrantType(
            $passwordGrant,
            new DateInterval('PT' . $this->accessTokenTTL . 'S')
        );

        // Enable Refresh Token Grant
        \Yii::info('Enabling Refresh Token Grant', __METHOD__);
        $refreshTokenGrant = new RefreshTokenGrant($this->_refreshTokenRepository);
        $refreshTokenGrant->setRefreshTokenTTL(new DateInterval('PT' . $this->refreshTokenTTL . 'S'));
        $this->_server->enableGrantType(
            $refreshTokenGrant,
            new DateInterval('PT' . $this->accessTokenTTL . 'S')
        );

        \Yii::info('OAuth2 Authorization Server initialized successfully', __METHOD__);
    }

    /**
     * Get League Authorization Server instance
     */
    public function getServer(): LeagueAuthorizationServer
    {
        return $this->_server;
    }

    /**
     * Get Client Repository
     */
    public function getClientRepository(): ClientRepository
    {
        return $this->_clientRepository;
    }

    /**
     * Get Access Token Repository
     */
    public function getAccessTokenRepository(): AccessTokenRepository
    {
        return $this->_accessTokenRepository;
    }

    /**
     * Get Refresh Token Repository
     */
    public function getRefreshTokenRepository(): RefreshTokenRepository
    {
        return $this->_refreshTokenRepository;
    }

    /**
     * Get Scope Repository
     */
    public function getScopeRepository(): ScopeRepository
    {
        return $this->_scopeRepository;
    }

    /**
     * Get User Repository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->_userRepository;
    }
}
