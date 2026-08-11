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
     * Renderiza o dashboard com as escalas/cultos reais cadastrados na célula logada.
     *
     * @return void
     */
    public function index() {
        if (!\Helpers\SecurityHelper::hasPermissao('escala.view')) {
            $defaultRoute = \Helpers\SecurityHelper::getDefaultRoute();
            if ($defaultRoute !== '/' && $defaultRoute !== '/escala') {
                header("Location: " . $defaultRoute);
                exit;
            }
            // Se o usuário não tiver permissão para escalas nem outros módulos, exibe a edição do seu próprio perfil
            $userId = (int)($_SESSION['user']['id'] ?? 0);
            header("Location: /usuarios/edit?id={$userId}");
            exit;
        }
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $escalas = $this->escalaModel->getEscalasComLiturgia($celula_id);
        $celulaInfo = $this->celulaModel->findByCelulaId($celula_id);
        require_once __DIR__ . '/../Views/Escala/index.php';
    }

    /**
     * Exibe o detalhamento completo de uma escala/culto específico.
     *
     * @return void
     */
    public function show() {
        if (!\Helpers\SecurityHelper::hasPermissao('escala.view')) {
            $_SESSION['flash_error'] = "Sem permissão para visualizar detalhes da escala.";
            header("Location: /escala");
            exit;
        }
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

        $liturgia = $this->escalaModel->getLiturgiaDetails($celula_id, $id);
        if (!$liturgia) {
            $_SESSION['flash_error'] = "Evento ou escala não encontrada.";
            header("Location: /escala");
            exit;
        }

        $celulaInfo = $this->celulaModel->findByCelulaId($celula_id);

        // Carrega presenças do evento
        $presencaModel = new \Models\Presenca();
        $presencas = $presencaModel->findByLiturgia($celula_id, $id);
        $usuarioLogadoConfirmado = $presencaModel->jaConfirmado($id, (int)($_SESSION['user']['id'] ?? 0));

        $todosUsuarios = $this->usuarioModel->findByCelulaId($celula_id);
        $visitantesHistorico = $presencaModel->findVisitantesByCelula($celula_id);

        require_once __DIR__ . '/../Views/Escala/show.php';
    }

    /**
     * Remove um evento/liturgia (e todas as atribuições vinculadas) da célula.
     * Restrito ao perfil LIDER.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @param array $data Dados POST contendo 'liturgia_id'.
     * @throws \Exception Se o perfil não for LIDER ou o evento não for encontrado.
     * @return void
     */
    public function delete($celula_id, $data) {
        if (!\Helpers\SecurityHelper::hasPermissao('escala.delete')) {
            throw new \Exception("Sem permissão para excluir eventos.");
        }

        $liturgiaId = (int)($data['liturgia_id'] ?? 0);
        if (!$liturgiaId) {
            throw new \Exception("ID do evento inválido.");
        }

        $deleted = $this->liturgiaModel->delete($celula_id, $liturgiaId);
        if (!$deleted) {
            throw new \Exception("Evento não encontrado ou não pertence à sua célula.");
        }
    }

    /**
     * Renderiza a tela de criação/edição dinâmica de escala e liturgia.
     * Requer permissão 'escala.create'.
     *
     * @return void
     */
    public function create() {
        if (!\Helpers\SecurityHelper::hasPermissao('escala.create')) {
            $_SESSION['flash_error'] = "Sem permissão para criar escalas.";
            header("Location: /escala");
            exit;
        }
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
     * @throws \Exception Se a data for omitida ou se houver erro ao salvar.
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

            $success = $this->escalaModel->create($liturgia_id, $usuario_id, $funcao_id, $celula_id);

            if ($success) {
                $mockUsuario = ['nome' => $usuario['nome'] ?? 'Voluntário', 'email' => $usuario['email'] ?? 'teste@email.com', 'telefone' => '11999999999'];
                $mockEscala = ['funcao' => 'Função ID ' . $funcao_id, 'data_culto' => 'Data a definir (ID ' . $liturgia_id . ')'];
                $this->notificationService->sendEscalaNotification($mockUsuario, $mockEscala);
            }

            return $success;
        }

        // Validação do formulário enviado via POST /escala/store
        $celulaInfo = $this->celulaModel->findByCelulaId($celula_id);
        $nomeDefault = !empty($celulaInfo['nome']) ? $celulaInfo['nome'] : (!empty($celulaInfo['nome_celula']) ? $celulaInfo['nome_celula'] : 'Célula Boas Novas');
        $horaDefault = !empty($celulaInfo['horario']) ? substr($celulaInfo['horario'], 0, 5) : '19:30';

        $dataCulto = isset($data['data']) ? trim($data['data']) : '';
        $horaCulto = !empty($data['hora']) ? trim($data['hora']) : $horaDefault;
        $momentos = isset($data['momentos']) && is_array($data['momentos']) ? $data['momentos'] : [];

        if (!isset($data['evento']) || trim($data['evento']) === '') {
            throw new \Exception("O nome do evento/culto é obrigatório.");
        }
        if (empty($dataCulto)) {
            throw new \Exception("A data do culto é obrigatória.");
        }

        $evento = !empty($data['evento']) ? trim($data['evento']) : "Encontro de Célula - {$nomeDefault}";

        // 1. Cadastra a liturgia no banco
        $liturgiaId = $this->liturgiaModel->create($celula_id, $dataCulto, $evento);
        if (!$liturgiaId) {
            throw new \Exception("Erro ao salvar a liturgia no banco de dados.");
        }

        // 2. Processa cada momento litúrgico e vincula voluntários
        // Um voluntário pode ser responsável por mais de um momento na mesma liturgia.
        foreach ($momentos as $index => $momento) {
            $tituloMomento = isset($momento['titulo']) ? trim($momento['titulo']) : '';
            $voluntarioId = !empty($momento['voluntario_id']) ? (int)$momento['voluntario_id'] : null;

            if ($voluntarioId) {
                // Valida pertencimento do voluntário à célula
                $usuario = $this->usuarioModel->findById($celula_id, $voluntarioId);
                if (!$usuario) {
                    throw new \Exception("Voluntário (ID: {$voluntarioId}) não foi encontrado ou não pertence a esta célula.");
                }

                $funcaoNome = !empty($tituloMomento) ? $tituloMomento : "Momento " . ($index + 1);
                $this->escalaModel->create($liturgiaId, $voluntarioId, $funcaoNome, $celula_id);

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
            
            if (!$this->escalaModel->hasConflict($usuario_id, $nova_liturgia_id, $celula_id)) {
                $this->escalaModel->create($nova_liturgia_id, $usuario_id, $funcao_id, $celula_id);
                $escalados_com_sucesso[] = $usuario_id;
            }
        }
        
        return $escalados_com_sucesso;
    }

    /**
     * Exibe a tela de edição de uma escala/liturgia existente.
     *
     * @return void
     */
    public function edit() {
        if (!\Helpers\SecurityHelper::hasPermissao('escala.create')) {
            $_SESSION['flash_error'] = "Sem permissão para editar escalas.";
            header("Location: /escala");
            exit;
        }
        $celula_id = $_SESSION['celula_id'] ?? 1;
        $id = (int)($_GET['id'] ?? 0);

        $liturgia = $this->escalaModel->getLiturgiaDetails($celula_id, $id);
        if (!$liturgia) {
            $_SESSION['flash_error'] = "Escala não encontrada.";
            header("Location: /escala");
            exit;
        }

        $celulaInfo = $this->celulaModel->findByCelulaId($celula_id);
        $momentosPredefinidos = $this->escalaModel->getMomentosPredefinidos($celula_id);
        $voluntarios = $this->usuarioModel->findByCelula($celula_id);

        require_once __DIR__ . '/../Views/Escala/edit.php';
    }

    /**
     * Processa a atualização dos dados e momentos de uma liturgia/escala existente.
     *
     * @param int $celula_id Identificador da célula (tenant).
     * @param array $data Dados POST do formulário.
     * @throws \Exception Se a liturgia for inválida ou não pertencer à célula.
     * @return bool Retorna verdadeiro em caso de sucesso.
     */
    public function updateStore($celula_id, $data) {
        if (!\Helpers\SecurityHelper::hasPermissao('escala.create')) {
            throw new \Exception("Sem permissão para editar escalas.");
        }

        $liturgiaId = (int)($data['liturgia_id'] ?? 0);
        if (!$liturgiaId) {
            throw new \Exception("ID da liturgia/escala inválido.");
        }

        $liturgiaAtual = $this->liturgiaModel->findById($celula_id, $liturgiaId);
        if (!$liturgiaAtual) {
            throw new \Exception("Liturgia não encontrada ou não pertence a esta célula.");
        }

        $dataCulto = isset($data['data']) ? trim($data['data']) : '';
        $horaCulto = !empty($data['hora']) ? trim($data['hora']) : '19:30';
        $momentos = isset($data['momentos']) && is_array($data['momentos']) ? $data['momentos'] : [];

        if (!isset($data['evento']) || trim($data['evento']) === '') {
            throw new \Exception("O nome do evento/culto é obrigatório.");
        }
        if (empty($dataCulto)) {
            throw new \Exception("A data do culto é obrigatória.");
        }

        $evento = trim($data['evento']);

        // 1. Atualiza a liturgia no banco
        $this->liturgiaModel->update($celula_id, $liturgiaId, $dataCulto, $evento);

        // 2. Remove as atribuições anteriores para essa liturgia
        $this->escalaModel->deleteByLiturgiaId($celula_id, $liturgiaId);

        // 3. Processa e insere cada novo momento com os voluntários
        foreach ($momentos as $index => $momento) {
            $tituloMomento = isset($momento['titulo']) ? trim($momento['titulo']) : '';
            $voluntarioId = !empty($momento['voluntario_id']) ? (int)$momento['voluntario_id'] : null;

            if ($voluntarioId) {
                $usuario = $this->usuarioModel->findById($celula_id, $voluntarioId);
                if ($usuario) {
                    $funcaoNome = !empty($tituloMomento) ? $tituloMomento : "Momento " . ($index + 1);
                    $this->escalaModel->create($liturgiaId, $voluntarioId, $funcaoNome, $celula_id);
                }
            }
        }

        return true;
    }
}
