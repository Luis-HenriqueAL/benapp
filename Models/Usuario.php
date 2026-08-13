<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Usuario
 * Model responsável pela persistência e manipulação dos dados de usuários (voluntários e líderes).
 */
class Usuario {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /** @var string Nome da tabela no banco de dados. */
    private $table_name = "usuarios";

    /**
     * Construtor da classe Usuario.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca um usuário pelo e-mail.
     *
     * @param string $email E-mail do usuário.
     * @return array|false Registro do usuário ou false se não encontrado.
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Busca todos os usuários pertencentes a uma célula específica.
     *
     * @param int $celula_id Identificador da célula.
     * @return array Lista de usuários da célula.
     */
    public function findByCelulaId($celula_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Alias para findByCelulaId para compatibilidade com chamadas no Controller.
     *
     * @param int $celula_id Identificador da célula.
     * @return array Lista de usuários da célula.
     */
    public function findByCelula($celula_id) {
        return $this->findByCelulaId($celula_id);
    }

    /**
     * Busca um usuário por ID garantindo o isolamento de célula.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $id Identificador do usuário.
     * @return array|false Dados do usuário ou false.
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
     * Cria um novo registro de usuário com senha criptografada em BCrypt.
     *
     * @param array $data Dados contendo celula_id, nome, email, senha, perfil, is_lider_principal.
     * @return bool Retorna verdadeiro se for criado com sucesso.
     */
    public function create($data) {
        $celula_id = (int)$data['celula_id'];
        $isLiderPrincipal = !empty($data['is_lider_principal']) ? 1 : 0;

        if ($isLiderPrincipal) {
            $this->resetLiderPrincipal($celula_id);
        }

        $query = "INSERT INTO " . $this->table_name . " (celula_id, nome, email, senha, perfil, status, is_lider_principal) VALUES (:celula_id, :nome, :email, :senha, :perfil, 'ativo', :is_lider_principal)";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($data['senha'], PASSWORD_BCRYPT);
        $perfil = $data['perfil'] ?? 'MEMBRO';

        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':senha', $hash);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':is_lider_principal', $isLiderPrincipal, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Atualiza os dados de um usuário existente na célula.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $id Identificador do usuário.
     * @param array $data Dados a serem atualizados (nome, email, perfil, status, is_lider_principal, senha opcional).
     * @return bool Retorna verdadeiro se for atualizado com sucesso.
     */
    public function update($celula_id, $id, $data) {
        $isLiderPrincipal = !empty($data['is_lider_principal']) ? 1 : 0;

        if ($isLiderPrincipal) {
            $this->resetLiderPrincipal($celula_id);
        }

        if (!empty($data['senha'])) {
            $query = "UPDATE " . $this->table_name . " SET nome = :nome, email = :email, senha = :senha, perfil = :perfil, status = :status, is_lider_principal = :is_lider_principal WHERE id = :id AND celula_id = :celula_id";
            $stmt = $this->conn->prepare($query);
            $hash = password_hash($data['senha'], PASSWORD_BCRYPT);
            $stmt->bindParam(':senha', $hash);
        } else {
            $query = "UPDATE " . $this->table_name . " SET nome = :nome, email = :email, perfil = :perfil, status = :status, is_lider_principal = :is_lider_principal WHERE id = :id AND celula_id = :celula_id";
            $stmt = $this->conn->prepare($query);
        }

        $status = $data['status'] ?? 'ativo';

        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':perfil', $data['perfil']);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':is_lider_principal', $isLiderPrincipal, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);

        return $stmt->execute();
    }

    /**
     * Reseta a flag is_lider_principal de todos os usuários de uma célula.
     *
     * @param int $celula_id
     * @return bool
     */
    public function resetLiderPrincipal($celula_id) {
        $query = "UPDATE " . $this->table_name . " SET is_lider_principal = 0 WHERE celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        return $stmt->execute();
    }

    /**
     * Localiza o usuário marcado como Líder Principal ativo na célula.
     * Caso não haja nenhum com is_lider_principal = 1/true, retorna o primeiro usuário ativo com perfil 'LIDER'.
     * Caso ainda assim não haja, retorna o primeiro usuário ativo da célula.
     *
     * @param int $celula_id Identificador da célula.
     * @return array|false Dados do usuário líder principal.
     */
    public function findLiderPrincipalByCelula($celula_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id AND is_lider_principal = 1 AND (status = 'ativo' OR status IS NULL) LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user;
        }

        $queryLider = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id AND UPPER(perfil) = 'LIDER' AND (status = 'ativo' OR status IS NULL) ORDER BY id ASC LIMIT 1";
        $stmtLider = $this->conn->prepare($queryLider);
        $stmtLider->bindParam(':celula_id', $celula_id);
        $stmtLider->execute();
        $userLider = $stmtLider->fetch(PDO::FETCH_ASSOC);

        if ($userLider) {
            return $userLider;
        }

        $queryQualquer = "SELECT * FROM " . $this->table_name . " WHERE celula_id = :celula_id AND (status = 'ativo' OR status IS NULL) ORDER BY id ASC LIMIT 1";
        $stmtQualquer = $this->conn->prepare($queryQualquer);
        $stmtQualquer->bindParam(':celula_id', $celula_id);
        $stmtQualquer->execute();
        return $stmtQualquer->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Realiza soft delete alterando o status do usuário para 'inativo'.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $id Identificador do usuário.
     * @return bool Retorna verdadeiro se o status for alterado com sucesso.
     */
    public function delete($celula_id, $id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'inativo' WHERE id = :id AND celula_id = :celula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':celula_id', $celula_id);
        return $stmt->execute();
    }
}
