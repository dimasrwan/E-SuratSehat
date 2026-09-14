<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\User::all() as $u) {
    echo "Email: " . $u->email . " | Role: " . $u->role . " | Active: " . ($u->is_active ? 'YES' : 'NO') . " | Check '123': " . (Illuminate\Support\Facades\Hash::check('123', $u->password) ? 'MATCH' : 'NO MATCH') . "\n";
}
