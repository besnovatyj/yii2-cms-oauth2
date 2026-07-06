/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

-- OAuth2 Clients table
CREATE TABLE IF NOT EXISTS `oauth2_clients` (
    `client_id` VARCHAR(80) NOT NULL,
    `client_secret` VARCHAR(255) NOT NULL,
    `redirect_uri` VARCHAR(2000) NULL,
    `grant_types` TEXT NULL,
    `scope` TEXT NULL,
    `user_id` INT(11) NULL,
    `is_confidential` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` INT(11) NOT NULL,
    `updated_at` INT(11) NOT NULL,
    PRIMARY KEY (`client_id`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAuth2 Access Tokens table
CREATE TABLE IF NOT EXISTS `oauth2_access_tokens` (
    `access_token` VARCHAR(255) NOT NULL,
    `client_id` VARCHAR(80) NOT NULL,
    `user_id` INT(11) NULL,
    `expires_at` INT(11) NOT NULL,
    `scope` TEXT NULL,
    `created_at` INT(11) NOT NULL,
    PRIMARY KEY (`access_token`),
    INDEX `idx_client_id` (`client_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAuth2 Refresh Tokens table
CREATE TABLE IF NOT EXISTS `oauth2_refresh_tokens` (
    `refresh_token` VARCHAR(255) NOT NULL,
    `access_token` VARCHAR(255) NOT NULL,
    `client_id` VARCHAR(80) NOT NULL,
    `user_id` INT(11) NULL,
    `expires_at` INT(11) NOT NULL,
    `scope` TEXT NULL,
    `created_at` INT(11) NOT NULL,
    PRIMARY KEY (`refresh_token`),
    INDEX `idx_access_token` (`access_token`),
    INDEX `idx_client_id` (`client_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAuth2 Scopes table
CREATE TABLE IF NOT EXISTS `oauth2_scopes` (
    `scope` VARCHAR(80) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` INT(11) NOT NULL,
    PRIMARY KEY (`scope`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default scopes
INSERT INTO `oauth2_scopes` (`scope`, `description`, `is_default`, `created_at`) VALUES
('basic', 'Basic access to user profile', 1, UNIX_TIMESTAMP()),
('read', 'Read access to resources', 0, UNIX_TIMESTAMP()),
('write', 'Write access to resources', 0, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `scope` = `scope`;
