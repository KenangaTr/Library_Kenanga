<?php

/**
 * Class Admin
 *
 * Merepresentasikan tabel `admins` di database.
 * Menerima koneksi PDO melalui constructor (Dependency Injection).
 * Password selalu di-hash dengan bcrypt sebelum disimpan —
 * plain text TIDAK PERNAH masuk ke database.
 *
 * Kolom tabel:
 *   id            INT  AUTO_INCREMENT PRIMARY KEY
 *   nama_lengkap  VARCHAR(100) NOT NULL
 *   username      VARCHAR(50)  NOT NULL UNIQUE
 *   password      VARCHAR(255) NOT NULL  (bcrypt hash)
 *   created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
 */
class Admin
{
    // ── Koneksi Database ───────────────────────────────────────────────────────
    private PDO $conn;
    private string $table = "admins";

    // ── Properti Kolom ─────────────────────────────────────────────────────────
    public ?int    $id           = null;
    public string  $nama_lengkap = "";
    public string  $username     = "";
    public string  $password     = "";   // plain text; akan di-hash sebelum disimpan
    public ?string $created_at   = null;

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
     * Mengambil semua admin atau satu admin berdasarkan ID.
     * Kolom `password` (hash) tidak disertakan dalam hasil query.
     *
     * @param int|null $id  Jika null, kembalikan semua admin.
     * @return PDOStatement
     */
    public function read(?int $id = null): PDOStatement
    {
        $select = "id, nama_lengkap, username, created_at";

        if ($id !== null) {
            $query = "SELECT {$select} FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt  = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        } else {
            $query = "SELECT {$select} FROM {$this->table} ORDER BY id ASC";
            $stmt  = $this->conn->prepare($query);
        }

        $stmt->execute();
        return $stmt;
    }

    // ─── CREATE ────────────────────────────────────────────────────────────────

    /**
     * Menyimpan record admin baru ke database.
     * Password di-hash dengan bcrypt sebelum disimpan.
     *
     * @return bool  true jika berhasil, false jika gagal.
     */
    public function create(): bool
    {
        $query = "INSERT INTO {$this->table}
                      (nama_lengkap, username, password)
                  VALUES
                      (:nama_lengkap, :username, :password)";

        $stmt = $this->conn->prepare($query);

        // Sanitasi input
        $this->nama_lengkap = htmlspecialchars(strip_tags($this->nama_lengkap));
        $this->username     = htmlspecialchars(strip_tags($this->username));

        // Hash password sebelum disimpan
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindValue(":nama_lengkap", $this->nama_lengkap, PDO::PARAM_STR);
        $stmt->bindValue(":username",     $this->username,     PDO::PARAM_STR);
        $stmt->bindValue(":password",     $hashedPassword,     PDO::PARAM_STR);

        if ($stmt->execute()) {
            $this->id = (int) $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // ─── UPDATE ────────────────────────────────────────────────────────────────

    /**
     * Memperbarui data admin berdasarkan $this->id.
     * Jika $this->password kosong, password lama tidak akan berubah.
     *
     * @return bool  true jika berhasil, false jika gagal.
     */
    public function update(): bool
    {
        // Sanitasi input
        $this->nama_lengkap = htmlspecialchars(strip_tags($this->nama_lengkap));
        $this->username     = htmlspecialchars(strip_tags($this->username));

        if (!empty($this->password)) {
            // Update termasuk password baru
            $query = "UPDATE {$this->table}
                      SET
                          nama_lengkap = :nama_lengkap,
                          username     = :username,
                          password     = :password
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":password", password_hash($this->password, PASSWORD_BCRYPT), PDO::PARAM_STR);
        } else {
            // Update tanpa mengubah password
            $query = "UPDATE {$this->table}
                      SET
                          nama_lengkap = :nama_lengkap,
                          username     = :username
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindValue(":nama_lengkap", $this->nama_lengkap, PDO::PARAM_STR);
        $stmt->bindValue(":username",     $this->username,     PDO::PARAM_STR);
        $stmt->bindValue(":id",           $this->id,           PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ─── DELETE ────────────────────────────────────────────────────────────────

    /**
     * Menghapus satu admin berdasarkan $this->id.
     *
     * @return bool  true jika berhasil & ada baris terdampak, false jika gagal.
     */
    public function delete(): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt  = $this->conn->prepare($query);

        $this->id = (int) htmlspecialchars(strip_tags((string) $this->id));

        $stmt->bindValue(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
