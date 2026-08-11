<?php

namespace Controllers;

use Models\Celula;
use Helpers\SecurityHelper;

/**
 * Class CelulaController
 * Gerencia a exibição e atualização das informações parametrizadas da célula.
 */
class CelulaController {
    /** @var Celula Instância do model Celula. */
    private $celulaModel;

    /**
     * Construtor do CelulaController.
     */
    public function __construct() {
        $this->celulaModel = new Celula();
    }

    /**
     * Exibe o formulário de parametrização da célula.
     *
     * @return void
     */
    public function index() {
        if (!SecurityHelper::hasPermissao('celula.edit')) {
            $_SESSION['flash_error'] = "Sem permissão para acessar as informações da célula.";
            header("Location: " . SecurityHelper::getDefaultRoute());
            exit;
        }
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $celula = $this->celulaModel->findByCelulaId($celula_id);
        require_once __DIR__ . '/../Views/celula/index.php';
    }

    /**
     * Processa e persiste a atualização dos dados da célula.
     *
     * @return void
     */
    public function update() {
        if (!SecurityHelper::hasPermissao('celula.edit')) {
            $_SESSION['flash_error'] = "Sem permissão para editar as informações da célula.";
            header("Location: /");
            exit;
        }
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $celula_id = $_SESSION['celula_id'] ?? 1;

        $nome_celula = trim($_POST['nome_celula'] ?? '');
        $dia_semana = trim($_POST['dia_semana'] ?? '');
        $horario = trim($_POST['horario'] ?? '');

        if (empty($nome_celula) || empty($dia_semana) || empty($horario)) {
            $_SESSION['flash_error'] = "Por favor, preencha os campos obrigatórios da célula (Nome, Dia e Horário).";
            header("Location: /celula");
            exit;
        }

        // Processa anfitriões
        $anfitrioesRaw = $_POST['anfitrioes'] ?? [];
        $anfitrioes = [];
        foreach ($anfitrioesRaw as $anf) {
            if (!empty($anf['nome'])) {
                $anfitrioes[] = [
                    'nome' => trim($anf['nome']),
                    'telefone1' => trim($anf['telefone1'] ?? ''),
                    'telefone2' => trim($anf['telefone2'] ?? '')
                ];
            }
        }

        // Processa líderes
        $lideresRaw = $_POST['lideres'] ?? [];
        $lideres = [];
        foreach ($lideresRaw as $lid) {
            if (!empty($lid['nome'])) {
                $telefones = array_filter(array_map('trim', $lid['telefones'] ?? []));
                $lideres[] = [
                    'nome' => trim($lid['nome']),
                    'telefones' => array_values($telefones)
                ];
            }
        }

        $data = [
            'nome_celula' => $nome_celula,
            'dia_semana' => $dia_semana,
            'horario' => $horario,
            'cep' => trim($_POST['cep'] ?? ''),
            'logradouro' => trim($_POST['logradouro'] ?? ''),
            'numero' => trim($_POST['numero'] ?? ''),
            'complemento' => trim($_POST['complemento'] ?? ''),
            'bairro' => trim($_POST['bairro'] ?? ''),
            'cidade' => trim($_POST['cidade'] ?? ''),
            'estado' => trim($_POST['estado'] ?? ''),
            'anfitrioes' => $anfitrioes,
            'lideres' => $lideres
        ];

        $this->celulaModel->save($celula_id, $data);
        $_SESSION['flash_success'] = "Informações da Célula salvas com sucesso!";
        header("Location: /celula");
        exit;
    }
}
