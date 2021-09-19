<?php

namespace App\Zoho;

use App\Jobs\CreateOrUpdateBugsJob;

class BugSyncer extends ZohoDataSyncer
{
    private $project;

    public function __construct($project)
    {
        $this->project = $project;
        $project['link'] = json_decode($project['link'], 1);
        $this->API = $project['link']['bug']['url'];
        $this->method = static::GET_REQUEST;
    }

    function parseResponse($response)
    {
        return $response['bugs'] ?? [];
    }

    function processResponse($response)
    {
        CreateOrUpdateBugsJob::dispatch($response, $this->project);
    }

    function isCallable(): bool
    {
        return $this->project && $this->API && $this->method;
    }
}
