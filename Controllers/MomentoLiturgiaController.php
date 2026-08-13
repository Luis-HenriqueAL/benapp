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
        $is_palavra = !empty($_POST['is_palavra']) ? 1 : 0;

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
            INSERT INTO momentos_predefinidos (celula_id, titulo, ordem, is_louvor, is_palavra)
            VALUES (:celula_id, :titulo, :ordem, :is_louvor, :is_palavra)
        ");
        $stmt->execute([
            ':celula_id' => $celula_id,
            ':titulo' => $titulo,
            ':ordem' => $maxOrdem + 1,
            ':is_louvor' => $is_louvor,
            ':is_palavra' => $is_palavra
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
