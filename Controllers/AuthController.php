<?php

namespace Controllers;

use Models\Usuario;
use Helpers\SecurityHelper;

/**
 * Class AuthController
 * Gerencia os fluxos de autenticação, login, validação de sessão e logout.
 */
class AuthController {
    /** @var Usuario Instância do model Usuario. */
    private $usuarioModel;

    /**
     * Construtor do AuthController.
     */
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Exibe a tela de login. Redireciona para o dashboard se já estiver autenticado.
     *
     * @return void
     */
    public function loginView() {
        if (isset($_SESSION['user'])) {
            header("Location: /");
            exit;
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Autentica o usuário validando e-mail, senha (BCrypt) e token CSRF.
     *
     * @return void
     */
    public function authenticate() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = "Por favor, preencha todos os campos.";
            header("Location: /login");
            exit;
        }

        $user = $this->usuarioModel->findByEmail($email);

        $isValid = false;
        if ($user && !empty($user['senha'])) {
            if (password_verify($password, $user['senha'])) {
                $isValid = true;
            } elseif ($user['senha'] === $password) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            $_SESSION['flash_error'] = "E-mail ou senha inválidos.";
            header("Location: /login");
            exit;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'perfil' => $user['perfil'],
            'celula_id' => $user['celula_id']
        ];
        $_SESSION['celula_id'] = $user['celula_id'];

        header("Location: /");
        exit;
    }

    /**
     * Encerra a sessão do usuário e redireciona para a tela de login.
     *
     * @return void
     */
    public function logout() {
        unset($_SESSION['user']);
        unset($_SESSION['celula_id']);
        session_destroy();
        header("Location: /login");
        exit;
    }
}
