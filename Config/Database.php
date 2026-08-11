<?php

namespace Config;

use PDO;
use PDOException;

/**
 * Class Database
 * Responsável pelo gerenciamento de conexão PDO singleton com o PostgreSQL.
 */
class Database {
    /**
     * @var PDO|null Instância singleton da conexão PDO.
     */
    private static $connection = null;

    /**
     * Obtém ou inicializa a conexão com o banco de dados PostgreSQL.
     *
     * @throws \Exception Se a conexão falhar.
     * @return PDO Instância ativa da conexão PDO.
     */
    public static function getConnection() {
        if (self::$connection === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $port = getenv('DB_PORT') ?: '5432';
            $db_name = getenv('DB_NAME') ?: 'benapp';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: 'rootpassword';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db_name;";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
