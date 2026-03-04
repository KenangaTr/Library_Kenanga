<?php

require_once __DIR__ . '/config/Database.php';

/**
 * Class Book
 *
 * Represents the `books` table and exposes CRUD operations.
 *
 * Table columns:
 *   id              INT  AUTO_INCREMENT PRIMARY KEY
 *   title           VARCHAR(255) NOT NULL
 *   author          VARCHAR(255) NOT NULL
 *   published_year  INT NOT NULL
 *   isbn            VARCHAR(20)  NOT NULL UNIQUE
 *   stock           INT DEFAULT 0
 */
class Book
{
    private PDO $db;
    private string $table = 'books';

    // ─── Attributes ────────────────────────────────────────────────────────────
    public ?int    $id             = null;
    public string  $title          = '';
    public string  $author         = '';
    public int     $published_year = 0;
    public string  $isbn           = '';
    public int     $stock          = 0;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ─── Read All ──────────────────────────────────────────────────────────────

    /**
     * Fetch all books with optional pagination.
     *
     * @param int $limit  Number of records per page (0 = no limit).
     * @param int $offset Starting offset.
     * @return array<int, array<string, mixed>>
     */
    public function getAll(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit > 0) {
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── Read One ──────────────────────────────────────────────────────────────

    /**
     * Fetch a single book by its primary key.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function getById(int $id): array|false
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    /**
     * Insert a new book record.
     * Populates $this->id with the new auto-incremented ID on success.
     *
     * @return bool
     */
    public function create(): bool
    {
        $sql = "INSERT INTO {$this->table}
                    (title, author, published_year, isbn, stock)
                VALUES
                    (:title, :author, :published_year, :isbn, :stock)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title',          $this->title,          PDO::PARAM_STR);
        $stmt->bindValue(':author',         $this->author,         PDO::PARAM_STR);
        $stmt->bindValue(':published_year', $this->published_year, PDO::PARAM_INT);
        $stmt->bindValue(':isbn',           $this->isbn,           PDO::PARAM_STR);
        $stmt->bindValue(':stock',          $this->stock,          PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->id = (int) $this->db->lastInsertId();
            return true;
        }
        return false;
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    /**
     * Update an existing book.
     * Uses the value of $this->id to identify the target row.
     *
     * @return bool
     */
    public function update(): bool
    {
        $sql = "UPDATE {$this->table}
                SET
                    title          = :title,
                    author         = :author,
                    published_year = :published_year,
                    isbn           = :isbn,
                    stock          = :stock
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title',          $this->title,          PDO::PARAM_STR);
        $stmt->bindValue(':author',         $this->author,         PDO::PARAM_STR);
        $stmt->bindValue(':published_year', $this->published_year, PDO::PARAM_INT);
        $stmt->bindValue(':isbn',           $this->isbn,           PDO::PARAM_STR);
        $stmt->bindValue(':stock',          $this->stock,          PDO::PARAM_INT);
        $stmt->bindValue(':id',             $this->id,             PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ─── Delete ────────────────────────────────────────────────────────────────

    /**
     * Delete a book by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql  = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Count total number of book records (useful for pagination).
     *
     * @return int
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Check whether an ISBN already exists (excluding a given book ID).
     *
     * @param string $isbn
     * @param int    $excludeId Exclude this ID from the check (useful for updates).
     * @return bool
     */
    public function isbnExists(string $isbn, int $excludeId = 0): bool
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE isbn = :isbn AND id != :excludeId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':isbn',      $isbn,      PDO::PARAM_STR);
        $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }
}
