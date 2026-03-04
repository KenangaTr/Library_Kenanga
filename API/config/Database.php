<?php

/**
 * Database Connection Class (Singleton Pattern)
 *
 * Provides a single shared PDO instance for all API classes,
 * reusing the same credentials as the existing application config.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private string $host     = 'localhost';
    private string $dbName   = 'library_db';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8mb4';

    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->connection = new PDO($dsn, $this->username, $this->password, $options);
    }

    /**
     * Get the singleton instance of Database.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Return the active PDO connection.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
