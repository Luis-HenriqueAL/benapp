<?php

use PHPUnit\Framework\TestCase;
use Models\Presenca;
use Models\Liturgia;
use Controllers\VisitanteController;

/**
 * Class VisitanteControllerTest
 * Testes unitários para geração de código de visitante e fluxo do VisitanteController.
 */
class VisitanteControllerTest extends TestCase {

    /**
     * Testa a geração de código aleatório de visitante.
     */
    public function testCodigoAcessoGeneratesValidFormat() {
        $code = Presenca::generateCodigoAcesso();
        assert(strlen($code) === 6, "Código de visitante deve ter 6 caracteres.");
        assert(strpos($code, 'V') === 0, "Código de visitante deve começar com 'V'.");
    }

    /**
     * Testa o cadastro de visitante com inserção automática de código de acesso.
     */
    public function testRegistrarVisitanteGeraCodigoAcesso() {
        $liturgiaModel = new Liturgia();
        $liturgiaId = $liturgiaModel->create(1, date('Y-m-d'), 'Culto de Teste Visitante');
        
        $presencaModel = new Presenca();
        $nome = "Visitante Teste " . rand(100, 999);
        $result = $presencaModel->registrarVisitante(1, $liturgiaId, $nome, 1, 1);
        
        assert($result === true, "Registrar visitante deve retornar true.");
        
        $presencas = $presencaModel->findByLiturgia(1, $liturgiaId);
        assert(count($presencas) > 0, "Deve haver presenças registradas.");
        
        $visItem = null;
        foreach ($presencas as $p) {
            if ($p['nome_visitante'] === $nome) {
                $visItem = $p;
                break;
            }
        }
        
        assert($visItem !== null, "Visitante deve ser encontrado na lista.");
        assert(!empty($visItem['codigo_acesso']), "Código de acesso do visitante não pode ser vazio.");
        
        // Testa a busca pelo código de acesso
        $encontrado = $presencaModel->findByCodigoAcesso($visItem['codigo_acesso']);
        assert($encontrado !== false, "Busca por código de acesso deve retornar o registro do visitante.");
        assert($encontrado['nome_visitante'] === $nome, "Nome do visitante encontrado deve corresponder ao cadastrado.");
    }
}
