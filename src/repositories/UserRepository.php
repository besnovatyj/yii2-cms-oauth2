<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\repositories;

use Besnovatyj\Oauth2\bridge\UserEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use modules\user\repositories\UserReadRepository;
use Yii;

/**
 * User Repository for League OAuth2 Server (Password Grant)
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        /** @var UserReadRepository $userRepository */
        $userRepository = Yii::$container->get(UserReadRepository::class);

        $user = $userRepository->findActiveByUsername($username);

        if ($user === null || !$user->validatePassword($password)) {
            return null;
        }

        $userEntity = new UserEntity();
        $userEntity->setIdentifier((string)$user->id);

        return $userEntity;
    }
}
