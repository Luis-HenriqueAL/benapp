<?php

namespace Controllers;

use Models\Presenca;
use Helpers\SecurityHelper;

/**
 * Class VisitanteController
 * Controlador responsável pelo acesso e navegação restrita de visitantes via código de convite.
 */
class VisitanteController {

    /**
     * Exibe a view de formulário para inserção do código de visitante.
     *
     * @return void
     */
    public function index() {
        require_once __DIR__ . '/../Views/auth/visitante.php';
    }

    /**
     * Autentica o visitante a partir do código de acesso informado.
     *
     * @return void
     */
    public function acessar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /visitante");
            exit;
        }

        try {
            $csrfToken = $_POST['csrf_token'] ?? '';
            SecurityHelper::verifyCsrfToken($csrfToken);

            $codigo = strtoupper(trim($_POST['codigo_acesso'] ?? ''));

            if (empty($codigo)) {
                throw new \Exception("Por favor, informe o código do visitante.");
            }

            $presencaModel = new Presenca();
            $presenca = $presencaModel->findByCodigoAcesso($codigo);

            if (!$presenca) {
                throw new \Exception("Código de visitante inválido ou não encontrado. Por favor, verifique o código informado.");
            }

            // Define os dados do visitante na sessão
            $_SESSION['visitante'] = [
                'codigo'       => $codigo,
                'celula_id'    => (int)$presenca['celula_id'],
                'liturgia_id'  => (int)$presenca['liturgia_id'],
                'nome'         => $presenca['nome_visitante'] ?: 'Visitante'
            ];
            $_SESSION['celula_id'] = (int)$presenca['celula_id'];

            $_SESSION['flash_success'] = "Seja bem-vindo(a), " . SecurityHelper::e($presenca['nome_visitante'] ?: 'Visitante') . "! Acessando encontro de célula.";
            header("Location: /escala/show?id=" . (int)$presenca['liturgia_id']);
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /visitante");
            exit;
        }
    }

    /**
     * Encerra a sessão de visitante e retorna para a tela de login.
     *
     * @return void
     */
    public function sair() {
        unset($_SESSION['visitante']);
        header("Location: /login");
        exit;
    }
}
