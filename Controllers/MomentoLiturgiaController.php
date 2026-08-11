<?php

namespace Controllers;

use Config\Database;
use Helpers\SecurityHelper;
use PDO;

/**
 * Class MomentoLiturgiaController
 * Gerencia os momentos predefinidos da liturgia (templates) para a célula.
 */
class MomentoLiturgiaController {
    /** @var PDO Conexão PDO com o banco de dados. */
    private $conn;

    /**
     * Construtor do MomentoLiturgiaController.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureSchema();
    }

    /**
     * Garante a existência da tabela momentos_predefinidos no banco de dados.
     *
     * @return void
     */
    private function ensureSchema() {
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS momentos_predefinidos (
                    id SERIAL PRIMARY KEY,
                    celula_id INT NOT NULL DEFAULT 1,
                    titulo VARCHAR(255) NOT NULL,
                    ordem INT NOT NULL DEFAULT 0,
                    duracao_minutos INT DEFAULT 15,
                    obrigatorio BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ");

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM momentos_predefinidos WHERE celula_id = 1");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $this->conn->exec("
                    INSERT INTO momentos_predefinidos (celula_id, titulo, ordem, duracao_minutos, obrigatorio)
                    VALUES
                    (1, 'Quebra-Gelo / Recepção', 1, 15, FALSE),
                    (1, 'Louvor e Adoração', 2, 20, FALSE),
                    (1, 'Estudo / Palavra', 3, 40, TRUE),
                    (1, 'Oração e Avisos', 4, 15, FALSE);
                ");
            }
        } catch (\PDOException $e) {
            // Ignora se tabela já existir
        }
    }

    /**
     * Exibe a tela de gerenciamento de momentos predefinidos da liturgia.
     *
     * @return void
     */
    public function index() {
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $stmt = $this->conn->prepare("SELECT * FROM momentos_predefinidos WHERE celula_id = :celula_id ORDER BY ordem ASC, id ASC");
        $stmt->execute([':celula_id' => $celula_id]);
        $momentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/liturgia/momentos.php';
    }

    /**
     * Cadastra um novo momento predefinido para a liturgia da célula.
     *
     * @return void
     */
    public function store() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $titulo = trim($_POST['titulo'] ?? '');

        if (empty($titulo)) {
            $_SESSION['flash_error'] = "O nome/descrição do momento é obrigatório.";
            header("Location: /liturgia/momentos");
            exit;
        }

        // Obtém a maior ordem atual para colocar ao final
        $stmtMax = $this->conn->prepare("SELECT MAX(ordem) FROM momentos_predefinidos WHERE celula_id = :celula_id");
        $stmtMax->execute([':celula_id' => $celula_id]);
        $maxOrdem = (int)$stmtMax->fetchColumn();

        $stmt = $this->conn->prepare("
            INSERT INTO momentos_predefinidos (celula_id, titulo, ordem)
            VALUES (:celula_id, :titulo, :ordem)
        ");
        $stmt->execute([
            ':celula_id' => $celula_id,
            ':titulo' => $titulo,
            ':ordem' => $maxOrdem + 1
        ]);

        $_SESSION['flash_success'] = "Momento '{$titulo}' cadastrado com sucesso!";
        header("Location: /liturgia/momentos");
        exit;
    }

    /**
     * Remove um momento predefinido da liturgia.
     *
     * @return void
     */
    public function delete() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $this->conn->prepare("DELETE FROM momentos_predefinidos WHERE id = :id AND celula_id = :celula_id AND obrigatorio = FALSE");
            $stmt->execute([':id' => $id, ':celula_id' => $celula_id]);
            $_SESSION['flash_success'] = "Momento removido com sucesso!";
        }

        header("Location: /liturgia/momentos");
        exit;
    }
}
