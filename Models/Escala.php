<?php

namespace Models;

use Config\Database;
use PDO;

class Escala {
    private $conn;
    private $table_name = "escalas";

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findByCelula($celula_id) {
        $query = "SELECT e.*, l.data_culto FROM " . $this->table_name . " e
                  JOIN liturgias l ON e.liturgia_id = l.id
                  WHERE l.celula_id = :celula_id
                  ORDER BY l.data_culto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function hasConflict($usuario_id, $liturgia_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id AND liturgia_id = :liturgia_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':liturgia_id', $liturgia_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }
    
    public function create($liturgia_id, $usuario_id, $funcao_id) {
        $query = "INSERT INTO " . $this->table_name . " (liturgia_id, usuario_id, funcao_id) VALUES (:liturgia_id, :usuario_id, :funcao_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':liturgia_id', $liturgia_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':funcao_id', $funcao_id);
        return $stmt->execute();
    }

    public function getLastMonthEscalas($celula_id) {
        $query = "SELECT e.*, l.data_culto FROM " . $this->table_name . " e
                  JOIN liturgias l ON e.liturgia_id = l.id
                  WHERE l.celula_id = :celula_id 
                  AND l.data_culto >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                  AND l.data_culto < CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
