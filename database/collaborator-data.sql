-- Optional, sanitized starter data for local collaborator databases.
-- Import only after database/setup.sql or `php artisan migrate` has run.
-- This file intentionally contains no user accounts, email addresses, or passwords.
-- Artwork ownership is anonymized and the supplied image is in public/assets/nfts.

USE `pixmoreedb`;

INSERT IGNORE INTO `artworks`
    (`id`, `user_id`, `title`, `creator`, `category`, `price`, `image`, `image_hash`, `description`, `status`, `created_at`, `updated_at`)
VALUES
    (
        'anime-b3182ae1',
        NULL,
        'anime',
        'Pixmoree Creator',
        'Illustration',
        11.00,
        'nfts/collaboration-anime.png',
        '08eb3f723b4b51c2dcf3a8ffd115c6c3ff2601c9a832e511832379afd7fe3506',
        'Shared demo artwork record. Creator account details are intentionally omitted.',
        'Listed',
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    );
