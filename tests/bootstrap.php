<?php

namespace {
    spl_autoload_register(function ($class) {
        if ($class === 'Config\Database') {
            // Mock DB connection for tests to avoid needing a real DB
            eval('
            namespace Config;
            class Database {
                private static $conn = null;
                public static function getConnection() {
                    if (self::$conn === null) {
                        self::$conn = new \PDO("sqlite::memory:", null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
                    }
                    return self::$conn;
                }
            }
            ');
            return;
        }

        $base_dir = __DIR__ . '/../';
        $file = $base_dir . str_replace('\\', '/', $class) . '.php';

        if (file_exists($file)) {
            require $file;
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



