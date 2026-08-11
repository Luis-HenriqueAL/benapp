<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/EscalaControllerTest.php';
require_once __DIR__ . '/UsuarioControllerTest.php';
require_once __DIR__ . '/CelulaControllerTest.php';
require_once __DIR__ . '/MomentoLiturgiaControllerTest.php';
require_once __DIR__ . '/LiturgiaModelTest.php';
require_once __DIR__ . '/EscalaModelTest.php';

$testClasses = [
    new EscalaControllerTest(),
    new UsuarioControllerTest(),
    new CelulaControllerTest(),
    new MomentoLiturgiaControllerTest(),
    new LiturgiaModelTest(),
    new EscalaModelTest()
];

$passed = 0;
$failed = 0;

foreach ($testClasses as $test) {
    $className = get_class($test);
    echo "=== Executando {$className} ===\n";
    $methods = get_class_methods($test);

    foreach ($methods as $method) {
        if (strpos($method, 'test') === 0) {
            $test->expectedException = null;
            $test->expectedExceptionMessage = null;
            try {
                if (method_exists($test, 'setUp')) {
                    $test->setUp();
                }
                $test->$method();
                if ($test->expectedException !== null) {
                    echo "[FAIL] {$method}: Exceção {$test->expectedException} era esperada, mas nenhuma foi lançada.\n";
                    $failed++;
                } else {
                    echo "[OK] {$method}\n";
                    $passed++;
                }
            } catch (\Throwable $e) {
                if ($test->expectedException !== null) {
                    if ($test->expectedExceptionMessage !== null && strpos($e->getMessage(), $test->expectedExceptionMessage) === false) {
                        echo "[FAIL] {$method}: Exceção esperada com mensagem '{$test->expectedExceptionMessage}', mas veio '{$e->getMessage()}'\n";
                        $failed++;
                    } else {
                        echo "[OK] {$method} (Exceção capturada como esperado)\n";
                        $passed++;
                    }
                } else {
                    echo "[FAIL] {$method}: " . $e->getMessage() . "\n";
                    $failed++;
                }
            }
        }
    }
}

echo "\nResultados Gerais: {$passed} passaram, {$failed} falharam.\n";
exit($failed > 0 ? 1 : 0);
