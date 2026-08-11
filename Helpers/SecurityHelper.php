<?php

namespace Helpers;

/**
 * Class SecurityHelper
 * Fornece métodos utilitários para proteção contra CSRF e XSS.
 */
class SecurityHelper {

    /**
     * Gera e armazena o token CSRF na sessão do usuário.
     *
     * @return string Token CSRF hexadecimal único.
     */
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
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
    public static function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
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
    public static function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
