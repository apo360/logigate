<?php

use App\Console\Commands\DispatchDueTasks;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:maintain')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('billing:maintain --notices')->dailyAt('08:00')->withoutOverlapping()->onOneServer();

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

// A cada minuto, verifica tarefas LEANDER
Schedule::command(DispatchDueTasks::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
