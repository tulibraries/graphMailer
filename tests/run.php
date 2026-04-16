<?php

require_once __DIR__ . '/bootstrap.php';

$tests = require __DIR__ . '/GraphMailerPluginTest.php';

$failures = [];

foreach ($tests as $name => $test) {
    try {
        $test();
        echo "PASS {$name}\n";
    } catch (Throwable $exception) {
        $failures[] = [$name, $exception->getMessage()];
        echo "FAIL {$name}: {$exception->getMessage()}\n";
    }
}

if ($failures !== []) {
    exit(1);
}

echo "All tests passed.\n";
