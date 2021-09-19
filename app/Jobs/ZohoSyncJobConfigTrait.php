<?php

namespace App\Jobs;

trait ZohoSyncJobConfigTrait
{
    public $timeout = 7200;
    public $tries = 10;
}
