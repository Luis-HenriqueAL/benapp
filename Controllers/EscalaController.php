<?php

namespace Controllers;

use Models\Escala;
use Models\Liturgia;
use Models\Usuario;
use Models\Celula;
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

    /** @var Usuario Instância do model Usuario. */
    private $usuarioModel;

    /** @var Celula Instância do model Celula. */
    private $celulaModel;

    /** @var NotificationService Instância do serviço de notificações. */
    private $notificationService;

    /**
     * Construtor do EscalaController.
     */
    public function __construct() {
        $this->escalaModel = new Escala();
        $this->liturgiaModel = new Liturgia();
        $this->usuarioModel = new Usuario();
        $this->celulaModel = new Celula();
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
     * Carrega automaticamente as informações cadastrais da célula (nome e horário) e os momentos predefinidos.
     *
     * @return void
     */
    public function create() {
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $celulaInfo = $this->celulaModel->findByCelulaId($celula_id);
        $momentosPredefinidos = $this->escalaModel->getMomentosPredefinidos($celula_id);
        $voluntarios = $this->usuarioModel->findByCelula($celula_id);

        require_once __DIR__ . '/../Views/Escala/create.php';
    }


    /**
     * Cadastra uma nova escala/liturgia ou atribui voluntários respeitando o isolamento do tenant e checando conflitos.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @param array $data Dados vindos do formulário POST.
     * @throws \Exception Se o voluntário/liturgia não for encontrado ou se houver conflito de horário.
     * @return bool Retorna verdadeiro se a atribuição for concluída.
     */
    public function store($celula_id, $data) {
        // Suporte a chamada legada / unit testes (atribuição direta de voluntário)
        if (isset($data['usuario_id'], $data['liturgia_id'])) {
            $usuario_id = $data['usuario_id'];
            $liturgia_id = $data['liturgia_id'];
            $funcao_id = $data['funcao_id'] ?? 1;

            $liturgia = $this->liturgiaModel->findById($celula_id, $liturgia_id);
            if (!$liturgia) {
                throw new \Exception("Liturgia não encontrada ou não pertence a esta célula.");
            }

            $usuario = $this->usuarioModel->findById($celula_id, $usuario_id);
            if (!$usuario) {
                throw new \Exception("Usuário não encontrado ou não pertence a esta célula.");
            }

            if ($this->escalaModel->hasConflict($usuario_id, $liturgia_id)) {
                throw new \Exception("Conflito de horário: o voluntário já está escalado para este culto.");
            }

            $success = $this->escalaModel->create($liturgia_id, $usuario_id, $funcao_id);

            if ($success) {
                $mockUsuario = ['nome' => $usuario['nome'] ?? 'Voluntário', 'email' => $usuario['email'] ?? 'teste@email.com', 'telefone' => '11999999999'];
                $mockEscala = ['funcao' => 'Função ID ' . $funcao_id, 'data_culto' => 'Data a definir (ID ' . $liturgia_id . ')'];
                $this->notificationService->sendEscalaNotification($mockUsuario, $mockEscala);
            }

            return $success;
        }

        // Validação e processamento do formulário enviado via POST /escala/store
        $evento = isset($data['evento']) ? trim($data['evento']) : '';
        $dataCulto = isset($data['data']) ? trim($data['data']) : '';
        $horaCulto = isset($data['hora']) ? trim($data['hora']) : '';
        $liderId = isset($data['lider_id']) ? (int)$data['lider_id'] : null;
        $momentos = isset($data['momentos']) && is_array($data['momentos']) ? $data['momentos'] : [];

        if (empty($evento)) {
            throw new \Exception("O nome do evento/culto é obrigatório.");
        }
        if (empty($dataCulto)) {
            throw new \Exception("A data do culto é obrigatória.");
        }
        if (empty($liderId)) {
            throw new \Exception("O líder responsável é obrigatório.");
        }

        // 1. Cadastra a liturgia no banco
        $liturgiaId = $this->liturgiaModel->create($celula_id, $dataCulto, $evento);
        if (!$liturgiaId) {
            throw new \Exception("Erro ao salvar a liturgia no banco de dados.");
        }

        // 2. Processa cada momento litúrgico e vincula voluntários
        foreach ($momentos as $index => $momento) {
            $tituloMomento = isset($momento['titulo']) ? trim($momento['titulo']) : '';
            $voluntarioId = !empty($momento['voluntario_id']) ? (int)$momento['voluntario_id'] : null;

            if ($voluntarioId) {
                // Valida pertencimento do voluntário à célula
                $usuario = $this->usuarioModel->findById($celula_id, $voluntarioId);
                if (!$usuario) {
                    throw new \Exception("Voluntário (ID: {$voluntarioId}) não foi encontrado ou não pertence a esta célula.");
                }

                // Valida conflito de horário
                if (is_numeric($liturgiaId) && $this->escalaModel->hasConflict($voluntarioId, $liturgiaId)) {
                    throw new \Exception("Conflito de horário: o voluntário '{$usuario['nome']}' já possui atribuição neste culto.");
                }

                $funcaoNome = !empty($tituloMomento) ? $tituloMomento : "Momento " . ($index + 1);
                $this->escalaModel->create($liturgiaId, $voluntarioId, $funcaoNome);

                $this->notificationService->sendEscalaNotification($usuario, [
                    'funcao' => $funcaoNome,
                    'data_culto' => $dataCulto . ' ' . $horaCulto
                ]);
            }
        }

        return true;
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
