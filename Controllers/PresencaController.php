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
     * Confirma a presença do usuário autenticado em um evento.
     * Redireciona de volta para o evento após o registro.
     *
     * @return void
     */
    public function confirmar() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id  = $_SESSION['celula_id'] ?? 1;
        $usuario_id = (int)($_SESSION['user']['id'] ?? 0);
        $liturgia_id = (int)($_POST['liturgia_id'] ?? 0);

        if (!$usuario_id || !$liturgia_id) {
            $_SESSION['flash_error'] = "Dados inválidos.";
            header("Location: /escala");
            exit;
        }

        $this->presencaModel->registrar($celula_id, $liturgia_id, $usuario_id);
        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }

    /**
     * Remove a presença do usuário autenticado de um evento.
     * Redireciona de volta para o evento após a remoção.
     *
     * @return void
     */
    public function cancelar() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        $celula_id   = $_SESSION['celula_id'] ?? 1;
        $usuario_id  = (int)($_SESSION['user']['id'] ?? 0);
        $liturgia_id = (int)($_POST['liturgia_id'] ?? 0);

        if (!$usuario_id || !$liturgia_id) {
            $_SESSION['flash_error'] = "Dados inválidos.";
            header("Location: /escala");
            exit;
        }

        $this->presencaModel->remover($celula_id, $liturgia_id, $usuario_id);
        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }
}
