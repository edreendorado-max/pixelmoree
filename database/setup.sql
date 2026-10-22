CREATE DATABASE IF NOT EXISTS `pixelmoree`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `pixelmoree`;

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
