<?php

namespace App\Zoho;

use App\Models\TimeSheet;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TimeSheetSyncer extends ZohoDataSyncer
{
    private $project,
        $component_type;

    public function __construct($project, $start_date, $end_date, $component_type)
    {
        $this->project = $project;
        $this->component_type = $component_type;

        $custom_date = ['start_date' => $start_date, 'end_date' => $end_date];
        $this->params = [
            'users_list' => 'all',
            'view_type' => 'custom_date',
            'date' => $start_date,
            'bill_status' => 'All',
            'component_type' => $component_type,
            'custom_date' => json_encode($custom_date)
        ];

        $project['link'] = json_decode($project['link'], 1);
        $this->API = $project['link']['timesheet']['url'];
        $this->method = static::GET_REQUEST;

        // $this->disableSkipPrediction();
    }

    function parseResponse($response): array
    {
        $output = [];

        $time_logs = $response['timelogs']['date'] ?? [];
        foreach ($time_logs as $time_log)
        {
            $log_date       = Carbon::createFromFormat('m-d-Y', $time_log['date']);
            $log_date_long  = $time_log['date_long'];
            $logs = $this->component_type == 'task' ? $time_log['tasklogs'] : $time_log['buglogs'];
            foreach ($logs as $log)
            {
                $log['log_date']        = $log_date;
                $log['log_date_long']   = $log_date_long;
                $log['type']            = $this->component_type;
                $output[]               = $log;
            }
        }

        return $output;
    }

    function processResponse($response)
    {
        $timesheet_columns = Schema::getColumnListing((new TimeSheet())->getTable());

        foreach ($response as $timesheet)
        {
            try {
                $timesheet = Arr::only($timesheet, $timesheet_columns);
                $timesheet_data = $this->prepareTimeSheetData($this->project, $timesheet);
                if ($timesheet_model = TimeSheet::find($timesheet_data['id'])) {
                    $timesheet_model->update($timesheet_data);
                } else {
                    TimeSheet::create($timesheet_data);
                }
            } catch (\Exception $exception) {
                Log::error($exception);
                continue;
            }
        }
    }

    function isCallable(): bool
    {
        return $this->project && in_array($this->component_type, ['task', 'bug']);
    }

    private function prepareTimeSheetData($project, $timesheet)
    {
        $json_fields = ['link', 'task', 'bug', 'added_by', 'task_list'];

        $timesheet['project_id'] = $project['id'];

        if (isset($timesheet['task']['is_sub_task']) && $timesheet['task']['is_sub_task']) {
            $timesheet['subtask_id']    = $timesheet['task']['id'] ?? null;
            $timesheet['subtask_name']  = $timesheet['task']['name'] ?? null;
            $timesheet['task_id']       = $timesheet['task']['root_task_id'] ?? null;
        } else {
            $timesheet['task_id']       = $timesheet['task']['id'] ?? null;
            $timesheet['task_name']     = $timesheet['task']['name'] ?? null;
        }

        if (isset($timesheet['created_date'])) {
            $timesheet['created_date'] = Carbon::createFromFormat('m-d-Y', $timesheet['created_date']);
        }

        if (isset($timesheet['last_modified_date'])) {
            $timesheet['last_modified_date'] = Carbon::createFromFormat('m-d-Y', $timesheet['last_modified_date']);
        }

        return prepare_json_columns($timesheet, $json_fields);
    }
}
