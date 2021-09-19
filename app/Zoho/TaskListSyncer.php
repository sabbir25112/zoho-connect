<?php

namespace App\Zoho;

use App\Jobs\CreateOrUpdateTaskListJob;

class TaskListSyncer extends ZohoDataSyncer
{

    private $project;

    public function __construct($project)
    {
        $this->project = $project;
        $project['link'] = json_decode($project['link'], 1);
        $this->API = $project['link']['tasklist']['url'];
        $this->method = self::GET_REQUEST;

        $this->params = [
            'flag' => 'allflag',
        ];
    }

    function parseResponse($response)
    {
        return $response['tasklists'] ?? [];
    }

    function processResponse($response)
    {
        CreateOrUpdateTaskListJob::dispatch($response, $this->project);
    }

    function isCallable(): bool
    {
        return $this->project && $this->API && $this->method;
    }
}
