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
     * @return bool Retorna verdadeiro se for criado com sucesso.
     */
    public function create($celula_id, $data_culto, $tema) {
        $query = "INSERT INTO " . $this->table_name . " (celula_id, data_culto, tema) VALUES (:celula_id, :data_culto, :tema)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->bindParam(':data_culto', $data_culto);
        $stmt->bindParam(':tema', $tema);
        return $stmt->execute();
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
