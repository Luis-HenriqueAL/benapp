<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Class Escala
 * Model responsável pela gestão de escalas de voluntários e validação de conflitos.
 */
class Escala {
    /**
     * @var PDO Conexão PDO com o banco de dados.
     */
    private $conn;

    /**
     * @var string Nome da tabela no banco de dados.
     */
    private $table_name = "escalas";

    /**
     * Construtor da classe Escala.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Busca todas as escalas associadas a uma célula específica.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @return array Lista de escalas encontradas.
     */
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

    /**
     * Verifica se um usuário voluntário possui conflito de horário/função na mesma liturgia.
     *
     * @param int $usuario_id Identificador do voluntário.
     * @param int $liturgia_id Identificador do culto/liturgia.
     * @return bool Retorna verdadeiro se houver conflito.
     */
    public function hasConflict($usuario_id, $liturgia_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id AND liturgia_id = :liturgia_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':liturgia_id', $liturgia_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return ($row['total'] ?? 0) > 0;
    }
    
    /**
     * Cria um novo registro de atribuição de voluntário em uma escala.
     *
     * @param int $liturgia_id Identificador da liturgia.
     * @param int $usuario_id Identificador do usuário alocado.
     * @param int|string $funcao_id Identificador da função/momento.
     * @return bool Retorna verdadeiro se for criado com sucesso.
     */
    public function create($liturgia_id, $usuario_id, $funcao_id) {
        $query = "INSERT INTO " . $this->table_name . " (liturgia_id, usuario_id, funcao_id) VALUES (:liturgia_id, :usuario_id, :funcao_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':liturgia_id', $liturgia_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':funcao_id', $funcao_id);
        return $stmt->execute();
    }

    /**
     * Obtém o histórico de escalas executadas no último mês para geração de sugestões automáticas.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @return array Lista de escalas passadas do último mês.
     */
    public function getLastMonthEscalas($celula_id) {
        $query = "SELECT e.*, l.data_culto FROM " . $this->table_name . " e
                  JOIN liturgias l ON e.liturgia_id = l.id
                  WHERE l.celula_id = :celula_id 
                  AND l.data_culto >= (CURRENT_DATE - INTERVAL '1 month')
                  AND l.data_culto < CURRENT_DATE";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
