/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

-- Создание тестового OAuth2 клиента
-- Client Secret: test-secret (нехешированный для простоты тестирования)
INSERT INTO `oauth2_clients`
(`client_id`, `client_secret`, `redirect_uri`, `grant_types`, `scope`, `user_id`, `is_confidential`, `created_at`, `updated_at`)
VALUES
('test-client', 'test-secret', 'http://localhost/callback', 'password refresh_token', 'basic read write', NULL, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `client_secret` = 'test-secret',
    `grant_types` = 'password refresh_token',
    `scope` = 'basic read write',
    `updated_at` = UNIX_TIMESTAMP();

-- Проверка: показать созданного клиента
SELECT * FROM `oauth2_clients` WHERE `client_id` = 'test-client';
