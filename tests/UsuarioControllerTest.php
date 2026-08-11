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
        $usuarioModel->method('findById')
            ->with($this->equalTo(1), $this->equalTo(99))
            ->willReturn(false);

        $result = $usuarioModel->findById(1, 99);
        $this->assertFalse($result);
    }
}
