<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan pengecekan bot pengingat & spam anti-lupa farm setiap menit
// Menggunakan withoutOverlapping agar tidak double-run jika proses belum selesai
Schedule::command('bot:notify')->everyMinute()->withoutOverlapping();

