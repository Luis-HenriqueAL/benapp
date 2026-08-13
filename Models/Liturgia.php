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

    /**
     * Atualiza a data e o tema de uma liturgia existente.
     *
     * @param int $celula_id Identificador do tenant.
     * @param int $id Identificador único da liturgia.
     * @param string $data_culto Data do culto (Y-m-d).
     * @param string $tema Tema do culto.
     * @return bool Retorna verdadeiro se for atualizado com sucesso.
     */
    public function update($celula_id, $id, $data_culto, $tema) {
        $query = "UPDATE " . $this->table_name . " SET data_culto = :data_culto, data_liturgia = :data_culto, tema = :tema WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_culto', $data_culto);
        $stmt->bindParam(':tema', $tema);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca uma liturgia cadastrada para uma data de culto específica na célula.
     *
     * @param int $celula_id Identificador do tenant.
     * @param string $data_culto Data do culto (Y-m-d).
     * @param int|null $ignore_id Opcional ID da liturgia a ignorar (em caso de edição).
     * @return array|false Dados da liturgia ou false se não for encontrada.
     */
    public function findByDataCulto($celula_id, $data_culto, $ignore_id = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id AND DATE(data_culto) = DATE(:data_culto)";
        if ($ignore_id) {
            $query .= " AND id != :ignore_id";
        }
        $query .= " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        $stmt->bindParam(':data_culto', $data_culto);
        if ($ignore_id) {
            $stmt->bindParam(':ignore_id', $ignore_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna um array com todas as datas (Y-m-d) de eventos/liturgias já cadastrados para a célula.
     *
     * @param int $celula_id Identificador da célula.
     * @param int|null $ignore_id ID da liturgia atual (para ignorar na edição).
     * @return array Lista de datas no formato 'Y-m-d'.
     */
    public function getDatasCultoExistentes($celula_id, $ignore_id = null) {
        $query = "SELECT DATE(data_culto) as data_fmt FROM " . $this->table_name . " WHERE celula_id = :celula_id";
        if ($ignore_id) {
            $query .= " AND id != :ignore_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id, PDO::PARAM_INT);
        if ($ignore_id) {
            $stmt->bindParam(':ignore_id', $ignore_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($rows, 'data_fmt');
    }
}
