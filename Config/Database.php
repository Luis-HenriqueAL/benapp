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

            $hosts = $dbHostEnv ? [$dbHostEnv, 'db', '127.0.0.1', 'localhost'] : ['db', '127.0.0.1', 'localhost'];
            $users = $dbUserEnv ? [$dbUserEnv, 'root', 'postgres'] : ['root', 'postgres'];

            // Tenta conectar ao PostgreSQL com até 3 tentativas de retry (para evitar corrida na inicialização do container)
            for ($attempt = 1; $attempt <= 3; $attempt++) {
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
                if ($attempt < 3) {
                    sleep(1);
                }
            }

            // Fallback transparente para SQLite se o PostgreSQL não estiver acessível no ambiente local
            $sqliteDir = __DIR__ . '/../db';
            if (!is_dir($sqliteDir)) {
                @mkdir($sqliteDir, 0777, true);
            }
            @chmod($sqliteDir, 0777);
            $sqlitePath = $sqliteDir . '/benapp.sqlite';
            if (file_exists($sqlitePath)) {
                @chmod($sqlitePath, 0666);
            }
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
            self::ensureDefaultUsers($conn);
        } catch (\Exception $e) {
            // Ignora erro se tabelas já existirem
        }
    }

    /**
     * Garante que o usuário inicial admin@celula.com exista com a senha padrão 'senha123' válida.
     *
     * @param PDO $conn Conexão PDO ativa.
     * @return void
     */
    private static function ensureDefaultUsers(PDO $conn) {
        try {
            $defaultHash = '$2y$10$qDRL6sLNw6GMxZ05oketB.CNiy.fkpYpTpfXaw96hXRwqvwW3TR/q';
            $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => 'admin@celula.com']);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                $ins = $conn->prepare("INSERT INTO usuarios (celula_id, nome, email, senha, perfil, status) VALUES (1, 'Líder Principal', 'admin@celula.com', :senha, 'LIDER', 'ativo')");
                $ins->execute([':senha' => $defaultHash]);
            } else if (empty($admin['senha']) || !password_verify('senha123', $admin['senha'])) {
                $upd = $conn->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                $upd->execute([':senha' => $defaultHash, ':id' => $admin['id']]);
            }
        } catch (\Exception $e) {
            // Ignora exceções se tabela ainda não for válida
        }
    }
}
