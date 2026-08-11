<?php

namespace Controllers;

use Models\Escala;
use Models\Liturgia;
use Models\Usuario;
use Services\NotificationService;

/**
 * Class EscalaController
 * Gerencia a lógica de negócios das escalas de cultos, envio de notificações e geração automática.
 */
class EscalaController {
    /** @var Escala Instância do model Escala. */
    private $escalaModel;

    /** @var Liturgia Instância do model Liturgia. */
    private $liturgiaModel;

    /** @var NotificationService Instância do serviço de notificações. */
    private $notificationService;

    /**
     * Construtor do EscalaController.
     */
    public function __construct() {
        $this->escalaModel = new Escala();
        $this->liturgiaModel = new Liturgia();
        $this->notificationService = new NotificationService();
    }

    /**
     * Renderiza o dashboard / lista principal de escalas da célula.
     *
     * @return void
     */
    public function index() {
        require_once __DIR__ . '/../Views/Escala/index.php';
    }

    /**
     * Renderiza a tela de criação/edição dinâmica de escala e liturgia.
     *
     * @return void
     */
    public function create() {
        require_once __DIR__ . '/../Views/Escala/create.php';
    }

    /**
     * Cadastra um voluntário em uma escala respeitando o isolamento do tenant e checando conflitos.
     *
     * @param int $celula_id Identificador da célula.
     * @param array $data Dados contendo usuario_id, liturgia_id e funcao_id.
     * @throws \Exception Se o voluntário/liturgia não for encontrado ou se houver conflito de horário.
     * @return bool Retorna verdadeiro se a atribuição for concluída.
     */
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
            $mockUsuario = ['nome' => 'Voluntário', 'email' => 'teste@email.com', 'telefone' => '11999999999'];
            $mockEscala = ['funcao' => 'Função ID ' . $funcao_id, 'data_culto' => 'Data a definir (ID ' . $liturgia_id . ')'];
            $this->notificationService->sendEscalaNotification($mockUsuario, $mockEscala);
        }

        return $success;
    }

    /**
     * Gera a escala mensal automaticamente com base nas escalas passadas do mês anterior.
     *
     * @param int $celula_id Identificador da célula.
     * @param int $nova_liturgia_id Identificador da nova liturgia criada.
     * @return array|false Lista de IDs de voluntários escalados ou false se não houver histórico.
     */
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
