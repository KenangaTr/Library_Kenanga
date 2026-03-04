<?php

/**
 * Class Book
 *
 * Merepresentasikan tabel `books` di database.
 * Menerima koneksi PDO melalui constructor (Dependency Injection).
 *
 * Kolom tabel:
 *   id              INT  AUTO_INCREMENT PRIMARY KEY
 *   title           VARCHAR(255) NOT NULL
 *   author          VARCHAR(255) NOT NULL
 *   published_year  INT  NOT NULL
 *   isbn            VARCHAR(20)  NOT NULL UNIQUE
 *   stock           INT  DEFAULT 0
 */
class Book
{
    // ── Koneksi Database ───────────────────────────────────────────────────────
    private PDO $conn;
    private string $table = "books";

    // ── Properti Kolom ─────────────────────────────────────────────────────────
    public ?int    $id             = null;
    public string  $title          = "";
    public string  $author         = "";
    public int     $published_year = 0;
    public string  $isbn           = "";
    public int     $stock          = 0;

    /**
     * Constructor menerima koneksi PDO dari luar (Dependency Injection).
     *
     * @param PDO $db  Objek koneksi PDO yang aktif.
     */
    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // ─── READ ──────────────────────────────────────────────────────────────────

    /**
     * Mengambil semua buku atau satu buku berdasarkan ID.
     *
     * @param int|null $id  Jika null, kembalikan semua buku.
     * @return PDOStatement
     */
    public function read(?int $id = null): PDOStatement
    {
        if ($id !== null) {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt  = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        } else {
            $query = "SELECT * FROM {$this->table} ORDER BY id ASC";
            $stmt  = $this->conn->prepare($query);
        }

        $stmt->execute();
        return $stmt;
    }

    // ─── CREATE ────────────────────────────────────────────────────────────────

    /**
     * Menyimpan record buku baru ke database.
     * Data diambil dari properti class yang sudah di-set sebelumnya.
     *
     * @return bool  true jika berhasil, false jika gagal.
     */
    public function create(): bool
    {
        $query = "INSERT INTO {$this->table}
                      (title, author, published_year, isbn, stock)
                  VALUES
                      (:title, :author, :published_year, :isbn, :stock)";

        $stmt = $this->conn->prepare($query);

        // Sanitasi input
        $this->title  = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn   = htmlspecialchars(strip_tags($this->isbn));

        $stmt->bindValue(":title",          $this->title,          PDO::PARAM_STR);
        $stmt->bindValue(":author",         $this->author,         PDO::PARAM_STR);
        $stmt->bindValue(":published_year", $this->published_year, PDO::PARAM_INT);
        $stmt->bindValue(":isbn",           $this->isbn,           PDO::PARAM_STR);
        $stmt->bindValue(":stock",          $this->stock,          PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->id = (int) $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // ─── UPDATE ────────────────────────────────────────────────────────────────

    /**
     * Memperbarui data buku berdasarkan $this->id.
     *
     * @return bool  true jika berhasil, false jika gagal.
     */
    public function update(): bool
    {
        $query = "UPDATE {$this->table}
                  SET
                      title          = :title,
                      author         = :author,
                      published_year = :published_year,
                      isbn           = :isbn,
                      stock          = :stock
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitasi input
        $this->title  = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn   = htmlspecialchars(strip_tags($this->isbn));

        $stmt->bindValue(":title",          $this->title,          PDO::PARAM_STR);
        $stmt->bindValue(":author",         $this->author,         PDO::PARAM_STR);
        $stmt->bindValue(":published_year", $this->published_year, PDO::PARAM_INT);
        $stmt->bindValue(":isbn",           $this->isbn,           PDO::PARAM_STR);
        $stmt->bindValue(":stock",          $this->stock,          PDO::PARAM_INT);
        $stmt->bindValue(":id",             $this->id,             PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ─── DELETE ────────────────────────────────────────────────────────────────

    /**
     * Menghapus satu buku berdasarkan $this->id.
     *
     * @return bool  true jika berhasil & ada baris terdampak, false jika gagal.
     */
    public function delete(): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags((string) $this->id));

        $stmt->bindValue(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
