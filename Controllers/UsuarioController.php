<?php

namespace Controllers;

use Models\Usuario;
use Helpers\SecurityHelper;

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function index() {
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $usuarios = $this->usuarioModel->findByCelulaId($celula_id);
        
        ob_start();
        require_once __DIR__ . '/../Views/usuarios/index.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layout.php';
    }

    public function create() {
        ob_start();
        require_once __DIR__ . '/../Views/usuarios/create.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layout.php';
    }

    public function store($celula_id, $data) {
        if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
            throw new \Exception("Preencha todos os campos obrigatórios (Nome, E-mail e Senha).");
        }

        $existingUser = $this->usuarioModel->findByEmail($data['email']);
        if ($existingUser) {
            throw new \Exception("Este e-mail já está cadastrado no sistema.");
        }

        $this->usuarioModel->create($celula_id, $data);
        header("Location: /usuarios");
        exit;
    }

    public function edit($id) {
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $usuario = $this->usuarioModel->findById($celula_id, $id);

        if (!$usuario) {
            throw new \Exception("Usuário não encontrado.");
        }

        ob_start();
        require_once __DIR__ . '/../Views/usuarios/edit.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layout.php';
    }

    public function update($celula_id, $id, $data) {
        if (empty($data['nome']) || empty($data['email'])) {
            throw new \Exception("Nome e E-mail são obrigatórios.");
        }

        $usuario = $this->usuarioModel->findById($celula_id, $id);
        if (!$usuario) {
            throw new \Exception("Usuário não encontrado.");
        }

        $existingUser = $this->usuarioModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            throw new \Exception("Este e-mail já está em uso por outro usuário.");
        }

        $this->usuarioModel->update($celula_id, $id, $data);
        header("Location: /usuarios");
        exit;
    }

    public function delete($celula_id, $id) {
        $usuario = $this->usuarioModel->findById($celula_id, $id);
        if (!$usuario) {
            throw new \Exception("Usuário não encontrado para exclusão.");
        }

        $this->usuarioModel->delete($celula_id, $id);
        header("Location: /usuarios");
        exit;
    }
}
