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
}
