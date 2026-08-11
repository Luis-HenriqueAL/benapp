<?php

namespace Controllers;

use Models\Usuario;
use Helpers\SecurityHelper;

/**
 * Class UsuarioController
 * Gerencia as operações de CRUD de membros e líderes da célula.
 */
class UsuarioController {
    /** @var Usuario Instância do model Usuario. */
    private $usuarioModel;

    /**
     * Construtor do UsuarioController.
     */
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Redireciona com erro se o usuário não possuir a permissão informada.
     *
     * @param string $chave Chave da permissão.
     * @return void
     */
    private function requirePermissao($chave) {
        if (!SecurityHelper::hasPermissao($chave)) {
            $_SESSION['flash_error'] = "Sem permissão para executar esta ação.";
            header("Location: /usuarios");
            exit;
        }
    }

    /**
     * Exibe a listagem de usuários da célula.
     *
     * @return void
     */
    public function index() {
        if (!SecurityHelper::hasPermissao('usuarios.view')) {
            $_SESSION['flash_error'] = "Sem permissão para visualizar membros.";
            header("Location: " . SecurityHelper::getDefaultRoute());
            exit;
        }
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $usuarios = $this->usuarioModel->findByCelulaId($celula_id);
        require_once __DIR__ . '/../Views/usuarios/index.php';
    }

    /**
     * Exibe o formulário de cadastro de novo usuário.
     *
     * @return void
     */
    public function create() {
        $this->requirePermissao('usuarios.manage');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $perfilModel = new \Models\Perfil();
        $perfilsCustomizados = $perfilModel->findAll($celula_id);
        require_once __DIR__ . '/../Views/usuarios/create.php';
    }

    /**
     * Processa o formulário de cadastro de usuário e persiste no banco de dados.
     *
     * @return void
     */
    public function store() {
        $this->requirePermissao('usuarios.manage');
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;

        $email = trim($_POST['email'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $perfil = trim($_POST['perfil'] ?? 'MEMBRO');

        if (empty($email) || empty($nome) || empty($senha)) {
            $_SESSION['flash_error'] = "Por favor, preencha todos os campos obrigatórios.";
            header("Location: /usuarios/create");
            exit;
        }

        $existente = $this->usuarioModel->findByEmail($email);
        if ($existente) {
            $_SESSION['flash_error'] = "Este e-mail já está em uso.";
            header("Location: /usuarios/create");
            exit;
        }

        $this->usuarioModel->create([
            'celula_id' => $celula_id,
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'perfil' => $perfil
        ]);

        header("Location: /usuarios");
        exit;
    }

    /**
     * Exibe o formulário de edição de um usuário existente (ou do próprio perfil logado).
     *
     * @return void
     */
    public function edit() {
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $current_user_id = $_SESSION['user']['id'] ?? null;
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: /usuarios");
            exit;
        }

        $isSelf = ($current_user_id && (int)$id === (int)$current_user_id);
        if (!SecurityHelper::hasPermissao('usuarios.manage') && !$isSelf) {
            $_SESSION['flash_error'] = "Sem permissão para editar este usuário.";
            header("Location: /usuarios");
            exit;
        }

        $usuario = $this->usuarioModel->findById($celula_id, $id);
        if (!$usuario) {
            $_SESSION['flash_error'] = "Usuário não encontrado.";
            header("Location: /usuarios");
            exit;
        }

        $perfilModel = new \Models\Perfil();
        $perfilsCustomizados = $perfilModel->findAll($celula_id);
        require_once __DIR__ . '/../Views/usuarios/edit.php';
    }

    /**
     * Processa a atualização cadastral de um usuário (ou do próprio perfil logado).
     *
     * @return void
     */
    public function update() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $current_user_id = $_SESSION['user']['id'] ?? null;
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header("Location: /usuarios");
            exit;
        }

        $isSelf = ($current_user_id && (int)$id === (int)$current_user_id);
        if (!SecurityHelper::hasPermissao('usuarios.manage') && !$isSelf) {
            $_SESSION['flash_error'] = "Sem permissão para atualizar este usuário.";
            header("Location: /usuarios");
            exit;
        }

        $existing = $this->usuarioModel->findById($celula_id, $id);
        if (!$existing) {
            $_SESSION['flash_error'] = "Usuário não encontrado.";
            header("Location: /usuarios");
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if (empty($nome) || empty($email)) {
            $_SESSION['flash_error'] = "Nome e E-mail são obrigatórios.";
            header("Location: /usuarios/edit?id=" . $id);
            exit;
        }

        // Apenas admins com usuarios.manage podem alterar perfil e status de usuários
        if (SecurityHelper::hasPermissao('usuarios.manage')) {
            $perfil = trim($_POST['perfil'] ?? $existing['perfil']);
            $status = trim($_POST['status'] ?? $existing['status']);
        } else {
            $perfil = $existing['perfil'];
            $status = $existing['status'];
        }

        if ($current_user_id && (int)$id === (int)$current_user_id && $status === 'inativo') {
            $_SESSION['flash_error'] = "Você não pode desativar seu próprio usuário.";
            header("Location: /usuarios/edit?id=" . $id);
            exit;
        }

        $this->usuarioModel->update($celula_id, $id, [
            'nome' => $nome,
            'email' => $email,
            'perfil' => $perfil,
            'status' => $status,
            'senha' => $senha
        ]);

        // Atualiza a sessão caso tenha editado o próprio perfil
        if ($isSelf) {
            $_SESSION['user']['nome'] = $nome;
            $_SESSION['user']['email'] = $email;
        }

        $_SESSION['flash_success'] = "Dados atualizados com sucesso!";
        header("Location: /usuarios");
        exit;
    }

    /**
     * Desativa (soft delete) um usuário da célula.
     *
     * @return void
     */
    public function delete() {
        $this->requirePermissao('usuarios.manage');
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = $_POST['id'] ?? null;
        $current_user_id = $_SESSION['user']['id'] ?? null;

        if ($id && ($current_user_id === null || $id != $current_user_id)) {
            $this->usuarioModel->delete($celula_id, $id);
        } else {
            $_SESSION['flash_error'] = "Você não pode excluir seu próprio usuário.";
        }

        header("Location: /usuarios");
        exit;
    }
}
