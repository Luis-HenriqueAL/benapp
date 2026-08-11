<?php

namespace Controllers;

use Helpers\SecurityHelper;
use Models\LiturgiaMusica;
use Services\CifraClubService;

/**
 * Class MusicaController
 * Controller para gerenciamento de músicas e visualização de cifras vinculadas às liturgias.
 */
class MusicaController {
    /** @var LiturgiaMusica Model de músicas. */
    private $musicaModel;

    /** @var CifraClubService Serviço de integração com Cifra Club. */
    private $cifraClubService;

    /**
     * Construtor do MusicaController.
     */
    public function __construct() {
        $this->musicaModel      = new LiturgiaMusica();
        $this->cifraClubService = new CifraClubService();
    }

    /**
     * Adiciona uma música a uma liturgia (via URL do Cifra Club, busca ou inserção manual).
     *
     * @return void
     */
    public function store() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        if (!SecurityHelper::hasPermissao('escala.edit')) {
            $_SESSION['flash_error'] = "Sem permissão para adicionar músicas à escala.";
            header("Location: /escala");
            exit;
        }

        $celula_id      = $_SESSION['celula_id'] ?? 1;
        $liturgia_id    = (int)($_POST['liturgia_id'] ?? 0);
        $momento_titulo = trim($_POST['momento_titulo'] ?? '');
        $cifraclub_url  = trim($_POST['cifraclub_url'] ?? '');
        $titulo         = trim($_POST['titulo'] ?? '');
        $artista        = trim($_POST['artista'] ?? '');
        $tom            = trim($_POST['tom'] ?? '');
        $cifra_manual   = trim($_POST['cifra_texto'] ?? '');

        if (!$liturgia_id) {
            $_SESSION['flash_error'] = "Evento inválido.";
            header("Location: /escala");
            exit;
        }

        $cifraTextoFinal = $cifra_manual;

        // Se uma URL ou Artista+Música for fornecida, busca no Cifra Club API / Scraper
        if (!empty($cifraclub_url)) {
            $fetched = $this->cifraClubService->fetchByUrl($cifraclub_url);
            if ($fetched) {
                if (empty($titulo))  $titulo  = $fetched['name'];
                if (empty($artista)) $artista = $fetched['artist'];
                if (empty($tom) && !empty($fetched['tom'])) $tom = $fetched['tom'];
                if (empty($cifraTextoFinal)) $cifraTextoFinal = $fetched['cifra'];
            }
        } elseif (!empty($titulo) && !empty($artista) && empty($cifraTextoFinal)) {
            $fetched = $this->cifraClubService->fetchCifra($artista, $titulo);
            if ($fetched) {
                if (empty($tom) && !empty($fetched['tom'])) $tom = $fetched['tom'];
                $cifraTextoFinal = $fetched['cifra'];
                if (empty($cifraclub_url)) $cifraclub_url = $fetched['url'];
            }
        }

        if (empty($titulo)) {
            $_SESSION['flash_error'] = "Por favor, informe o título da música ou insira uma URL válida do Cifra Club.";
            header("Location: /escala/show?id={$liturgia_id}");
            exit;
        }

        $this->musicaModel->create($celula_id, $liturgia_id, $momento_titulo, $titulo, $artista, $tom, $cifraclub_url, $cifraTextoFinal);
        $_SESSION['flash_success'] = "Música '{$titulo}' vinculada com sucesso!";
        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }

    /**
     * Remove uma música da liturgia.
     *
     * @return void
     */
    public function delete() {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

        if (!SecurityHelper::hasPermissao('escala.edit')) {
            $_SESSION['flash_error'] = "Sem permissão para remover músicas.";
            header("Location: /escala");
            exit;
        }

        $celula_id   = $_SESSION['celula_id'] ?? 1;
        $liturgia_id = (int)($_POST['liturgia_id'] ?? 0);
        $id          = (int)($_POST['id'] ?? 0);

        if ($id) {
            $this->musicaModel->delete($celula_id, $id);
            $_SESSION['flash_success'] = "Música removida com sucesso!";
        }

        header("Location: /escala/show?id={$liturgia_id}");
        exit;
    }

    /**
     * Exibe a cifra e letra de uma música ou lista de músicas da liturgia.
     *
     * @return void
     */
    public function cifraView() {
        if (!SecurityHelper::hasPermissao('escala.view')) {
            $_SESSION['flash_error'] = "Sem permissão para visualizar cifras.";
            header("Location: /escala");
            exit;
        }

        $celula_id   = $_SESSION['celula_id'] ?? 1;
        $liturgia_id = (int)($_GET['liturgia_id'] ?? 0);
        $musica_id   = (int)($_GET['id'] ?? 0);

        if ($musica_id) {
            $musica = $this->musicaModel->findById($celula_id, $musica_id);
            $musicas = $musica ? [$musica] : [];
        } elseif ($liturgia_id) {
            $musicas = $this->musicaModel->findByLiturgia($celula_id, $liturgia_id);
        } else {
            $_SESSION['flash_error'] = "Música não encontrada.";
            header("Location: /escala");
            exit;
        }

        $forceRefresh = isset($_GET['force_refresh']) && $_GET['force_refresh'] == '1';

        // Tenta auto-carregar e atualizar no banco se cifra_texto estiver vazio ou force_refresh=1
        foreach ($musicas as &$mus) {
            if (($forceRefresh || empty($mus['cifra_texto'])) && !empty($mus['cifraclub_url'])) {
                $fetched = $this->cifraClubService->fetchByUrl($mus['cifraclub_url']);
                if ($fetched && !empty($fetched['cifra'])) {
                    $mus['cifra_texto'] = $fetched['cifra'];
                    if (empty($mus['tom']) && !empty($fetched['tom'])) {
                        $mus['tom'] = $fetched['tom'];
                    }
                    if (empty($mus['artista']) && !empty($fetched['artist'])) {
                        $mus['artista'] = $fetched['artist'];
                    }
                    $this->musicaModel->updateCifraCache($celula_id, (int)$mus['id'], $mus['cifra_texto'], $mus['tom'], $mus['artista']);
                }
            }
        }
        unset($mus);

        require_once __DIR__ . '/../Views/Escala/cifra.php';
    }
}
