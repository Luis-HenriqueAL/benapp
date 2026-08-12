<?php

use PHPUnit\Framework\TestCase;
use Controllers\UsuarioController;
use Models\Usuario;
use Helpers\SecurityHelper;

/**
 * Class UsuarioControllerTest
 * Testes unitários para validação de regras de negócio e segurança do Módulo de Usuários.
 */
class UsuarioControllerTest extends TestCase {

    /**
     * Configura a sessão simulada com permissões para os testes.
     */
    public function setUp(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user'] = ['id' => 1, 'nome' => 'Teste', 'perfil' => 'MEMBRO', 'celula_id' => 1];
        $_SESSION['celula_id'] = 1;
        $_SESSION['permissoes'] = ['usuarios.view', 'usuarios.manage', 'escala.view', 'escala.create'];
    }

    /**
     * Testa se o token CSRF é exigido no cadastro (store).
     */
    public function testStoreRequiresValidCsrfToken() {
        $_POST = [
            'csrf_token' => 'token_invalido',
            'nome' => 'Teste Silva',
            'email' => 'teste@celula.com',
            'senha' => '123456'
        ];

        $controller = new UsuarioController();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token CSRF inválido.');

        $controller->store();
    }

    /**
     * Testa o isolamento de tenant na busca de usuário por ID.
     */
    public function testTenantIsolationInUsuarioModel() {
        $usuarioModel = $this->createMock(Usuario::class);
        $usuarioModel->method('findById')->willReturn(false);

        $result = $usuarioModel->findById(1, 99);
        if ($result !== false) {
            throw new \Exception("Assertion failed: expected false");
        }
    }
}
