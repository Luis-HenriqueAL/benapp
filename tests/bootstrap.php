<?php

namespace Config {
    class Database {
        private static $conn = null;

        public static function getConnection() {
            if (self::$conn === null) {
                self::$conn = new \PDO("sqlite::memory:", null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
                self::$conn->exec("
                    CREATE TABLE IF NOT EXISTS celulas_info (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        nome VARCHAR(255) NULL,
                        horario VARCHAR(50) NULL,
                        dia_semana VARCHAR(50) NULL,
                        logradouro VARCHAR(255) NULL,
                        numero VARCHAR(50) NULL,
                        bairro VARCHAR(100) NULL,
                        cep VARCHAR(20) NULL,
                        anfitrioes TEXT NULL,
                        lideres TEXT NULL
                    );
                    CREATE TABLE IF NOT EXISTS liturgias (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        data_culto DATE NULL,
                        data_liturgia DATE NULL,
                        tema VARCHAR(255) NULL
                    );
                    CREATE TABLE IF NOT EXISTS presencas (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        usuario_id INT NULL,
                        nome_visitante VARCHAR(255) NULL,
                        qtd_visitas INT DEFAULT 1,
                        tipo VARCHAR(20) DEFAULT 'membro',
                        registrado_por_id INT NULL,
                        codigo_acesso VARCHAR(20) NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                    CREATE TABLE IF NOT EXISTS usuarios (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        nome VARCHAR(255) NULL,
                        email VARCHAR(255) NULL,
                        senha VARCHAR(255) NULL,
                        perfil VARCHAR(50) DEFAULT 'MEMBRO',
                        status VARCHAR(20) DEFAULT 'ativo'
                    );
                    CREATE TABLE IF NOT EXISTS escalas (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        celula_id INT NOT NULL DEFAULT 1,
                        liturgia_id INT NOT NULL,
                        funcao_id VARCHAR(100) NULL,
                        voluntario_id INT NULL
                    );
                ");
            }
            return self::$conn;
        }
    }
}

namespace {
    spl_autoload_register(function ($class) {
        $base_dir = __DIR__ . '/../';
        $file = $base_dir . str_replace('\\', '/', $class) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

namespace PHPUnit\Framework {
    if (!class_exists('PHPUnit\Framework\TestCase')) {
        class TestCase {
            public $expectedException = null;
            public $expectedExceptionMessage = null;

            public function expectException($exception) {
                $this->expectedException = $exception;
            }
            public function expectExceptionMessage($message) {
                $this->expectedExceptionMessage = $message;
            }
            public function assertTrue($condition, $message = '') {
                if (!$condition) throw new \Exception("Assertion failed: expected true. " . $message);
            }
            public function createMock($originalClassName) {
                return new class {
                    public $stubs = [];
                    public function method($name) {
                        $stub = new class {
                            public $val;
                            public function willReturn($val) { $this->val = $val; }
                        };
                        $this->stubs[$name] = $stub;
                        return $stub;
                    }
                    public function __call($name, $args) {
                        if (isset($this->stubs[$name])) {
                            return $this->stubs[$name]->val;
                        }
                        return null;
                    }
                };
            }
        }
    }
}
