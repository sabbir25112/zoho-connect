<?php

namespace App\Zoho;

use App\Models\SubTask;
use App\Models\TaskCustom;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SubTaskSyncer extends ZohoDataSyncer
{
    private $task;
    private $project;

    public function __construct($task, $project)
    {
        $this->task = $task;
        $this->project = $project;

        $task['link'] = json_decode($task['link'], 1);
        $this->API = $task['link']['subtask']['url'];
        $this->method = static::GET_REQUEST;
    }

    function parseResponse($response): array
    {
        return $response['tasks'] ?? [];
    }

    function processResponse($response)
    {
        $this->createOrUpdateSubTask($response, $this->project);
    }

    function isCallable(): bool
    {
        return $this->task && $this->API && $this->method;
    }

    private function createOrUpdateSubTask($tasks, $project)
    {
        $task_columns = Schema::getColumnListing((new SubTask())->getTable());
        foreach ($tasks as $task) {
            try {
                $task = Arr::only($task, $task_columns);
                $task['percent_complete'] = (float) $task['percent_complete'];
                $task['project_id'] = $project['id'];

                if (isset($task['created_time'])) {
                    $task['created_time'] = Carbon::createFromFormat('m-d-Y', $task['created_time']);
                }

                if (isset($task['last_updated_time'])) {
                    $task['last_updated_time'] = Carbon::createFromFormat('m-d-Y', $task['last_updated_time']);
                }

                $json_columns = [
                    'details',
                    'custom_fields',
                    'task_followers',
                    'GROUP_NAME',
                    'log_hours',
                    'tasklist',
                    'status',
                    'link',
                ];

                $formatted_task_data = prepare_json_columns($task, $json_columns);
                if ($task_model = SubTask::find($formatted_task_data['id'])) {
                    $task_model->update($formatted_task_data);
                } else {
                    $task_model = SubTask::create($formatted_task_data);
                }
                $this->createOrUpdateTaskCustoms($formatted_task_data['custom_fields'] ? json_decode($formatted_task_data['custom_fields'], true) : [], $task_model);
            } catch (\Exception $exception) {
                Log::error($exception);
            }
        }
    }

    private function createOrUpdateTaskCustoms($customs, $task)
    {
        foreach ($customs as $custom)
        {
            try {
                if (!isset($custom['label_name']) || !isset($custom['label_value'])) continue;

                $customData = [
                    'TaskID'     => $task->id,
                    'label_name' => $custom['label_name'],
                    'label_value'=> $custom['label_value'],
                ];

                $taskCustom = TaskCustom::where('TaskID', $task->id)
                    ->where('label_name', $custom['label_name'])
                    ->first();
                if ($taskCustom) {
                    $taskCustom->update($customData);
                } else {
                    TaskCustom::create($customData);
                }
            } catch (\Exception $exception) {
                Log::error($exception);
            }
        }
    }
}
