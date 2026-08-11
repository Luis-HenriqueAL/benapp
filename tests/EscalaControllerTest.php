<?php

use PHPUnit\Framework\TestCase;
use Controllers\EscalaController;
use Models\Escala;
use Models\Liturgia;
use Models\Usuario;
use Services\NotificationService;

class EscalaControllerTest extends TestCase {
    
    public function testStoreThrowsExceptionOnLiturgiaNotFound() {
        $celula_id = 1;
        $data = [
            'usuario_id' => 10,
            'liturgia_id' => 999, // Liturgia inexistente
            'funcao_id' => 2
        ];
        
        $controller = new EscalaController();
        
        // Substituindo o LiturgiaModel interno por um mock usando Reflection
        $liturgiaMock = $this->createMock(Liturgia::class);
        $liturgiaMock->method('findById')->willReturn(false);
        
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('liturgiaModel');
        $property->setAccessible(true);
        $property->setValue($controller, $liturgiaMock);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Liturgia não encontrada ou não pertence a esta célula.');
        
        $controller->store($celula_id, $data);
    }

    public function testStoreThrowsExceptionOnMissingEvento() {
        $celula_id = 1;
        $data = [
            'data' => '2026-08-20',
            'hora' => '19:00',
            'lider_id' => '1'
        ];
        
        $controller = new EscalaController();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('O nome do evento/culto é obrigatório.');
        
        $controller->store($celula_id, $data);
    }

    public function testStoreThrowsExceptionOnMissingData() {
        $celula_id = 1;
        $data = [
            'evento' => 'Culto de Domingo',
            'hora' => '19:00',
            'lider_id' => '1'
        ];
        
        $controller = new EscalaController();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A data do culto é obrigatória.');
        
        $controller->store($celula_id, $data);
    }

    public function testStoreSuccessfulFormSubmission() {
        $celula_id = 1;
        $data = [
            'evento' => 'Culto Especial',
            'data' => '2026-08-25',
            'hora' => '19:30',
            'lider_id' => '1',
            'momentos' => [
                [
                    'titulo' => 'Louvor',
                    'inicio' => '19:30',
                    'fim' => '20:00',
                    'voluntario_id' => '1'
                ]
            ]
        ];

        $controller = new EscalaController();

        $liturgiaMock = $this->createMock(Liturgia::class);
        $liturgiaMock->method('create')->willReturn(101);

        $usuarioMock = $this->createMock(Usuario::class);
        $usuarioMock->method('findById')->willReturn(['id' => 1, 'nome' => 'João Silva', 'email' => 'joao@email.com']);

        $escalaMock = $this->createMock(Escala::class);
        $escalaMock->method('hasConflict')->willReturn(false);
        $escalaMock->method('create')->willReturn(true);

        $reflection = new \ReflectionClass($controller);
        
        $p1 = $reflection->getProperty('liturgiaModel');
        $p1->setAccessible(true);
        $p1->setValue($controller, $liturgiaMock);

        $p2 = $reflection->getProperty('escalaModel');
        $p2->setAccessible(true);
        $p2->setValue($controller, $escalaMock);

        $p3 = $reflection->getProperty('usuarioModel');
        $p3->setAccessible(true);
        $p3->setValue($controller, $usuarioMock);

        $result = $controller->store($celula_id, $data);
        $this->assertTrue($result);
    }

    public function testGetMomentosPredefinidosReturnsArray() {
        $escalaModel = new Escala();
        $momentos = $escalaModel->getMomentosPredefinidos(1);
        $this->assertTrue(is_array($momentos));
        $this->assertTrue(!empty($momentos));
    }
}




