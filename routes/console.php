<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('users:reset-settings', function () {
    User::resetSettingsOfAllUsers();
    $this->info('All user settings have been reset!');
});

Artisan::command('users:reset-table-headers-by-key {key}', function (string $key) {
    User::resetTableHeadersByKeyForAllUsers($key);
    $this->info("All users '{$key}' headers were reset successfully.");
});
