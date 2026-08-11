<?php

namespace Models;

use Config\Database;
use PDO;

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureSchema();
    }

    private function ensureSchema() {
        try {
            // Garante a criação da tabela base se não existir
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS usuarios (
                    id SERIAL PRIMARY KEY,
                    celula_id INT NOT NULL DEFAULT 1,
                    nome VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE,
                    senha VARCHAR(255),
                    perfil VARCHAR(50) DEFAULT 'MEMBRO',
                    status VARCHAR(20) DEFAULT 'ativo'
                );
            ");

            // Executa migrations para colunas adicionadas
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS email VARCHAR(255) UNIQUE;");
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS senha VARCHAR(255);");
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS perfil VARCHAR(50) DEFAULT 'MEMBRO';");
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'ativo';");

            // Seed de usuários se não houver registros
            $stmt = $this->conn->query("SELECT COUNT(*) FROM usuarios");
            if ($stmt->fetchColumn() == 0) {
                $hash = password_hash('senha123', PASSWORD_BCRYPT);
                $stmtInsert = $this->conn->prepare("
                    INSERT INTO usuarios (celula_id, nome, email, senha, perfil, status)
                    VALUES (1, 'Líder Principal', 'admin@celula.com', :hash, 'LIDER', 'ativo')
                    ON CONFLICT (email) DO NOTHING;
                ");
                $stmtInsert->execute([':hash' => $hash]);
            }
        } catch (\PDOException $e) {
            // Ignora erro em migração automática em tempo de execução
        }
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByCelulaId($celula_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($celula_id, $id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND celula_id = :celula_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($celula_id, $data) {
        $query = "INSERT INTO " . $this->table_name . " (celula_id, nome, email, senha, perfil, status) 
                  VALUES (:celula_id, :nome, :email, :senha, :perfil, :status) RETURNING id";
        
        $senhaHash = password_hash($data['senha'], PASSWORD_BCRYPT);
        $perfil = $data['perfil'] ?? 'MEMBRO';
        $status = $data['status'] ?? 'ativo';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':senha', $senhaHash);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function update($celula_id, $id, $data) {
        if (!empty($data['senha'])) {
            $query = "UPDATE " . $this->table_name . " 
                      SET nome = :nome, email = :email, senha = :senha, perfil = :perfil, status = :status 
                      WHERE id = :id AND celula_id = :celula_id";
            $senhaHash = password_hash($data['senha'], PASSWORD_BCRYPT);
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':senha', $senhaHash);
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET nome = :nome, email = :email, perfil = :perfil, status = :status 
                      WHERE id = :id AND celula_id = :celula_id";
            $stmt = $this->conn->prepare($query);
        }

        $perfil = $data['perfil'] ?? 'MEMBRO';
        $status = $data['status'] ?? 'ativo';

        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);

        return $stmt->execute();
    }

    public function delete($celula_id, $id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);
        return $stmt->execute();
    }
}
