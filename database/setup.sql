CREATE DATABASE IF NOT EXISTS `pixmoreedb`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `pixmoreedb`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
    `phone` VARCHAR(30) NULL,
    `bio` TEXT NULL,
    `avatar_path` VARCHAR(255) NULL,
    `pixel_coin_balance` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

SET @add_user_role = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'role'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`users` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT ''user'' AFTER `password`'
);
PREPARE `add_user_role` FROM @add_user_role;
EXECUTE `add_user_role`;
DEALLOCATE PREPARE `add_user_role`;

SET @add_user_avatar = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'avatar_path'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`users` ADD COLUMN `avatar_path` VARCHAR(255) NULL AFTER `bio`'
);
PREPARE `add_user_avatar` FROM @add_user_avatar;
EXECUTE `add_user_avatar`;
DEALLOCATE PREPARE `add_user_avatar`;

SET @add_user_pixel_coin_balance = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'pixel_coin_balance'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`users` ADD COLUMN `pixel_coin_balance` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `avatar_path`'
);
PREPARE `add_user_pixel_coin_balance` FROM @add_user_pixel_coin_balance;
EXECUTE `add_user_pixel_coin_balance`;
DEALLOCATE PREPARE `add_user_pixel_coin_balance`;

CREATE TABLE IF NOT EXISTS `artworks` (
    `id` VARCHAR(140) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(120) NOT NULL,
    `creator` VARCHAR(80) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `image` VARCHAR(255) NOT NULL,
    `image_hash` CHAR(64) NULL,
    `description` VARCHAR(500) NOT NULL,
    `status` ENUM('Listed', 'Review', 'Draft') NOT NULL DEFAULT 'Review',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `artworks_image_hash_unique` (`image_hash`),
    UNIQUE KEY `artworks_image_unique` (`image`),
    KEY `artworks_status_created_at_index` (`status`, `created_at`),
    KEY `artworks_user_id_index` (`user_id`),
    CONSTRAINT `artworks_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

SET @add_artworks_creator_columns = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'artworks'
          AND COLUMN_NAME = 'user_id'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`artworks`
        ADD COLUMN `user_id` BIGINT UNSIGNED NULL,
        ADD KEY `artworks_user_id_index` (`user_id`),
        ADD CONSTRAINT `artworks_user_id_foreign`
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL'
);
PREPARE `add_artworks_creator_columns` FROM @add_artworks_creator_columns;
EXECUTE `add_artworks_creator_columns`;
DEALLOCATE PREPARE `add_artworks_creator_columns`;

SET @add_artworks_image_hash = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'artworks'
          AND COLUMN_NAME = 'image_hash'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`artworks`
        ADD COLUMN `image_hash` CHAR(64) NULL,
        ADD UNIQUE KEY `artworks_image_hash_unique` (`image_hash`)'
);
PREPARE `add_artworks_image_hash` FROM @add_artworks_image_hash;
EXECUTE `add_artworks_image_hash`;
DEALLOCATE PREPARE `add_artworks_image_hash`;

SET @add_artworks_image_unique = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'artworks'
          AND INDEX_NAME = 'artworks_image_unique'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`artworks` ADD UNIQUE KEY `artworks_image_unique` (`image`)'
);
PREPARE `add_artworks_image_unique` FROM @add_artworks_image_unique;
EXECUTE `add_artworks_image_unique`;
DEALLOCATE PREPARE `add_artworks_image_unique`;

CREATE TABLE IF NOT EXISTS `images` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size_bytes` BIGINT UNSIGNED NOT NULL,
    `alt_text` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `images_user_id_created_at_index` (`user_id`, `created_at`),
    CONSTRAINT `images_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_pages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(50) NOT NULL,
    `content` JSON NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `site_pages_slug_unique` (`slug`)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pixel_coin_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `artwork_id` VARCHAR(140) NULL,
    `type` VARCHAR(24) NOT NULL,
    `amount` BIGINT UNSIGNED NOT NULL,
    `balance_after` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pixel_coin_transactions_purchase_unique` (`user_id`, `type`, `artwork_id`),
    KEY `pixel_coin_transactions_user_created_index` (`user_id`, `created_at`),
    CONSTRAINT `pixel_coin_transactions_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `pixel_coin_transactions_artwork_id_foreign`
        FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
