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
     * Exibe a listagem de usuários da célula.
     *
     * @return void
     */
    public function index() {
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
        require_once __DIR__ . '/../Views/usuarios/create.php';
    }

    /**
     * Processa o formulário de cadastro de usuário e persiste no banco de dados.
     *
     * @return void
     */
    public function store() {
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
     * Exibe o formulário de edição de um usuário existente.
     *
     * @return void
     */
    public function edit() {
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /usuarios");
            exit;
        }

        $usuario = $this->usuarioModel->findById($celula_id, $id);
        if (!$usuario) {
            $_SESSION['flash_error'] = "Usuário não encontrado.";
            header("Location: /usuarios");
            exit;
        }

        require_once __DIR__ . '/../Views/usuarios/edit.php';
    }

    /**
     * Processa a atualização cadastral de um usuário.
     *
     * @return void
     */
    public function update() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header("Location: /usuarios");
            exit;
        }

        $current_user_id = $_SESSION['user']['id'] ?? null;
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $perfil = trim($_POST['perfil'] ?? 'MEMBRO');
        $status = trim($_POST['status'] ?? 'ativo');
        $senha = trim($_POST['senha'] ?? '');

        if ($current_user_id && $id == $current_user_id && $status === 'inativo') {
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

        header("Location: /usuarios");
        exit;
    }

    /**
     * Desativa (soft delete) um usuário da célula.
     *
     * @return void
     */
    public function delete() {
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
