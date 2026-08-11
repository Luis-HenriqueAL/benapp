<?php

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
