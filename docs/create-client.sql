/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

-- Создание тестового OAuth2 клиента
-- Выполните: mysql -u root -p yii2cms < create-client.sql

-- Удалите старые записи
DELETE FROM oauth2_clients WHERE client_id = 'test-client';

-- Создайте нового клиента
INSERT INTO oauth2_clients
(client_id, client_secret, redirect_uri, grant_types, scope, user_id, is_confidential, created_at, updated_at)
VALUES
('test-client', 'test-secret', 'http://localhost/callback', 'password refresh_token', 'basic read write', NULL, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Проверьте результат
SELECT
    client_id,
    client_secret,
    grant_types,
    scope,
    is_confidential
FROM oauth2_clients
WHERE client_id = 'test-client';
