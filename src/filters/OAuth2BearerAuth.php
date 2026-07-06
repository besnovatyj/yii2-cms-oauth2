<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\filters;

use Besnovatyj\Oauth2\bridge\Psr7Factory;
use Besnovatyj\Oauth2\ResourceServer;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

/**
 * OAuth2 Bearer Authentication filter
 *
 * Использование:
 * ```php
 * 'as authenticator' => [
 *     'class' => 'Besnovatyj\Oauth2\filters\OAuth2BearerAuth',
 *     'except' => ['site/index'],
 * ]
 * ```
 */
class OAuth2BearerAuth extends AuthMethod
{
    /**
     * @var string название HTTP заголовка с токеном
     */
    public string $header = 'Authorization';

    /**
     * @var string паттерн для извлечения токена из заголовка
     */
    public string $pattern = '/^Bearer\s+(.*?)$/';

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response): ?\yii\web\IdentityInterface
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader !== null) {
            if (preg_match($this->pattern, $authHeader, $matches)) {
                $token = $matches[1];
                $identity = $user->loginByAccessToken($token, get_class($this));
                if ($identity !== null) {
                    return $identity;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function challenge($response): void
    {
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer realm="api"');
    }
}
