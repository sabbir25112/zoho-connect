<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Logger
{
    public static function verbose($message)
    {
        self::showMessageWithTime($message);
    }

    public static function error($message, $exception = null)
    {
        Log::error($message);
        if ($exception) Log::error($exception);
        self::showMessageWithTime($message);
    }

    public static function info($message)
    {
        Log::info($message);
        self::showMessageWithTime($message);
    }

    public static function warning($message)
    {
        Log::warning($message);
        self::showMessageWithTime($message);
    }

    private static function showMessageWithTime($message)
    {
        if(! empty(config('app.verbose'))) {
            $time = Carbon::now()->toDateTimeString();
            echo "\e[0;32;40m$time\e[0m $message\n";
        }
    }
}
