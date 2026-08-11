<?php

use PHPUnit\Framework\TestCase;
use Models\Escala;
use Models\Liturgia;

/**
 * Class EscalaModelTest
 * Testes unitários para validação de métodos do Model Escala, prevenção de conflitos e histórico.
 */
class EscalaModelTest extends TestCase {

    /**
     * Testa inserção na escala e verificação de conflito para um voluntário.
     */
    public function testCreateAndHasConflict() {
        $liturgiaModel = new Liturgia();
        $escalaModel = new Escala();

        $celulaId = 1;
        $liturgiaId = $liturgiaModel->create($celulaId, '2026-08-20', 'Culto Semanal');
        
        $usuarioId = 10;
        $funcaoId = 'Recepção';

        $temConflitoInicial = $escalaModel->hasConflict($usuarioId, $liturgiaId);
        $this->assertTrue(!$temConflitoInicial, "Não deve haver conflito antes da escalação");

        $sucesso = $escalaModel->create($liturgiaId, $usuarioId, $funcaoId);
        $this->assertTrue($sucesso, "Criação de escala deve retornar true");

        $temConflitoApos = $escalaModel->hasConflict($usuarioId, $liturgiaId);
        $this->assertTrue($temConflitoApos, "Deve identificar conflito de horário para o voluntário na mesma liturgia");
    }

    /**
     * Testa busca de momentos predefinidos por célula.
     */
    public function testGetMomentosPredefinidos() {
        $escalaModel = new Escala();
        $momentos = $escalaModel->getMomentosPredefinidos(1);

        $this->assertTrue(is_array($momentos), "getMomentosPredefinidos deve retornar um array");
        $this->assertTrue(count($momentos) > 0, "Lista de momentos não deve ser vazia");
    }
}
