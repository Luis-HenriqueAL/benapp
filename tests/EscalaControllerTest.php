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
}
