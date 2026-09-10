<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw("Test", function ($m) {
        $m->to("dimasrwann@gmail.com")->subject("Test");
    });
    echo "Success\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
