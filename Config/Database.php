<?php

namespace Config;

use PDO;
use PDOException;

/**
 * Class Database
 * Responsável pelo gerenciamento de conexão PDO singleton com o PostgreSQL e SQLite.
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
                        self::initializeDbIfEmpty(self::$connection, 'pgsql');
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
            self::initializeDbIfEmpty(self::$connection, 'sqlite');
        }
        return self::$connection;
    }

    /**
     * Executa o script DDL centralizado na pasta db/ apenas se o banco for novo e não possuir tabelas.
     *
     * @param PDO $conn Conexão PDO ativa.
     * @param string $driver Nome do driver PDO ('pgsql' ou 'sqlite').
     * @return void
     */
    private static function initializeDbIfEmpty(PDO $conn, $driver) {
        try {
            if ($driver === 'sqlite') {
                $stmt = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
                if (!$stmt || !$stmt->fetch()) {
                    $sql = @file_get_contents(__DIR__ . '/../db/schema_sqlite.sql');
                    if ($sql) {
                        $conn->exec($sql);
                    }
                }
            } else {
                $stmt = $conn->query("SELECT to_regclass('public.usuarios')");
                if (!$stmt || !$stmt->fetchColumn()) {
                    $sql = @file_get_contents(__DIR__ . '/../db/init.sql');
                    if ($sql) {
                        $conn->exec($sql);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignora erro se tabelas já existirem
        }
    }
}
