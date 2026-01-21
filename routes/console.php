<?php

use App\Models\Atx;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Process;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Country::recalculateAllProcessCountsInDatabase();
    Currency::updateAllUSDRatios();
    Process::recalculateAllDaysPastSinceLastActivity();
})->daily();

Artisan::command('users:reset-settings', function () {
    User::resetSettingsOfAllUsers();
    $this->info('All user settings have been reset!');
});

Artisan::command('users:reset-table-headers-by-key {key}', function (string $key) {
    User::resetTableHeadersByKeyForAllUsers($key);
    $this->info("All users '{$key}' headers were reset successfully.");
});

Artisan::command('atx:delete-unused', function () {
    Atx::whereDoesntHave('products')->delete();
    $this->info('All unused ATX have been deleted!');
});

// Temporary function
Artisan::command('comments:delete-all-order-part-comments', function () {
    Comment::whereIn('commentable_type', [
        Order::class,
        OrderProduct::class,
        Invoice::class,
        Shipment::class,
    ])->delete();

    $this->info('All order part comments have been deleted!');
});
