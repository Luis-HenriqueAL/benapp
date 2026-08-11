<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Presenca
 * Model responsável pelo registro de presenças de usuários em eventos/liturgias.
 */
class Presenca {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /** @var string Nome da tabela no banco de dados. */
    private $table_name = "presencas";

    /**
     * Construtor da classe Presenca.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureSchema();
    }

    /**
     * Garante a criação da tabela de presenças (idempotente, suporta PostgreSQL e SQLite).
     *
     * @return void
     */
    private function ensureSchema() {
        try {
            $driver = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS presencas (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        usuario_id INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE(liturgia_id, usuario_id)
                    );
                ");
            } else {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS presencas (
                        id SERIAL PRIMARY KEY,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        usuario_id INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        CONSTRAINT uq_presenca UNIQUE (liturgia_id, usuario_id)
                    );
                ");
            }
        } catch (\PDOException $e) {
            // Ignora se tabela já existir
        }
    }

    /**
     * Registra a presença de um usuário em um evento.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @param int $usuario_id Identificador do usuário.
     * @return bool Verdadeiro se inserido com sucesso.
     */
    public function registrar($celula_id, $liturgia_id, $usuario_id) {
        try {
            $query = "INSERT INTO {$this->table_name} (celula_id, liturgia_id, usuario_id) VALUES (:celula_id, :liturgia_id, :usuario_id)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':celula_id' => $celula_id, ':liturgia_id' => $liturgia_id, ':usuario_id' => $usuario_id]);
        } catch (\PDOException $e) {
            return false; // Ignora duplicata
        }
    }

    /**
     * Remove a presença de um usuário em um evento.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @param int $usuario_id Identificador do usuário.
     * @return bool Verdadeiro se removido com sucesso.
     */
    public function remover($celula_id, $liturgia_id, $usuario_id) {
        $query = "DELETE FROM {$this->table_name} WHERE celula_id = :celula_id AND liturgia_id = :liturgia_id AND usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':liturgia_id' => $liturgia_id, ':usuario_id' => $usuario_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se um usuário está confirmado em um evento.
     *
     * @param int $liturgia_id Identificador do evento.
     * @param int $usuario_id Identificador do usuário.
     * @return bool Verdadeiro se o usuário estiver confirmado.
     */
    public function jaConfirmado($liturgia_id, $usuario_id) {
        $query = "SELECT COUNT(*) as total FROM {$this->table_name} WHERE liturgia_id = :liturgia_id AND usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':liturgia_id' => $liturgia_id, ':usuario_id' => $usuario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['total'] ?? 0) > 0;
    }

    /**
     * Retorna todos os usuários confirmados em um evento com seus dados.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $liturgia_id Identificador do evento.
     * @return array Lista de usuários confirmados.
     */
    public function findByLiturgia($celula_id, $liturgia_id) {
        $query = "
            SELECT p.*, u.nome as usuario_nome, u.perfil as usuario_perfil
            FROM {$this->table_name} p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.celula_id = :celula_id AND p.liturgia_id = :liturgia_id
            ORDER BY p.created_at ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':celula_id' => $celula_id, ':liturgia_id' => $liturgia_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
