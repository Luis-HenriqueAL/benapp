<?php

namespace Models;

use Config\Database;
use PDO;

class Liturgia {
    private $conn;
    private $table_name = "liturgias";

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findAll($celula_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id ORDER BY data_culto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function create($celula_id, $data_culto, $tema) {
        $query = "INSERT INTO " . $this->table_name . " (celula_id, data_culto, tema) VALUES (:celula_id, :data_culto, :tema)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->bindParam(':data_culto', $data_culto);
        $stmt->bindParam(':tema', $tema);
        return $stmt->execute();
    }

    public function findById($celula_id, $id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
