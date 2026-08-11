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
            $driver = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS momentos_predefinidos (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        titulo VARCHAR(255) NOT NULL,
                        ordem INT NOT NULL DEFAULT 0,
                        duracao_minutos INT DEFAULT 15,
                        obrigatorio BOOLEAN DEFAULT FALSE,
                        is_louvor BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $this->conn->exec("
                    CREATE TABLE IF NOT EXISTS momentos_predefinidos (
                        id SERIAL PRIMARY KEY,
                        celula_id INT NOT NULL DEFAULT 1,
                        titulo VARCHAR(255) NOT NULL,
                        ordem INT NOT NULL DEFAULT 0,
                        duracao_minutos INT DEFAULT 15,
                        obrigatorio BOOLEAN DEFAULT FALSE,
                        is_louvor BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            }

            try { $this->conn->exec("ALTER TABLE momentos_predefinidos ADD COLUMN is_louvor BOOLEAN DEFAULT FALSE;"); } catch (\PDOException $e) {}

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM momentos_predefinidos WHERE celula_id = 1");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $this->conn->exec("
                    INSERT INTO momentos_predefinidos (celula_id, titulo, ordem, duracao_minutos, obrigatorio, is_louvor)
                    VALUES
                    (1, 'Quebra-Gelo / Recepção', 1, 15, FALSE, FALSE),
                    (1, 'Louvor e Adoração', 2, 20, FALSE, TRUE),
                    (1, 'Estudo / Palavra', 3, 40, TRUE, FALSE),
                    (1, 'Oração e Avisos', 4, 15, FALSE, FALSE);
                ");
            } else {
                try {
                    $this->conn->exec("UPDATE momentos_predefinidos SET is_louvor = TRUE WHERE (LOWER(titulo) LIKE '%louvor%' OR LOWER(titulo) LIKE '%música%') AND celula_id = 1;");
                } catch (\PDOException $e) {}
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
        if (!SecurityHelper::hasPermissao('liturgia.momentos')) {
            $_SESSION['flash_error'] = "Sem permissão para gerenciar os momentos da liturgia.";
            header("Location: " . SecurityHelper::getDefaultRoute());
            exit;
        }
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
        if (!SecurityHelper::hasPermissao('liturgia.momentos')) {
            $_SESSION['flash_error'] = "Sem permissão para gerenciar os momentos da liturgia.";
            header("Location: /");
            exit;
        }
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $titulo = trim($_POST['titulo'] ?? '');
        $is_louvor = !empty($_POST['is_louvor']) ? 1 : 0;

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
            INSERT INTO momentos_predefinidos (celula_id, titulo, ordem, is_louvor)
            VALUES (:celula_id, :titulo, :ordem, :is_louvor)
        ");
        $stmt->execute([
            ':celula_id' => $celula_id,
            ':titulo' => $titulo,
            ':ordem' => $maxOrdem + 1,
            ':is_louvor' => $is_louvor
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
        if (!SecurityHelper::hasPermissao('liturgia.momentos')) {
            $_SESSION['flash_error'] = "Sem permissão para gerenciar os momentos da liturgia.";
            header("Location: /");
            exit;
        }
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
