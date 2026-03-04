<?php

/**
 * Class Database
 *
 * Mengelola koneksi ke database MySQL menggunakan PDO.
 * Menyimpan kredensial sebagai properti class dan
 * menyediakan method getConnection() untuk membuat koneksi.
 */
class Database
{
    // ── Kredensial Database ────────────────────────────────────────────────────
    private string $host     = "localhost";
    private string $db_name  = "library_db";
    private string $username = "root";
    private string $password = "";
    private string $charset  = "utf8mb4";

    // ── Objek koneksi PDO ──────────────────────────────────────────────────────
    private ?PDO $conn = null;

    /**
     * Membuat dan mengembalikan objek koneksi PDO.
     * Jika koneksi sudah ada, koneksi yang sama dikembalikan.
     *
     * @return PDO|null  Objek PDO jika koneksi berhasil, null jika gagal.
     */
    public function getConnection(): ?PDO
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Kembalikan null agar endpoint bisa mengembalikan 503
            $this->conn = null;
        }

        return $this->conn;
    }
}
