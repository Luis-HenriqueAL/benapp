<?php

namespace Controllers;

use Models\Liturgia;

/**
 * Class LiturgiaController
 * Gerencia a criação e listagem de cultos e liturgias por célula.
 */
class LiturgiaController {
    /** @var Liturgia Instância do model Liturgia. */
    private $liturgiaModel;

    /**
     * Construtor do LiturgiaController.
     */
    public function __construct() {
        $this->liturgiaModel = new Liturgia();
    }

    /**
     * Lista todas as liturgias cadastradas de uma determinada célula.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @return array Lista de liturgias.
     */
    public function index($celula_id) {
        return $this->liturgiaModel->findAll($celula_id);
    }
    
    /**
     * Cria uma nova liturgia no sistema.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @param array $data Dados da liturgia (data_culto, tema).
     * @throws \Exception Se a data do culto for omitida.
     * @return bool Retorna verdadeiro em caso de sucesso.
     */
    public function store($celula_id, $data) {
        if (empty($data['data_culto'])) {
            throw new \Exception("A data do culto é obrigatória.");
        }
        $tema = $data['tema'] ?? null;
        return $this->liturgiaModel->create($celula_id, $data['data_culto'], $tema);
    }
}

