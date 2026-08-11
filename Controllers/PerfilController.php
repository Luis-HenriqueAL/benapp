<?php

namespace Controllers;

use Models\Perfil;
use Helpers\SecurityHelper;

/**
 * Class PerfilController
 * Gerencia os perfis customizados da célula e suas permissões por módulo.
 * Acesso restrito ao perfil LIDER.
 */
class PerfilController {
    /** @var Perfil Instância do model Perfil. */
    private $perfilModel;

    /**
     * Construtor do PerfilController.
     */
    public function __construct() {
        $this->perfilModel = new Perfil();
    }

    /**
     * Verifica se o usuário autenticado possui perfil LIDER.
     * Redireciona para a home com mensagem de erro em caso de acesso não autorizado.
     *
     * @return void
     */
    private function requireLider() {
        $perfil = $_SESSION['user']['perfil'] ?? '';
        if ($perfil !== 'LIDER') {
            $_SESSION['flash_error'] = "Acesso restrito: apenas líderes podem gerenciar perfis.";
            header("Location: /");
            exit;
        }
    }

    /**
     * Lista todos os perfis customizados da célula com suas permissões atribuídas.
     *
     * @return void
     */
    public function index() {
        $this->requireLider();
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $perfis = $this->perfilModel->findAll($celula_id);
        $permissoesDisponiveis = Perfil::$permissoesDisponiveis;
        require_once __DIR__ . '/../Views/perfil/index.php';
    }

    /**
     * Exibe o formulário de criação de um novo perfil customizado.
     *
     * @return void
     */
    public function create() {
        $this->requireLider();
        $permissoesDisponiveis = Perfil::$permissoesDisponiveis;
        $perfilEdicao = null;
        require_once __DIR__ . '/../Views/perfil/form.php';
    }

    /**
     * Exibe o formulário de edição de um perfil existente preenchido com os dados atuais.
     *
     * @return void
     */
    public function edit() {
        $this->requireLider();
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = (int)($_GET['id'] ?? 0);

        $perfilEdicao = $this->perfilModel->findById($celula_id, $id);
        if (!$perfilEdicao) {
            $_SESSION['flash_error'] = "Perfil não encontrado.";
            header("Location: /perfil");
            exit;
        }

        $permissoesDisponiveis = Perfil::$permissoesDisponiveis;
        require_once __DIR__ . '/../Views/perfil/form.php';
    }

    /**
     * Persiste um novo perfil no banco de dados após validação de CSRF e permissões.
     *
     * @return void
     */
    public function store() {
        $this->requireLider();
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id = $_SESSION['celula_id'] ?? 1;
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $permissoes = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];

        if (empty($nome)) {
            $_SESSION['flash_error'] = "O nome do perfil é obrigatório.";
            header("Location: /perfil/create");
            exit;
        }

        $this->perfilModel->create($celula_id, $nome, $descricao, $permissoes);
        $_SESSION['flash_success'] = "Perfil \"{$nome}\" criado com sucesso.";
        header("Location: /perfil");
        exit;
    }

    /**
     * Atualiza os dados e permissões de um perfil existente após validação.
     *
     * @return void
     */
    public function update() {
        $this->requireLider();
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $permissoes = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];

        if (!$id || empty($nome)) {
            $_SESSION['flash_error'] = "Dados inválidos para atualização.";
            header("Location: /perfil");
            exit;
        }

        $this->perfilModel->update($celula_id, $id, $nome, $descricao, $permissoes);
        $_SESSION['flash_success'] = "Perfil atualizado com sucesso.";
        header("Location: /perfil");
        exit;
    }

    /**
     * Remove um perfil customizado da célula após validação de CSRF e permissões.
     *
     * @return void
     */
    public function delete() {
        $this->requireLider();
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = "ID de perfil inválido.";
            header("Location: /perfil");
            exit;
        }

        $deleted = $this->perfilModel->delete($celula_id, $id);
        if ($deleted) {
            $_SESSION['flash_success'] = "Perfil removido com sucesso.";
        } else {
            $_SESSION['flash_error'] = "Perfil não encontrado ou não pertence à sua célula.";
        }

        header("Location: /perfil");
        exit;
    }
}
