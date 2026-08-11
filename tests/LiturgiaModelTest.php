<?php

use PHPUnit\Framework\TestCase;
use Models\Liturgia;

/**
 * Class LiturgiaModelTest
 * Testes unitários diretos para validação de métodos e isolamento por celula_id no Model Liturgia.
 */
class LiturgiaModelTest extends TestCase {

    /**
     * Testa criação de liturgia e busca por ID com tenant celula_id.
     */
    public function testCreateAndFindById() {
        $liturgiaModel = new Liturgia();
        $celulaId = 1;
        $dataCulto = '2026-08-20';
        $tema = 'Culto de Ação de Graças';

        $id = $liturgiaModel->create($celulaId, $dataCulto, $tema);
        $this->assertTrue($id !== false && $id > 0, "ID da liturgia criada deve ser maior que 0");

        $liturgia = $liturgiaModel->findById($celulaId, $id);
        $this->assertTrue(is_array($liturgia), "Liturgia deve retornar um array de dados");
        $this->assertTrue($liturgia['tema'] === $tema, "Tema da liturgia deve corresponder ao cadastrado");
    }

    /**
     * Testa isolamento multitenant no método findById.
     */
    public function testFindByIdTenantIsolation() {
        $liturgiaModel = new Liturgia();
        $celulaId = 1;
        $outraCelulaId = 999;
        
        $id = $liturgiaModel->create($celulaId, '2026-08-21', 'Culto Jovem');
        
        $tentativaOutraCelula = $liturgiaModel->findById($outraCelulaId, $id);
        $this->assertTrue($tentativaOutraCelula === false, "Busca por celula_id diferente deve retornar false");
    }

    /**
     * Testa listagem de todas as liturgias da célula.
     */
    public function testFindAllByCelula() {
        $liturgiaModel = new Liturgia();
        $celulaId = 5;

        $liturgiaModel->create($celulaId, '2026-08-22', 'Culto de Celebração');
        $liturgiaModel->create($celulaId, '2026-08-29', 'Culto da Família');

        $lista = $liturgiaModel->findAll($celulaId);
        $this->assertTrue(is_array($lista), "findAll deve retornar um array");
        $this->assertTrue(count($lista) >= 2, "findAll deve retornar ao menos 2 liturgias da celula_id = 5");
    }
}
