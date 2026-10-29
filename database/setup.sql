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

CREATE TABLE IF NOT EXISTS `artworks` (
    `id` VARCHAR(140) NOT NULL,
    `title` VARCHAR(120) NOT NULL,
    `creator` VARCHAR(80) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `image` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500) NOT NULL,
    `status` ENUM('Listed', 'Review', 'Draft') NOT NULL DEFAULT 'Review',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `artworks_status_created_at_index` (`status`, `created_at`)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

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

INSERT INTO `artworks` (`id`, `title`, `creator`, `category`, `price`, `image`, `description`, `status`)
VALUES
    ('neon-wanderer', 'Neon Wanderer', 'pixel.dreams', 'Space', 0.86, 'anime-style-character-space.jpg', 'A fictional traveler drifts through a violet galaxy in this demo collectible.', 'Listed'),
    ('moonlit-guardian', 'Moonlit Guardian', 'neko.studio', 'Characters', 1.24, 'cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png', 'A fictional guardian watches over a quiet moonlit world.', 'Listed'),
    ('starlight-drift', 'Starlight Drift', 'cosmic.art', 'Space', 1.52, 'anime-style-character-space (2).jpg', 'An imagined journey across a glowing nebula. This is sample artwork.', 'Review')
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `creator` = VALUES(`creator`),
    `category` = VALUES(`category`),
    `price` = VALUES(`price`),
    `image` = VALUES(`image`),
    `description` = VALUES(`description`),
    `status` = VALUES(`status`);

SET @legacy_artworks_copy = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = 'pixelmoree'
          AND TABLE_NAME = 'artworks'
    ),
    'INSERT INTO `pixmoreedb`.`artworks` (`id`, `title`, `creator`, `category`, `price`, `image`, `description`, `status`, `created_at`, `updated_at`)
     SELECT `id`, `title`, `creator`, `category`, `price`, `image`, `description`, `status`, `created_at`, `updated_at`
     FROM `pixelmoree`.`artworks`
     ON DUPLICATE KEY UPDATE
        `title` = VALUES(`title`),
        `creator` = VALUES(`creator`),
        `category` = VALUES(`category`),
        `price` = VALUES(`price`),
        `image` = VALUES(`image`),
        `description` = VALUES(`description`),
        `status` = VALUES(`status`),
        `created_at` = VALUES(`created_at`),
        `updated_at` = VALUES(`updated_at`)',
    'SELECT 1'
);
PREPARE `copy_legacy_artworks` FROM @legacy_artworks_copy;
EXECUTE `copy_legacy_artworks`;
DEALLOCATE PREPARE `copy_legacy_artworks`;
