<?php

namespace App\Jobs;

trait CommonJobConfig
{
    public $timeout = 1800;
    public $tries = 10;
}
