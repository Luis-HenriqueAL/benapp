<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Liturgia
 * Model responsável pelo gerenciamento de liturgias e cultos por célula.
 */
class Liturgia {
    /**
     * @var PDO Conexão PDO com o banco de dados.
     */
    private $conn;

    /**
     * @var string Nome da tabela no banco de dados.
     */
    private $table_name = "liturgias";

    /**
     * Construtor da classe Liturgia.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureSchema();
    }

    /**
     * Garante a criação da tabela e colunas data_culto, data_liturgia e tema no PostgreSQL / SQLite.
     *
     * @return void
     */
    private function ensureSchema() {
        try {
            $driver = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS liturgias (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        data_culto DATE NOT NULL DEFAULT CURRENT_DATE,
                        data_liturgia DATE,
                        tema VARCHAR(255)
                    );
                ");
            } else {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS liturgias (
                        id SERIAL PRIMARY KEY,
                        celula_id INT NOT NULL DEFAULT 1,
                        data_culto DATE NOT NULL DEFAULT CURRENT_DATE,
                        data_liturgia DATE,
                        tema VARCHAR(255)
                    );
                ");

                $this->conn->exec("ALTER TABLE liturgias ADD COLUMN IF NOT EXISTS data_culto DATE DEFAULT CURRENT_DATE;");
                $this->conn->exec("ALTER TABLE liturgias ADD COLUMN IF NOT EXISTS data_liturgia DATE;");
                $this->conn->exec("ALTER TABLE liturgias ADD COLUMN IF NOT EXISTS tema VARCHAR(255);");
            }
        } catch (\PDOException $e) {
            // Ignora se tabela/coluna já existir
        }
    }

    /**
     * Busca todas as liturgias cadastradas de uma célula.
     *
     * @param int $celula_id Identificador do tenant (célula).
     * @return array Lista de liturgias encontradas.
     */
    public function findAll($celula_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id ORDER BY data_culto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Cadastra uma nova liturgia de culto para a célula.
     *
     * @param int $celula_id Identificador do tenant.
     * @param string $data_culto Data do culto (Y-m-d).
     * @param string $tema Tema do culto.
     * @return bool|int Retorna o ID gerado se for criado com sucesso.
     */
    public function create($celula_id, $data_culto, $tema) {
        $query = "INSERT INTO " . $this->table_name . " (celula_id, data_culto, data_liturgia, tema) VALUES (:celula_id, :data_culto, :data_liturgia, :tema)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->bindParam(':data_culto', $data_culto);
        $stmt->bindParam(':data_liturgia', $data_culto);
        $stmt->bindParam(':tema', $tema);
        if ($stmt->execute()) {
            $lastId = $this->conn->lastInsertId();
            return $lastId ? (int)$lastId : true;
        }
        return false;
    }

    /**
     * Remove uma liturgia (e suas escalas via CASCADE) garantindo isolamento multi-tenant.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @param int $id Identificador da liturgia a ser removida.
     * @return bool Retorna verdadeiro se deletado com sucesso.
     */
    public function delete($celula_id, $id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':celula_id', $celula_id, \PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Busca uma liturgia específica por ID e célula.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador único da liturgia.
     * @return array|false Dados da liturgia ou false se não for encontrada.
     */
    public function findById($celula_id, $id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
