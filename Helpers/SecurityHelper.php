<?php

namespace Helpers;

/**
 * Class SecurityHelper
 * Fornece métodos utilitários para proteção contra CSRF e XSS.
 */
class SecurityHelper
{

    /**
     * Gera e armazena o token CSRF na sessão do usuário.
     *
     * @return string Token CSRF hexadecimal único.
     */
    public static function generateCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida o token CSRF recebido na requisição contra o token gravado na sessão.
     *
     * @param string $token Token enviado via POST/Header.
     * @throws \Exception Se o token for inválido ou ausente.
     * @return bool Retorna verdadeiro se for válido.
     */
    public static function verifyCsrfToken($token)
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new \Exception("Token CSRF inválido.");
        }
        return true;
    }

    /**
     * Escapa caracteres especiais HTML para prevenir vulnerabilidades XSS.
     *
     * @param string|null $string Valor a ser sanitizado.
     * @return string Texto seguro para ser renderizado no HTML.
     */
    public static function e($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Verifica se o usuário autenticado possui uma permissão específica.
     * Líderes sempre retornam verdadeiro (acesso irrestrito).
     *
     * @param string $chave Chave da permissão (ex: 'escala.create', 'usuarios.manage').
     * @return bool Verdadeiro se o usuário tiver a permissão.
     */
    public static function hasPermissao($chave)
    {
        if (isset($_SESSION['visitante']) && !isset($_SESSION['user'])) {
            return $chave === 'escala.view';
        }
        $perfil = $_SESSION['user']['perfil'] ?? '';
        if ($perfil === 'LIDER')
            return true;
        $permissoes = $_SESSION['permissoes'] ?? [];
        return in_array($chave, $permissoes, true);
    }

    /**
     * Determina a rota inicial padrão apropriada para o usuário autenticado com base em suas permissões.
     *
     * @return string URL da rota destino autorizada.
     */
    public static function getDefaultRoute()
    {
        if (!isset($_SESSION['user'])) {
            return '/login';
        }
        if (self::hasPermissao('escala.view')) {
            return '/';
        }
        if (self::hasPermissao('usuarios.view')) {
            return '/usuarios';
        }
        if (self::hasPermissao('celula.edit')) {
            return '/celula';
        }
        if (self::hasPermissao('liturgia.momentos')) {
            return '/liturgia/momentos';
        }
        if (self::hasPermissao('perfil.manage')) {
            return '/perfil';
        }
        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        return "/usuarios/edit?id={$userId}";
    }

    /**
     * Sanitiza a mensagem de erro para exibição ao usuário final.
     * Se APP_DEBUG for falso (ou não for 'true'/'1'), oculta erros internos de banco de dados e SQL.
     *
     * @param \Throwable|string $error
     * @return string Mensagem de erro apropriada para o usuário.
     */
    public static function formatUserErrorMessage($error)
    {
        $message = is_string($error) ? $error : $error->getMessage();

        $isDbError = ($error instanceof \PDOException) ||
            (strpos($message, 'SQLSTATE') !== false) ||
            (strpos($message, 'General error:') !== false) ||
            (strpos($message, 'attempt to write') !== false) ||
            (strpos($message, 'no such column') !== false) ||
            (strpos($message, 'database is locked') !== false) ||
            (strpos($message, 'readonly database') !== false);

        $appDebugRaw = strtolower(trim((string) (getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? ''))));
        $isDebugMode = ($appDebugRaw === 'true' || $appDebugRaw === '1');

        if (!$isDebugMode && $isDbError) {
            return "Ocorreu um erro ao processar a requisição. Por favor, tente novamente ou contate o administrador.";
        }

        return $message;
    }
}
