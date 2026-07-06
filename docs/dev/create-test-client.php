<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Скрипт для создания тестового OAuth2 клиента
 *
 * Использование:
 * php app/common/components/oauth2/create-test-client.php
 *
 * ВНИМАНИЕ: справочный dev-скрипт, перенесён из app/common/components/oauth2/ как есть.
 * Пути в require ниже указывают на корень приложения относительно ПРЕЖНЕГО расположения
 * (app/common/components/oauth2/) — при запуске из пакета скорректируйте их под своё окружение.
 */

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../common/config/bootstrap.php';
require __DIR__ . '/../../../rest/config/bootstrap.php';

$config = require __DIR__ . '/../../../rest/config/main.php';

// Создаем приложение
$application = new \yii\web\Application($config);

use Besnovatyj\Oauth2\entities\OAuth2Client;

// Данные клиента
$clientId = 'test-client';
$clientSecret = 'test-secret';

// Проверяем, существует ли клиент
$existingClient = OAuth2Client::findOne(['client_id' => $clientId]);
if ($existingClient) {
    echo "Клиент '$clientId' уже существует. Удаляю...\n";
    $existingClient->delete();
}

// Создаем нового клиента
$client = new OAuth2Client();
$client->client_id = $clientId;
$client->client_secret = Yii::$app->security->generatePasswordHash($clientSecret);
$client->redirect_uri = 'http://localhost/callback';
$client->grant_types = 'password refresh_token';
$client->scope = 'basic read write';
$client->is_confidential = 1;
$client->created_at = time();
$client->updated_at = time();

if ($client->save()) {
    echo "\n✓ Тестовый OAuth2 клиент успешно создан!\n\n";
    echo "Client ID: $clientId\n";
    echo "Client Secret: $clientSecret\n";
    echo "Grant Types: password, refresh_token\n";
    echo "Scopes: basic, read, write\n\n";

    echo "Пример запроса для получения токена:\n";
    echo "curl -X POST http://your-api.com/oauth2/token \\\n";
    echo "  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n";
    echo "  -d \"grant_type=password\" \\\n";
    echo "  -d \"client_id=$clientId\" \\\n";
    echo "  -d \"client_secret=$clientSecret\" \\\n";
    echo "  -d \"username=YOUR_USERNAME\" \\\n";
    echo "  -d \"password=YOUR_PASSWORD\" \\\n";
    echo "  -d \"scope=basic\"\n\n";
} else {
    echo "\n✗ Ошибка при создании клиента:\n";
    print_r($client->errors);
}
