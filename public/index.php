<?php
session_start();

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// Carrega as variáveis de ambiente do arquivo .env
\Helpers\EnvHelper::load(__DIR__ . '/../.env');

// Capturador Global de Erros para nunca exibir tela branca com exceção pura
set_exception_handler(function ($e) {
    $_SESSION['flash_error'] = $e->getMessage();
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        $errorMessage = $e->getMessage();
        require_once __DIR__ . '/../Views/errors/500.php';
        exit;
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Rotas e Autenticação
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/public', '', $uri);

// Ignora ativos estáticos e favicon para não gerar erro ou redirecionamento em segundo plano
if (preg_match('/\.(ico|png|jpg|jpeg|svg|css|js|map|woff|woff2|ttf)$/i', $uri)) {
    http_response_code(404);
    exit;
}

// Rotas públicas
if ($uri === '/login') {
    $controller = new \Controllers\AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->authenticate();
    } else {
        $controller->loginView();
    }
    exit;
}

if ($uri === '/logout') {
    $controller = new \Controllers\AuthController();
    $controller->logout();
    exit;
}

if ($uri === '/visitante') {
    $controller = new \Controllers\VisitanteController();
    $controller->index();
    exit;
}

if ($uri === '/visitante/acessar') {
    $controller = new \Controllers\VisitanteController();
    $controller->acessar();
    exit;
}

if ($uri === '/visitante/sair') {
    $controller = new \Controllers\VisitanteController();
    $controller->sair();
    exit;
}

// Middleware de Proteção de Autenticação (Membro/Líder ou Visitante)
if (!isset($_SESSION['user']) && !isset($_SESSION['visitante'])) {
    header("Location: /login");
    exit;
}

// Middleware de Restrição para Visitantes (Somente Leitura da Liturgia/Escala)
if (isset($_SESSION['visitante']) && !isset($_SESSION['user'])) {
    $allowedVisitorRoutes = ['/escala/show', '/escala', '/', '', '/cifra', '/musica/cifra', '/escala/cifra'];
    if (!in_array($uri, $allowedVisitorRoutes) && !preg_match('/^\/escala\/\d+$/', $uri)) {
        $targetLiturgiaId = (int)($_SESSION['visitante']['liturgia_id'] ?? 0);
        header("Location: /escala/show?id=" . $targetLiturgiaId);
        exit;
    }
}

// Middleware de Tenant
if (!isset($_SESSION['celula_id'])) {
    if (isset($_SESSION['visitante']['celula_id'])) {
        $_SESSION['celula_id'] = (int)$_SESSION['visitante']['celula_id'];
    } else {
        $_SESSION['celula_id'] = $_SESSION['user']['celula_id'] ?? 1;
    }
}

// Rotas Protegidas
if ($uri === '/' || $uri === '' || $uri === '/escala') {
    $controller = new \Controllers\EscalaController();
    $controller->index();
} elseif ($uri === '/escala/create') {
    $controller = new \Controllers\EscalaController();
    $controller->create();
} elseif ($uri === '/escala/store') {
    $controller = new \Controllers\EscalaController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            \Helpers\SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            $controller->store($_SESSION['celula_id'], $_POST);
            header("Location: /");
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala/create");
            exit;
        }
    } else {
        $controller->create();
    }
} elseif ($uri === '/escala/edit') {
    $controller = new \Controllers\EscalaController();
    $controller->edit();
} elseif ($uri === '/escala/update') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            \Helpers\SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            $controller = new \Controllers\EscalaController();
            $controller->updateStore($_SESSION['celula_id'], $_POST);
            $_SESSION['flash_success'] = "Escala/Liturgia atualizada com sucesso!";
            header("Location: /escala/show?id=" . (int)($_POST['liturgia_id'] ?? 0));
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala/edit?id=" . (int)($_POST['liturgia_id'] ?? 0));
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/escala/show' || preg_match('/^\/escala\/\d+$/', $uri)) {
    $controller = new \Controllers\EscalaController();
    $controller->show();
} elseif ($uri === '/escala/delete') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            \Helpers\SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            $controller = new \Controllers\EscalaController();
            $controller->delete($_SESSION['celula_id'], $_POST);
            header("Location: /escala");
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala");
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/usuarios') {
    $controller = new \Controllers\UsuarioController();
    $controller->index();
} elseif ($uri === '/usuarios/create') {
    $controller = new \Controllers\UsuarioController();
    $controller->create();
} elseif ($uri === '/usuarios/store') {
    $controller = new \Controllers\UsuarioController();
    $controller->store();
} elseif ($uri === '/usuarios/edit') {
    $controller = new \Controllers\UsuarioController();
    $controller->edit();
} elseif ($uri === '/usuarios/update') {
    $controller = new \Controllers\UsuarioController();
    $controller->update();
} elseif ($uri === '/usuarios/delete') {
    $controller = new \Controllers\UsuarioController();
    $controller->delete();
} elseif ($uri === '/celula') {
    $controller = new \Controllers\CelulaController();
    $controller->index();
} elseif ($uri === '/celula/update') {
    $controller = new \Controllers\CelulaController();
    $controller->update();
} elseif ($uri === '/liturgia/momentos') {
    $controller = new \Controllers\MomentoLiturgiaController();
    $controller->index();
} elseif ($uri === '/liturgia/momentos/store') {
    $controller = new \Controllers\MomentoLiturgiaController();
    $controller->store();
} elseif ($uri === '/liturgia/momentos/delete') {
    $controller = new \Controllers\MomentoLiturgiaController();
    $controller->delete();
} elseif ($uri === '/perfil') {
    $controller = new \Controllers\PerfilController();
    $controller->index();
} elseif ($uri === '/perfil/create') {
    $controller = new \Controllers\PerfilController();
    $controller->create();
} elseif ($uri === '/perfil/store') {
    $controller = new \Controllers\PerfilController();
    $controller->store();
} elseif ($uri === '/perfil/edit') {
    $controller = new \Controllers\PerfilController();
    $controller->edit();
} elseif ($uri === '/perfil/update') {
    $controller = new \Controllers\PerfilController();
    $controller->update();
} elseif ($uri === '/perfil/delete') {
    $controller = new \Controllers\PerfilController();
    $controller->delete();
} elseif ($uri === '/presenca/confirmar') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $controller = new \Controllers\PresencaController();
            $controller->confirmar();
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala");
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/presenca/visitante') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $controller = new \Controllers\PresencaController();
            $controller->registrarVisitante();
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala");
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/presenca/cancelar') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $controller = new \Controllers\PresencaController();
            $controller->cancelar();
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala");
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/escala/musica/adicionar') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $controller = new \Controllers\MusicaController();
            $controller->store();
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala");
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/escala/musica/remover') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $controller = new \Controllers\MusicaController();
            $controller->delete();
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /escala");
            exit;
        }
    } else {
        header("Location: /escala");
        exit;
    }
} elseif ($uri === '/escala/cifra') {
    $controller = new \Controllers\MusicaController();
    $controller->cifraView();
} else {
    http_response_code(404);
    $errorMessage = "Página não encontrada (404): " . htmlspecialchars($uri);
    require_once __DIR__ . '/../Views/errors/500.php';
    exit;
}
