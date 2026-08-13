<?php

use Services\EscalaGeneratorService;
use Models\Usuario;

class EscalaGeneratorServiceTest {
    public $expectedException = null;
    public $expectedExceptionMessage = null;
    private $service;
    private $usuarioModel;

    public function setUp() {
        $this->service = new EscalaGeneratorService();
        $this->usuarioModel = new Usuario();
    }

    public function testGerarAtribuicoesComLiderPrincipalEPalavra() {
        $celula_id = 1;
        $momentos = [
            ['id' => 1, 'titulo' => 'Quebra-Gelo / Recepção', 'is_louvor' => false, 'is_palavra' => false],
            ['id' => 2, 'titulo' => 'Louvor e Adoração', 'is_louvor' => true, 'is_palavra' => false],
            ['id' => 3, 'titulo' => 'Estudo / Palavra', 'is_louvor' => false, 'is_palavra' => true],
            ['id' => 4, 'titulo' => 'Oração e Avisos', 'is_louvor' => false, 'is_palavra' => false]
        ];

        $result = $this->service->gerarAtribuicoes($celula_id, $momentos);

        if (count($result) !== 4) {
            throw new \Exception("Deveria retornar 4 atribuições.");
        }

        $liderPrincipal = $this->usuarioModel->findLiderPrincipalByCelula($celula_id);
        $liderId = (int)$liderPrincipal['id'];

        if ((int)$result[1]['usuario_id'] !== $liderId) {
            throw new \Exception("Momento de Louvor deve ser atribuído ao Líder Principal.");
        }

        if ((int)$result[2]['usuario_id'] !== $liderId) {
            throw new \Exception("Momento de Palavra deve ser atribuído ao Líder Principal.");
        }
    }

    public function testGerarAtribuicoesComApenasTitulosSemFlagsDoFrontend() {
        $celula_id = 1;
        $momentos = [
            ['idx' => 0, 'titulo' => 'Quebra-Gelo / Recepção'],
            ['idx' => 1, 'titulo' => 'Louvor e Adoração'],
            ['idx' => 2, 'titulo' => 'Estudo / Palavra'],
            ['idx' => 3, 'titulo' => 'Oração e Avisos']
        ];

        $result = $this->service->gerarAtribuicoes($celula_id, $momentos);

        $liderPrincipal = $this->usuarioModel->findLiderPrincipalByCelula($celula_id);
        $liderId = (int)$liderPrincipal['id'];

        if ((int)$result[1]['usuario_id'] !== $liderId) {
            throw new \Exception("Momento 'Louvor e Adoração' deve ser atribuído ao Líder Principal.");
        }

        if ((int)$result[2]['usuario_id'] !== $liderId) {
            throw new \Exception("Momento 'Estudo / Palavra' deve ser atribuído ao Líder Principal.");
        }
    }
}
