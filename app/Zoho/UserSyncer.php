<?php

namespace App\Zoho;

use App\Jobs\CreateOrUpdateUserJob;
use App\Logger;

class UserSyncer extends ZohoDataSyncer
{
    private $project;

    public function __construct($project)
    {
        $this->project = $project;
        $project['link'] = json_decode($project['link'], 1);
        $this->API = $project['link']['user']['url'];
        $this->method = static::GET_REQUEST;
    }

    function isCallable(): bool
    {
        return $this->project && $this->API && $this->method;
    }

    function parseResponse($response): array
    {
        return $response['users'] ?? [];
    }

    function processResponse($response)
    {
        Logger::verbose("Dispatching CreateOrUpdateUserJob for ". $this->project['id']);
        CreateOrUpdateUserJob::dispatch($response, $this->project);
    }
}
