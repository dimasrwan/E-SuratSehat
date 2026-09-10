<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $p = App\Models\Pemeriksaan::find(4);
    if (!$p) die("Pemeriksaan 4 not found\n");
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('pemeriksaan.pdf', ['pemeriksaan' => $p]);
    $content = $pdf->output();
    echo "Success: " . strlen($content) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
