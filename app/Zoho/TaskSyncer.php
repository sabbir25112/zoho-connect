<?php

namespace App\Zoho;

use App\Jobs\CreateOrUpdateTaskJob;

class TaskSyncer extends ZohoDataSyncer
{
    private $project;

    public function __construct($project)
    {
        $this->project = $project;
        $project['link'] = json_decode($project['link'], 1);
        $this->API = $project['link']['task']['url'];
        $this->method = static::GET_REQUEST;
    }

    function parseResponse($response): array
    {
        return $response['tasks'] ?? [];
    }

    function processResponse($response)
    {
        CreateOrUpdateTaskJob::dispatch($response, $this->project);
    }

    function isCallable(): bool
    {
        return $this->project && $this->API && $this->method;
    }
}
