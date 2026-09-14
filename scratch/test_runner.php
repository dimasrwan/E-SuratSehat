<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$methods = get_class_methods(Tests\Feature\MasterFakultasProgramStudiFilterTest::class);
$testMethods = array_filter($methods, fn($m) => str_starts_with($m, 'test_'));

echo "Found " . count($testMethods) . " tests:\n";
foreach ($testMethods as $method) {
    echo "- $method\n";
}
