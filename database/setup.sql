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
    `banner_path` VARCHAR(255) NULL,
    `pixel_coin_balance` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `account_id` CHAR(36) NULL,
    `cash_balance_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `cash_reserved_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    UNIQUE KEY `users_account_id_unique` (`account_id`)
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

SET @add_user_banner = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'pixmoreedb'
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'banner_path'
    ),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`users` ADD COLUMN `banner_path` VARCHAR(255) NULL AFTER `avatar_path`'
);
PREPARE `add_user_banner` FROM @add_user_banner;
EXECUTE `add_user_banner`;
DEALLOCATE PREPARE `add_user_banner`;

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
    `product_id` CHAR(36) NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(120) NOT NULL,
    `creator` VARCHAR(80) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `item_count` INT UNSIGNED NOT NULL DEFAULT 1,
    `image` VARCHAR(255) NOT NULL,
    `image_hash` CHAR(64) NULL,
    `description` VARCHAR(500) NOT NULL,
    `status` ENUM('Listed', 'Review', 'Draft') NOT NULL DEFAULT 'Review',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `artworks_product_id_unique` (`product_id`),
    UNIQUE KEY `artworks_image_hash_unique` (`image_hash`),
    UNIQUE KEY `artworks_image_unique` (`image`),
    KEY `artworks_status_created_at_index` (`status`, `created_at`),
    KEY `artworks_user_id_index` (`user_id`),
    CONSTRAINT `artworks_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

SET @add_artworks_item_count = IF(
        EXISTS (
                SELECT 1
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = 'pixmoreedb'
                    AND TABLE_NAME = 'artworks'
                    AND COLUMN_NAME = 'item_count'
        ),
        'SELECT 1',
        'ALTER TABLE `pixmoreedb`.`artworks` ADD COLUMN `item_count` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `price`'
);
PREPARE `add_artworks_item_count` FROM @add_artworks_item_count;
EXECUTE `add_artworks_item_count`;
DEALLOCATE PREPARE `add_artworks_item_count`;

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

CREATE TABLE IF NOT EXISTS `artwork_price_snapshots` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `artwork_id` VARCHAR(140) NOT NULL,
    `snapshot_date` DATE NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `artwork_price_snapshots_artwork_id_snapshot_date_unique` (`artwork_id`, `snapshot_date`),
    CONSTRAINT `artwork_price_snapshots_artwork_id_foreign`
        FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`) ON DELETE CASCADE
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

SET @add_user_account_id = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'account_id'),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`users` ADD COLUMN `account_id` CHAR(36) NULL, ADD UNIQUE KEY `users_account_id_unique` (`account_id`)'
);
PREPARE `add_user_account_id` FROM @add_user_account_id;
EXECUTE `add_user_account_id`;
DEALLOCATE PREPARE `add_user_account_id`;
UPDATE `users` SET `account_id` = UUID() WHERE `account_id` IS NULL;

SET @add_user_cash_balance = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'cash_balance_minor'),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`users` ADD COLUMN `cash_balance_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0, ADD COLUMN `cash_reserved_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0'
);
PREPARE `add_user_cash_balance` FROM @add_user_cash_balance;
EXECUTE `add_user_cash_balance`;
DEALLOCATE PREPARE `add_user_cash_balance`;

SET @add_artworks_product_id = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'artworks' AND COLUMN_NAME = 'product_id'),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`artworks` ADD COLUMN `product_id` CHAR(36) NULL, ADD UNIQUE KEY `artworks_product_id_unique` (`product_id`)'
);
PREPARE `add_artworks_product_id` FROM @add_artworks_product_id;
EXECUTE `add_artworks_product_id`;
DEALLOCATE PREPARE `add_artworks_product_id`;
UPDATE `artworks` SET `product_id` = UUID() WHERE `product_id` IS NULL;

CREATE TABLE IF NOT EXISTS `user_profiles` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `phone` VARCHAR(30) NULL,
    `bio` TEXT NULL,
    `avatar_path` VARCHAR(255) NULL,
    `banner_path` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO `user_profiles` (`user_id`, `phone`, `bio`, `avatar_path`, `banner_path`, `created_at`, `updated_at`)
SELECT `id`, `phone`, `bio`, `avatar_path`, `banner_path`, `created_at`, `updated_at`
FROM `users`
ON DUPLICATE KEY UPDATE
    `phone` = VALUES(`phone`),
    `bio` = VALUES(`bio`),
    `avatar_path` = VALUES(`avatar_path`),
    `banner_path` = VALUES(`banner_path`),
    `updated_at` = VALUES(`updated_at`);

CREATE TABLE IF NOT EXISTS `payment_orders` (
    `purchase_id` CHAR(36) NOT NULL,
    `buyer_id` BIGINT UNSIGNED NOT NULL,
    `seller_id` BIGINT UNSIGNED NULL,
    `artwork_id` VARCHAR(140) NULL,
    `type` VARCHAR(24) NOT NULL,
    `amount_minor` BIGINT UNSIGNED NOT NULL,
    `currency` CHAR(3) NOT NULL DEFAULT 'PHP',
    `payment_method` VARCHAR(24) NOT NULL,
    `transaction_reference` VARCHAR(120) NULL,
    `proof_path` VARCHAR(255) NULL,
    `payout_email` VARCHAR(254) NULL,
    `payout_reference` VARCHAR(120) NULL,
    `status` VARCHAR(24) NOT NULL DEFAULT 'pending_review',
    `reviewed_by` BIGINT UNSIGNED NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`purchase_id`),
    UNIQUE KEY `payment_orders_method_reference_unique` (`payment_method`, `transaction_reference`),
    UNIQUE KEY `payment_orders_payout_reference_unique` (`payout_reference`),
    KEY `payment_orders_status_created_at_index` (`status`, `created_at`),
    CONSTRAINT `payment_orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `payment_orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `payment_orders_artwork_id_foreign` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`) ON DELETE SET NULL,
    CONSTRAINT `payment_orders_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @add_payment_order_payout_columns = IF(
    EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'payment_orders' AND COLUMN_NAME = 'payout_email'),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`payment_orders` ADD COLUMN `payout_email` VARCHAR(254) NULL, ADD COLUMN `payout_reference` VARCHAR(120) NULL'
);
PREPARE `add_payment_order_payout_columns` FROM @add_payment_order_payout_columns;
EXECUTE `add_payment_order_payout_columns`;
DEALLOCATE PREPARE `add_payment_order_payout_columns`;

SET @add_payment_order_reference_unique = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'payment_orders' AND INDEX_NAME = 'payment_orders_method_reference_unique'),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`payment_orders` ADD UNIQUE KEY `payment_orders_method_reference_unique` (`payment_method`, `transaction_reference`)'
);
PREPARE `add_payment_order_reference_unique` FROM @add_payment_order_reference_unique;
EXECUTE `add_payment_order_reference_unique`;
DEALLOCATE PREPARE `add_payment_order_reference_unique`;

SET @add_payment_order_payout_reference_unique = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'payment_orders' AND INDEX_NAME = 'payment_orders_payout_reference_unique'),
    'SELECT 1',
    'ALTER TABLE `pixmoreedb`.`payment_orders` ADD UNIQUE KEY `payment_orders_payout_reference_unique` (`payout_reference`)'
);
PREPARE `add_payment_order_payout_reference_unique` FROM @add_payment_order_payout_reference_unique;
EXECUTE `add_payment_order_payout_reference_unique`;
DEALLOCATE PREPARE `add_payment_order_payout_reference_unique`;

CREATE TABLE IF NOT EXISTS `cash_ledger_entries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `purchase_id` CHAR(36) NOT NULL,
    `type` VARCHAR(24) NOT NULL,
    `direction` VARCHAR(8) NOT NULL,
    `amount_minor` BIGINT UNSIGNED NOT NULL,
    `balance_after_minor` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `cash_ledger_user_purchase_type_unique` (`user_id`, `purchase_id`, `type`),
    CONSTRAINT `cash_ledger_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `cash_ledger_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `payment_orders` (`purchase_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `artwork_ownerships` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `artwork_id` VARCHAR(140) NOT NULL,
    `owner_id` BIGINT UNSIGNED NOT NULL,
    `acquired_purchase_id` CHAR(36) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `artwork_ownerships_artwork_id_unique` (`artwork_id`),
    UNIQUE KEY `artwork_ownerships_acquired_purchase_id_unique` (`acquired_purchase_id`),
    CONSTRAINT `artwork_ownerships_artwork_id_foreign` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`) ON DELETE CASCADE,
    CONSTRAINT `artwork_ownerships_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `artwork_ownerships_purchase_id_foreign` FOREIGN KEY (`acquired_purchase_id`) REFERENCES `payment_orders` (`purchase_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `resale_listings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `artwork_id` VARCHAR(140) NOT NULL,
    `seller_id` BIGINT UNSIGNED NOT NULL,
    `price_minor` BIGINT UNSIGNED NOT NULL,
    `evidence_path` VARCHAR(255) NOT NULL,
    `ownership_declaration` TEXT NOT NULL,
    `status` VARCHAR(24) NOT NULL DEFAULT 'pending_review',
    `reviewed_by` BIGINT UNSIGNED NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `resale_listings_artwork_status_index` (`artwork_id`, `status`),
    KEY `resale_listings_status_created_at_index` (`status`, `created_at`),
    CONSTRAINT `resale_listings_artwork_id_foreign` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`) ON DELETE CASCADE,
    CONSTRAINT `resale_listings_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `resale_listings_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @drop_resale_artwork_unique = IF(
    EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'pixmoreedb' AND TABLE_NAME = 'resale_listings' AND INDEX_NAME = 'resale_listings_artwork_id_unique' AND NON_UNIQUE = 0),
    'ALTER TABLE `pixmoreedb`.`resale_listings` DROP INDEX `resale_listings_artwork_id_unique`, ADD KEY `resale_listings_artwork_status_index` (`artwork_id`, `status`)',
    'SELECT 1'
);
PREPARE `drop_resale_artwork_unique` FROM @drop_resale_artwork_unique;
EXECUTE `drop_resale_artwork_unique`;
DEALLOCATE PREPARE `drop_resale_artwork_unique`;

CREATE TABLE IF NOT EXISTS `ownership_transfers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` CHAR(36) NOT NULL,
    `from_user_id` BIGINT UNSIGNED NULL,
    `to_user_id` BIGINT UNSIGNED NOT NULL,
    `purchase_id` CHAR(36) NOT NULL,
    `amount_minor` BIGINT UNSIGNED NOT NULL,
    `currency` CHAR(3) NOT NULL DEFAULT 'PHP',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ownership_transfers_purchase_id_unique` (`purchase_id`),
    CONSTRAINT `ownership_transfers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `artworks` (`product_id`) ON DELETE CASCADE,
    CONSTRAINT `ownership_transfers_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ownership_transfers_to_user_id_foreign` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ownership_transfers_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `payment_orders` (`purchase_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
