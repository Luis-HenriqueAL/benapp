<?php

namespace Controllers;

use Models\Escala;
use Models\Liturgia;
use Models\Usuario;
use Services\NotificationService;

class EscalaController {
    private $escalaModel;
    private $liturgiaModel;
    private $notificationService;

    public function __construct() {
        $this->escalaModel = new Escala();
        $this->liturgiaModel = new Liturgia();
        $this->notificationService = new NotificationService();
    }

    public function index() {
        // Carrega a view de dashboard/lista
        require_once __DIR__ . '/../Views/layout.php';
        // A view de layout deve estar incluindo a view index internamente, ou nós a passamos
        // Para simplificar, vou dar um include direto aqui se o layout não fizer
        // Assumindo que a view de index foi feita para ser carregada no layout
        $view = 'Escala/index.php';
        require_once __DIR__ . '/../Views/layout.php';
    }

    public function create() {
        $view = 'Escala/create.php';
        require_once __DIR__ . '/../Views/layout.php';
    }


    public function store($celula_id, $data) {
        $usuario_id = $data['usuario_id'];
        $liturgia_id = $data['liturgia_id'];
        $funcao_id = $data['funcao_id'];

        $liturgia = $this->liturgiaModel->findById($celula_id, $liturgia_id);
        if (!$liturgia) {
            throw new \Exception("Liturgia não encontrada ou não pertence a esta célula.");
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($celula_id, $usuario_id);
        if (!$usuario) {
            throw new \Exception("Usuário não encontrado ou não pertence a esta célula.");
        }

        if ($this->escalaModel->hasConflict($usuario_id, $liturgia_id)) {
            throw new \Exception("Conflito de horário: o voluntário já está escalado para este culto.");
        }

        $success = $this->escalaModel->create($liturgia_id, $usuario_id, $funcao_id);

        if ($success) {
            // Em um sistema real, buscar os dados do usuário e da função no BD.
            $mockUsuario = ['nome' => 'Voluntário', 'email' => 'teste@email.com', 'telefone' => '11999999999'];
            $mockEscala = ['funcao' => 'Função ID ' . $funcao_id, 'data_culto' => 'Data a definir (ID ' . $liturgia_id . ')'];
            $this->notificationService->sendEscalaNotification($mockUsuario, $mockEscala);
        }

        return $success;
    }

    public function gerarEscalaAutomaticaMensal($celula_id, $nova_liturgia_id) {
        $historico = $this->escalaModel->getLastMonthEscalas($celula_id);
        
        if (empty($historico)) {
            return false;
        }

        $escalados_com_sucesso = [];
        foreach ($historico as $registro) {
            $usuario_id = $registro['usuario_id'];
            $funcao_id = $registro['funcao_id'];
            
            if (!$this->escalaModel->hasConflict($usuario_id, $nova_liturgia_id)) {
                $this->escalaModel->create($nova_liturgia_id, $usuario_id, $funcao_id);
                $escalados_com_sucesso[] = $usuario_id;
            }
        }
        
        return $escalados_com_sucesso;
    }
}
