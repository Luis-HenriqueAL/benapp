<?php

namespace Controllers;

use Models\Presenca;
use Helpers\SecurityHelper;

/**
 * Class PresencaController
 * Gerencia a confirmação e remoção de presenças de usuários em eventos/liturgias.
 * Qualquer usuário autenticado pode confirmar ou cancelar sua própria presença.
 */
class PresencaController {
    /** @var Presenca Instância do model Presenca. */
    private $presencaModel;

    /**
     * Construtor do PresencaController.
     */
    public function __construct() {
        $this->presencaModel = new Presenca();
    }

    /**
     * Confirma a presença do usuário autenticado ou de outro membro selecionado em um evento.
     *
     * @return void
     */
    public function confirmar() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id   = $_SESSION['celula_id'] ?? 1;
        $liturgia_id = (int)($_POST['liturgia_id'] ?? 0);
        $loggedId    = (int)($_SESSION['user']['id'] ?? 0);
        $usuario_id  = !empty($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : $loggedId;

        if (!$usuario_id || !$liturgia_id) {
            $_SESSION['flash_error'] = "Selecione um membro válido para confirmar presença.";
            header("Location: /escala/show?id={$liturgia_id}");
            exit;
        }

        $this->presencaModel->registrar($celula_id, $liturgia_id, $usuario_id, $loggedId);
        $_SESSION['flash_success'] = "Presença confirmada com sucesso!";
        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }

    /**
     * Cadastra a presença de um novo visitante no evento gravando o usuário autor.
     *
     * @return void
     */
    public function registrarVisitante() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id     = $_SESSION['celula_id'] ?? 1;
        $liturgia_id   = (int)($_POST['liturgia_id'] ?? 0);
        $loggedId      = (int)($_SESSION['user']['id'] ?? 0);
        $nomeVisitante = trim($_POST['nome_visitante'] ?? '');
        $qtdVisitas    = (int)($_POST['qtd_visitas'] ?? 1);

        if (empty($nomeVisitante) || !$liturgia_id) {
            $_SESSION['flash_error'] = "Por favor, informe o nome do visitante.";
            header("Location: /escala/show?id={$liturgia_id}");
            exit;
        }

        $this->presencaModel->registrarVisitante($celula_id, $liturgia_id, $nomeVisitante, $qtdVisitas, $loggedId);
        $_SESSION['flash_success'] = "Visitante '{$nomeVisitante}' adicionado com sucesso!";
        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }

    /**
     * Remove a presença de um membro ou visitante com checagem rigorosa de autorização.
     *
     * @return void
     */
    public function cancelar() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id   = $_SESSION['celula_id'] ?? 1;
        $liturgia_id = (int)($_POST['liturgia_id'] ?? 0);
        $presenca_id = (int)($_POST['presenca_id'] ?? 0);
        $usuario_id  = (int)($_POST['usuario_id'] ?? 0);
        $loggedId    = (int)($_SESSION['user']['id'] ?? 0);
        $isLider     = SecurityHelper::hasPermissao('escala.delete');

        if ($presenca_id) {
            $presenca = $this->presencaModel->findById($celula_id, $presenca_id);
            if (!$this->presencaModel->podeRemover($presenca, $loggedId, $isLider)) {
                $_SESSION['flash_error'] = "Você só pode remover sua própria presença ou presenças inseridas por você.";
                header("Location: /escala/show?id={$liturgia_id}");
                exit;
            }
            $this->presencaModel->removerById($celula_id, $presenca_id);
        } elseif ($usuario_id) {
            if ($usuario_id !== $loggedId && !$isLider) {
                $_SESSION['flash_error'] = "Você só pode remover sua própria presença ou presenças inseridas por você.";
                header("Location: /escala/show?id={$liturgia_id}");
                exit;
            }
            $this->presencaModel->remover($celula_id, $liturgia_id, $usuario_id);
        } else {
            $this->presencaModel->remover($celula_id, $liturgia_id, $loggedId);
        }

        $_SESSION['flash_success'] = "Confirmação de presença removida com sucesso.";
        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }
}
