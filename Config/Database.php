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
     * Obtém ou inicializa a conexão com o banco de dados.
     *
     * @return PDO Instância ativa da conexão PDO.
     */
    public static function getConnection() {
        if (self::$connection === null) {
            $dbHostEnv = getenv('DB_HOST');
            $dbUserEnv = getenv('DB_USER');
            $dbPassEnv = getenv('DB_PASS');
            $dbNameEnv = getenv('DB_NAME') ?: 'benapp';
            $dbPortEnv = getenv('DB_PORT') ?: '5432';

            $hosts = $dbHostEnv ? [$dbHostEnv] : ['127.0.0.1', 'localhost', 'db'];
            $users = $dbUserEnv ? [$dbUserEnv] : ['postgres', 'root'];

            foreach ($hosts as $host) {
                foreach ($users as $user) {
                    $pass = $dbPassEnv !== false ? $dbPassEnv : ($user === 'root' ? 'rootpassword' : 'postgres');
                    try {
                        $dsn = "pgsql:host=$host;port=$dbPortEnv;dbname=$dbNameEnv;";
                        $options = [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ];
                        self::$connection = new PDO($dsn, $user, $pass, $options);
                        return self::$connection;
                    } catch (PDOException $e) {
                        // Tenta a próxima combinação de host/usuário
                    }
                }
            }

            // Fallback transparente para SQLite se o PostgreSQL não estiver acessível no ambiente local
            $sqliteDir = __DIR__ . '/../db';
            if (!is_dir($sqliteDir)) {
                @mkdir($sqliteDir, 0777, true);
            }
            $sqlitePath = $sqliteDir . '/benapp.sqlite';
            $dsn = "sqlite:" . $sqlitePath;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            self::$connection = new PDO($dsn, null, null, $options);
        }
        return self::$connection;
    }
}
