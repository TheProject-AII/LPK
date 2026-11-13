<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['database.default' => 'mysql_back_office']);

$user = App\Models\User::where('email', 'admin@lpk.test')->first();

var_export([
    'user_found' => (bool) $user,
    'password_match' => $user ? Illuminate\Support\Facades\Hash::check('LPKtest#2026!', $user->password) : false,
    'user_active' => $user?->active,
    'lpk_active' => $user?->userLpk?->active,
]);
