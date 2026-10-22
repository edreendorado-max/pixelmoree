<?php

final class ArtworkRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, title, creator, category, price, image, description, status
             FROM artworks
             ORDER BY created_at DESC, title ASC'
        );

        return $statement->fetchAll();
    }

    public function recent(int $limit = 5): array
    {
        $limit = max(1, min($limit, 50));
        $statement = $this->pdo->query(
            'SELECT id, title, creator, category, price, image, description, status
             FROM artworks
             ORDER BY created_at DESC, title ASC
             LIMIT ' . $limit
        );

        return $statement->fetchAll();
    }

    public function find(string $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, title, creator, category, price, image, description, status
             FROM artworks
             WHERE id = :id'
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
        $id = $this->slug($artwork['title']) . '-' . bin2hex(random_bytes(3));
        $statement = $this->pdo->prepare(
            'INSERT INTO artworks (id, title, creator, category, price, image, description, status)
             VALUES (:id, :title, :creator, :category, :price, :image, :description, :status)'
        );
        $statement->execute([
            'id' => $id,
            'title' => $artwork['title'],
            'creator' => $artwork['creator'],
            'category' => $artwork['category'],
            'price' => $artwork['price'],
            'image' => $artwork['image'],
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
        $statement = $this->pdo->prepare('DELETE FROM artworks WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            throw new OutOfBoundsException('The artwork listing could not be found.');
        }
    }

    private function validate(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $creator = trim((string) ($input['creator'] ?? ''));
        $category = trim((string) ($input['category'] ?? ''));
        $price = trim((string) ($input['price'] ?? ''));
        $image = trim((string) ($input['image'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));

        if ($title === '' || mb_strlen($title) > 120) {
            throw new InvalidArgumentException('Enter a title up to 120 characters long.');
        }
        if ($creator === '' || mb_strlen($creator) > 80) {
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

        $assetName = basename($image);
        $assetPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $assetName;
        if ($image !== $assetName || $assetName === '' || !is_file($assetPath)) {
            throw new InvalidArgumentException('Choose an image from the supplied project assets.');
        }

        return [
            'title' => $title,
            'creator' => $creator,
            'category' => $category,
            'price' => $price,
            'image' => $assetName,
            'description' => $description,
        ];
    }

    private function slug(string $value): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'artwork';
    }
}
