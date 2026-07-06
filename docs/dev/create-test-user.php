<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Скрипт для создания тестового пользователя
 * Использование: php create-test-user.php
 *
 * ВНИМАНИЕ: справочный dev-скрипт, перенесён из app/common/components/oauth2/ как есть.
 * Пути в require ниже указывают на корень приложения относительно ПРЕЖНЕГО расположения
 * (app/common/components/oauth2/) — при запуске из пакета скорректируйте их под своё окружение.
 * Также содержит прямую ссылку на modules\user\entities\User (см. readme.md, раздел про User).
 */

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../common/config/bootstrap.php';
require __DIR__ . '/../../../console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../../common/config/main.php',
    require __DIR__ . '/../../../common/config/main-local.php',
    require __DIR__ . '/../../../console/config/main.php',
    require __DIR__ . '/../../../console/config/main-local.php'
);

$application = new yii\console\Application($config);

use modules\user\entities\User;

$username = 'oauth-test';
$password = '123456';
$email = 'oauth-test@example.com';

// Проверяем, существует ли пользователь
$existingUser = User::findOne(['username' => $username]);
if ($existingUser) {
    echo "Пользователь '$username' уже существует. Обновляю пароль...\n";
    $existingUser->setPassword($password);
    $existingUser->status = 10; // Активен
    $existingUser->save(false);
    $user = $existingUser;
} else {
    echo "Создаю нового пользователя '$username'...\n";
    $user = User::create($username, $email, '', '', $password);
    $user->status = 10; // Активен
    $user->save(false);
}

echo "\n✓ Тестовый пользователь для OAuth2 создан!\n\n";
echo "Username: $username\n";
echo "Password: $password\n";
echo "Email: $email\n";
echo "Status: Active (10)\n\n";

echo "Используйте в OAuth2 запросе:\n";
echo "{\n";
echo "  \"grant_type\": \"password\",\n";
echo "  \"client_id\": \"test-client\",\n";
echo "  \"client_secret\": \"test-secret\",\n";
echo "  \"username\": \"$username\",\n";
echo "  \"password\": \"$password\",\n";
echo "  \"scope\": \"basic\"\n";
echo "}\n\n";
