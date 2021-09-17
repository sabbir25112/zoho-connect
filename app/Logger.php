<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Logger
{
    public static function verbose($message)
    {
        if(! empty(config('app.verbose'))) echo ' ' . $message .  ' <br />' . PHP_EOL;
    }

    public static function error($message)
    {
        Log::error($message);

        if(! empty(config('app.verbose'))) echo ' ' . $message . ' <br />' . PHP_EOL;
    }

    public static function info($message)
    {
        Log::info($message);

        if(! empty(config('app.verbose'))) echo ' ' . $message .  ' <br />' . PHP_EOL;
    }

    public static function warning($message)
    {
        Log::warning($message);

        if(! empty(config('app.verbose'))) echo ' ' . $message .  ' <br />' . PHP_EOL;
    }
}
