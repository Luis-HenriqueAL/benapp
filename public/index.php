<?php
session_start();

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

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

// Middleware de Proteção de Autenticação
if (!isset($_SESSION['user'])) {
    header("Location: /login");
    exit;
}

// Middleware de Tenant
if (!isset($_SESSION['celula_id'])) {
    $_SESSION['celula_id'] = $_SESSION['user']['celula_id'] ?? 1;
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
} elseif (preg_match('/^\/escala\/\d+$/', $uri)) {
    // Rota de visualização de escala específica (ex: /escala/1)
    $controller = new \Controllers\EscalaController();
    $controller->index();
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
} else {
    http_response_code(404);
    $errorMessage = "Página não encontrada (404): " . htmlspecialchars($uri);
    require_once __DIR__ . '/../Views/errors/500.php';
    exit;
}
