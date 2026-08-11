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
        $this->ensureSchema();
    }

    /**
     * Garante a criação e adequação de colunas da tabela escalas.
     *
     * @return void
     */
    private function ensureSchema() {
        try {
            $driver = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS escalas (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        usuario_id INT NOT NULL,
                        funcao_id VARCHAR(100) NOT NULL,
                        data_escala TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS escalas (
                        id SERIAL PRIMARY KEY,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        usuario_id INT NOT NULL,
                        funcao_id VARCHAR(100) NOT NULL,
                        data_escala TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
                $this->conn->exec("ALTER TABLE escalas ADD COLUMN IF NOT EXISTS usuario_id INT;");
                $this->conn->exec("ALTER TABLE escalas ADD COLUMN IF NOT EXISTS funcao_id VARCHAR(100);");
                $this->conn->exec("ALTER TABLE escalas ADD COLUMN IF NOT EXISTS celula_id INT DEFAULT 1;");
                $this->conn->exec("ALTER TABLE escalas ALTER COLUMN data_escala SET DEFAULT CURRENT_TIMESTAMP;");
                $this->conn->exec("ALTER TABLE escalas ALTER COLUMN data_escala DROP NOT NULL;");
            }
        } catch (\PDOException $e) {
            // Ignora se tabela/coluna já existir
        }
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
     * Busca os eventos/cultos criados na célula com a contagem real de voluntários alocados.
     *
     * @param int $celula_id Identificador da célula.
     * @return array Lista de cultos e resumo de voluntários.
     */
    public function getEscalasComLiturgia($celula_id) {
        $query = "
            SELECT l.id as liturgia_id, l.data_culto, l.tema, 
                   COUNT(e.id) as total_voluntarios
            FROM liturgias l
            LEFT JOIN escalas e ON l.id = e.liturgia_id
            WHERE l.celula_id = :celula_id
            GROUP BY l.id, l.data_culto, l.tema
            ORDER BY l.data_culto DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca o detalhamento completo de uma liturgia e suas atribuições de momentos/voluntários.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $liturgia_id Identificador do evento/liturgia.
     * @return array|false Dados do evento e lista de voluntários alocados.
     */
    public function getLiturgiaDetails($celula_id, $liturgia_id) {
        $queryLit = "SELECT * FROM liturgias WHERE id = :liturgia_id AND celula_id = :celula_id LIMIT 1";
        $stmtLit = $this->conn->prepare($queryLit);
        $stmtLit->execute([':liturgia_id' => $liturgia_id, ':celula_id' => $celula_id]);
        $liturgia = $stmtLit->fetch(PDO::FETCH_ASSOC);

        if (!$liturgia) return false;

        $queryEsc = "
            SELECT e.*, u.nome as voluntario_nome, u.email as voluntario_email, u.perfil as voluntario_perfil
            FROM escalas e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            WHERE e.liturgia_id = :liturgia_id AND e.celula_id = :celula_id
            ORDER BY e.id ASC
        ";
        $stmtEsc = $this->conn->prepare($queryEsc);
        $stmtEsc->execute([':liturgia_id' => $liturgia_id, ':celula_id' => $celula_id]);
        $atribuicoes = $stmtEsc->fetchAll(PDO::FETCH_ASSOC);

        $liturgia['atribuicoes'] = $atribuicoes;
        return $liturgia;
    }

    /**
     * Verifica se um usuário voluntário possui conflito de horário/função na mesma liturgia.
     *
     * @param int $usuario_id Identificador do voluntário.
     * @param int $liturgia_id Identificador do culto/liturgia.
     * @param int|null $celula_id Identificador da célula (tenant).
     * @return bool Retorna verdadeiro se houver conflito.
     */
    public function hasConflict($usuario_id, $liturgia_id, $celula_id = null) {
        if ($celula_id !== null) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                      WHERE usuario_id = :usuario_id AND liturgia_id = :liturgia_id AND celula_id = :celula_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':celula_id', $celula_id);
        } else {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                      WHERE usuario_id = :usuario_id AND liturgia_id = :liturgia_id";
            $stmt = $this->conn->prepare($query);
        }
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':liturgia_id', $liturgia_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return ($row['total'] ?? 0) > 0;
    }
    
    /**
     * Cria um novo registro de atribuição de voluntário em uma escala vinculando celula_id (tenant) e data_escala.
     *
     * @param int $liturgia_id Identificador da liturgia.
     * @param int $usuario_id Identificador do usuário alocado.
     * @param int|string $funcao_id Identificador da função/momento.
     * @param int $celula_id Identificador da célula (tenant).
     * @return bool Retorna verdadeiro se for criado com sucesso.
     */
    public function create($liturgia_id, $usuario_id, $funcao_id, $celula_id = 1) {
        $query = "INSERT INTO " . $this->table_name . " (celula_id, liturgia_id, usuario_id, funcao_id, data_escala) VALUES (:celula_id, :liturgia_id, :usuario_id, :funcao_id, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celula_id', $celula_id);
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

    /**
     * Obtém os momentos litúrgicos predefinidos (templates) para a célula.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @return array Lista de momentos predefinidos.
     */
    public function getMomentosPredefinidos($celula_id) {
        try {
            $query = "SELECT * FROM momentos_predefinidos WHERE celula_id = :celula_id ORDER BY ordem ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':celula_id', $celula_id);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                return $results;
            }
        } catch (\PDOException $e) {
            // Em caso de falha de conexão ou tabela omissa em ambiente de teste
        }

        return [
            ['id' => 1, 'titulo' => 'Quebra-Gelo / Recepção', 'ordem' => 1, 'duracao_minutos' => 15, 'obrigatorio' => false],
            ['id' => 2, 'titulo' => 'Louvor e Adoração', 'ordem' => 2, 'duracao_minutos' => 20, 'obrigatorio' => false],
            ['id' => 3, 'titulo' => 'Estudo / Palavra', 'ordem' => 3, 'duracao_minutos' => 40, 'obrigatorio' => true],
            ['id' => 4, 'titulo' => 'Oração e Avisos', 'ordem' => 4, 'duracao_minutos' => 15, 'obrigatorio' => false]
        ];
    }
}
