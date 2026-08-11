<?php

namespace Helpers;

/**
 * Class EnvHelper
 * Carregador utilitário nativo de arquivos .env para suporte a variáveis de ambiente sem dependências externas.
 */
class EnvHelper {
    /**
     * Carrega e interpreta um arquivo .env injetando as variáveis no putenv(), $_ENV e $_SERVER.
     *
     * @param string $filePath Caminho absoluto do arquivo .env.
     * @return void
     */
    public static function load($filePath) {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remove aspas envolventes se presentes
                if ((strpos($value, '"') === 0 && substr($value, -1) === '"') ||
                    (strpos($value, "'") === 0 && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }

                if (!empty($name)) {
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}
