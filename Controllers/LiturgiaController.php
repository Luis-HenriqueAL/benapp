<?php

namespace Controllers;

use Models\Liturgia;

class LiturgiaController {
    private $liturgiaModel;

    public function __construct() {
        $this->liturgiaModel = new Liturgia();
    }

    public function index($celula_id) {
        return $this->liturgiaModel->findAll($celula_id);
    }
    
    public function store($celula_id, $data) {
        if (empty($data['data_culto'])) {
            throw new \Exception("A data do culto é obrigatória.");
        }
        $tema = $data['tema'] ?? null;
        return $this->liturgiaModel->create($celula_id, $data['data_culto'], $tema);
    }
}
