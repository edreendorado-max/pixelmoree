<?php

final class ArtworkRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT artworks.id, artworks.user_id, artworks.title,
                    COALESCE(users.name, artworks.creator) AS creator,
                    artworks.category, artworks.price, artworks.image,
                    artworks.image_hash, artworks.description, artworks.status
             FROM artworks
             LEFT JOIN users ON users.id = artworks.user_id
             ORDER BY artworks.created_at DESC, artworks.title ASC'
        );

        return $statement->fetchAll();
    }

    public function listed(): array
    {
        $statement = $this->pdo->query(
            "SELECT artworks.id, artworks.user_id, artworks.title,
                    COALESCE(users.name, artworks.creator) AS creator,
                    artworks.category, artworks.price, artworks.image,
                    artworks.image_hash, artworks.description, artworks.status
             FROM artworks
             LEFT JOIN users ON users.id = artworks.user_id
             WHERE artworks.status = 'Listed'
             ORDER BY artworks.created_at DESC, artworks.title ASC"
        );

        return $statement->fetchAll();
    }

    public function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 50));
        $statement = $this->pdo->query(
            'SELECT artworks.id, artworks.user_id, artworks.title,
                    COALESCE(users.name, artworks.creator) AS creator,
                    artworks.category, artworks.price, artworks.image,
                    artworks.image_hash, artworks.description, artworks.status
             FROM artworks
             LEFT JOIN users ON users.id = artworks.user_id
             ORDER BY artworks.created_at DESC, artworks.title ASC
             LIMIT ' . $limit
        );

        return $statement->fetchAll();
    }

    public function find(string $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT artworks.id, artworks.user_id, artworks.title,
                    COALESCE(users.name, artworks.creator) AS creator,
                    artworks.category, artworks.price, artworks.image,
                    artworks.image_hash, artworks.description, artworks.status
             FROM artworks
             LEFT JOIN users ON users.id = artworks.user_id
             WHERE artworks.id = :id'
        );
        $statement->execute(['id' => $id]);
        $artwork = $statement->fetch();

        return $artwork === false ? null : $artwork;
    }

    public function counts(): array
    {
        $statement = $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'Listed') AS listed,
                    SUM(status = 'Review') AS review
             FROM artworks"
        );
        $counts = $statement->fetch();

        return [
            'total' => (int) ($counts['total'] ?? 0),
            'listed' => (int) ($counts['listed'] ?? 0),
            'review' => (int) ($counts['review'] ?? 0),
        ];
    }

    public function create(array $input): string
    {
        $artwork = $this->validate($input);
        $duplicatePath = $this->pdo->prepare('SELECT id FROM artworks WHERE image = :image');
        $duplicatePath->execute(['image' => $artwork['image']]);
        if ($duplicatePath->fetchColumn() !== false) {
            throw new InvalidArgumentException('This artwork image is already listed.');
        }

        $imageHash = hash_file('sha256', $artwork['asset_path']);
        if ($imageHash === false) {
            throw new RuntimeException('The selected artwork image could not be verified.');
        }
        $duplicate = $this->pdo->prepare('SELECT id FROM artworks WHERE image_hash = :image_hash');
        $duplicate->execute(['image_hash' => $imageHash]);
        if ($duplicate->fetchColumn() !== false) {
            throw new InvalidArgumentException('This exact artwork image is already listed.');
        }

        $id = $this->slug($artwork['title']) . '-' . bin2hex(random_bytes(3));
        $statement = $this->pdo->prepare(
            'INSERT INTO artworks (id, user_id, title, creator, category, price, image, image_hash, description, status)
             VALUES (:id, :user_id, :title, :creator, :category, :price, :image, :image_hash, :description, :status)'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $artwork['user_id'],
            'title' => $artwork['title'],
            'creator' => $artwork['creator'],
            'category' => $artwork['category'],
            'price' => $artwork['price'],
            'image' => $artwork['image'],
            'image_hash' => $imageHash,
            'description' => $artwork['description'],
            'status' => 'Review',
        ]);

        return $id;
    }

    public function updateStatus(string $id, string $status): void
    {
        if (!in_array($status, ['Listed', 'Review', 'Draft'], true)) {
            throw new InvalidArgumentException('Choose a valid listing status.');
        }

        $statement = $this->pdo->prepare('UPDATE artworks SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
        if ($statement->rowCount() === 0 && $this->find($id) === null) {
            throw new OutOfBoundsException('The artwork listing could not be found.');
        }
    }

    public function delete(string $id): void
    {
        $artwork = $this->find($id);
        if ($artwork === null) {
            throw new OutOfBoundsException('The artwork listing could not be found.');
        }

        $statement = $this->pdo->prepare('DELETE FROM artworks WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            throw new OutOfBoundsException('The artwork listing could not be found.');
        }

        if (str_starts_with($artwork['image'], 'storage/')) {
            $storedPath = substr($artwork['image'], strlen('storage/'));
            if (
                \Illuminate\Support\Facades\Storage::disk('public')->exists($storedPath)
                && ! \Illuminate\Support\Facades\Storage::disk('public')->delete($storedPath)
            ) {
                throw new RuntimeException('The artwork image file could not be removed.');
            }
        }
    }

    private function validate(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $creator = trim((string) ($input['creator'] ?? ''));
        $category = trim((string) ($input['category'] ?? ''));
        $price = trim((string) ($input['price'] ?? ''));
        $image = trim((string) ($input['image'] ?? ''));
        $ownerId = $input['user_id'] ?? null;
        $description = trim((string) ($input['description'] ?? ''));

        if ($title === '' || mb_strlen($title) > 120) {
            throw new InvalidArgumentException('Enter a title up to 120 characters long.');
        }
        if (($creator === '' && $ownerId === null) || mb_strlen($creator) > 80) {
            throw new InvalidArgumentException('Enter a creator name up to 80 characters long.');
        }
        if ($category === '' || mb_strlen($category) > 50) {
            throw new InvalidArgumentException('Enter a category up to 50 characters long.');
        }
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price)) {
            throw new InvalidArgumentException('Enter a non-negative price with up to two decimal places.');
        }
        if ($description === '' || mb_strlen($description) > 500) {
            throw new InvalidArgumentException('Enter a description up to 500 characters long.');
        }
        if ($ownerId !== null && filter_var($ownerId, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Choose a valid artwork creator.');
        }

        if ($ownerId !== null) {
            $ownerStatement = $this->pdo->prepare('SELECT name FROM users WHERE id = :id');
            $ownerStatement->execute(['id' => $ownerId]);
            $ownerName = $ownerStatement->fetchColumn();
            if ($ownerName === false) {
                throw new InvalidArgumentException('Choose a valid artwork creator.');
            }
            $creator = $ownerName;
        }

        $image = str_replace('\\', '/', $image);
        $hasSafeFormat = preg_match(
            '/\A(?:nfts\/)?[A-Za-z0-9][A-Za-z0-9 .()_-]*\.(?:jpe?g|png|gif|webp)\z/i',
            $image
        ) === 1;
        $assetRoot = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets');
        $assetPath = $assetRoot === false
            ? false
            : realpath($assetRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $image));
        $pathParts = explode('/', $image);
        $isAllowedPath = count($pathParts) === 1
            || (count($pathParts) === 2 && $pathParts[0] === 'nfts');
        if (
            !$isAllowedPath
            || !$hasSafeFormat
            || $image === ''
            || in_array('..', $pathParts, true)
            || $assetRoot === false
            || $assetPath === false
            || !is_file($assetPath)
            || !str_starts_with($assetPath, $assetRoot . DIRECTORY_SEPARATOR)
        ) {
            throw new InvalidArgumentException('Choose an image from the supplied project assets.');
        }

        return [
            'asset_path' => $assetPath,
            'user_id' => $ownerId,
            'title' => $title,
            'creator' => $creator,
            'category' => $category,
            'price' => $price,
            'image' => $image,
            'description' => $description,
        ];
    }

    private function slug(string $value): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'artwork';
    }
}
