<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Contests close on their own: expired attempts are scored, finished contests
| are ranked and paid out. Runs every minute so a contest that ends at 20:40
| has its leaderboard settled by 20:41.
*/
Schedule::command('contests:finalize')->everyMinute()->withoutOverlapping();
