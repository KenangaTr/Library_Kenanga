<?php

require_once __DIR__ . '/config/Database.php';

/**
 * Class Admin
 *
 * Represents the `admins` table and exposes CRUD operations.
 * Passwords are always stored as bcrypt hashes — plain text is NEVER
 * persisted to the database.
 *
 * Table columns:
 *   id            INT  AUTO_INCREMENT PRIMARY KEY
 *   nama_lengkap  VARCHAR(100) NOT NULL
 *   username      VARCHAR(50)  NOT NULL UNIQUE
 *   password      VARCHAR(255) NOT NULL  (bcrypt hash)
 *   created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
 */
class Admin
{
    private PDO $db;
    private string $table = 'admins';

    // ─── Attributes ────────────────────────────────────────────────────────────
    public ?int    $id           = null;
    public string  $nama_lengkap = '';
    public string  $username     = '';
    public string  $password     = '';   // Plain text; will be hashed before storage
    public ?string $created_at   = null;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ─── Read All ──────────────────────────────────────────────────────────────

    /**
     * Fetch all admin accounts (password hash is excluded from the response).
     *
     * @param int $limit  Number of records per page (0 = no limit).
     * @param int $offset Starting offset.
     * @return array<int, array<string, mixed>>
     */
    public function getAll(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT id, nama_lengkap, username, created_at
                FROM {$this->table} ORDER BY id ASC";

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
     * Fetch a single admin by primary key (password excluded).
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function getById(int $id): array|false
    {
        $sql  = "SELECT id, nama_lengkap, username, created_at
                 FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    /**
     * Insert a new admin account.
     * $this->password must be set to the plain-text password before calling.
     * Populates $this->id on success.
     *
     * @return bool
     */
    public function create(): bool
    {
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO {$this->table}
                    (nama_lengkap, username, password)
                VALUES
                    (:nama_lengkap, :username, :password)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nama_lengkap', $this->nama_lengkap, PDO::PARAM_STR);
        $stmt->bindValue(':username',     $this->username,     PDO::PARAM_STR);
        $stmt->bindValue(':password',     $hashedPassword,     PDO::PARAM_STR);

        if ($stmt->execute()) {
            $this->id = (int) $this->db->lastInsertId();
            return true;
        }
        return false;
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    /**
     * Update an existing admin account.
     * If $this->password is not empty, the password will also be updated (re-hashed).
     *
     * @return bool
     */
    public function update(): bool
    {
        // Only update password when a new one is explicitly provided
        if (!empty($this->password)) {
            $sql = "UPDATE {$this->table}
                    SET nama_lengkap = :nama_lengkap,
                        username     = :username,
                        password     = :password
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':password', password_hash($this->password, PASSWORD_BCRYPT), PDO::PARAM_STR);
        } else {
            $sql = "UPDATE {$this->table}
                    SET nama_lengkap = :nama_lengkap,
                        username     = :username
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
        }

        $stmt->bindValue(':nama_lengkap', $this->nama_lengkap, PDO::PARAM_STR);
        $stmt->bindValue(':username',     $this->username,     PDO::PARAM_STR);
        $stmt->bindValue(':id',           $this->id,           PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ─── Delete ────────────────────────────────────────────────────────────────

    /**
     * Delete an admin by ID.
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

    // ─── Authentication / Helpers ─────────────────────────────────────────────

    /**
     * Verify a plain-text password against a stored hash.
     *
     * @param string $plainPassword
     * @param string $storedHash
     * @return bool
     */
    public function verifyPassword(string $plainPassword, string $storedHash): bool
    {
        return password_verify($plainPassword, $storedHash);
    }

    /**
     * Find an admin by username and return the full row including the hash
     * (for use in authentication flows only).
     *
     * @param string $username
     * @return array<string, mixed>|false
     */
    public function findByUsername(string $username): array|false
    {
        $sql  = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Check whether a username already exists (excluding a given admin ID).
     *
     * @param string $username
     * @param int    $excludeId Exclude this ID from the check (useful for updates).
     * @return bool
     */
    public function usernameExists(string $username, int $excludeId = 0): bool
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table}
                 WHERE username = :username AND id != :excludeId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username',  $username,  PDO::PARAM_STR);
        $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Count total number of admin records.
     *
     * @return int
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }
}
