<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Task;
use App\Models\TaskBilling;
use App\Models\TaskOwner;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateOrUpdateTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CommonJobConfig;

    protected $project;
    protected $tasks;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tasks, $project)
    {
        $this->tasks = $tasks;
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $task_columns = Schema::getColumnListing((new Task())->getTable());

        foreach ($this->tasks as $task) {
            try {
                $task = Arr::only($task, $task_columns);
                $task['project_id'] = $this->project['id'];

                if (isset($task['created_time'])) {
                    $task['created_time'] = Carbon::createFromFormat('m-d-Y', $task['created_time']);
                }

                if (isset($task['last_updated_time'])) {
                    $task['last_updated_time'] = Carbon::createFromFormat('m-d-Y', $task['last_updated_time']);
                }

                $json_columns = ['details', 'link', 'custom_fields', 'log_hours', 'status'];

                $formatted_task_data = prepare_json_columns($task, $json_columns);
                $task_model = Task::find($formatted_task_data['id']);
                if ($task_model) {
                    $task_model->update($formatted_task_data);
                } else {
                    $task_model = Task::create($formatted_task_data);
                }
                $owners = json_decode($task_model->details, true);

                $this->createOrUpdateTaskOwners($owners['owners'] ?? [], $task_model);
                $this->createOrUpdateTaskStatuses($task['status'] ?? [], $task_model);

                $bills = json_decode($task_model->log_hours, true);
                $taskBilling = TaskBilling::where('TaskID', $task_model->id)->first();
                if (!$taskBilling) {
                    TaskBilling::create([
                        'non_billable_hours' => $bills['non_billable_hours'] ?? '0',
                        'billable_hours'     => $bills['billable_hours'] ?? '0',
                        'TaskID'             => $task_model->id
                    ]);
                } else {
                    $taskBilling->update([
                        'non_billable_hours' => $bills['non_billable_hours'] ?? '0',
                        'billable_hours'     => $bills['billable_hours'] ?? '0',
                    ]);
                }
            } catch (\Exception $exception) {
                Logger::verbose("Unexpected Error in CreateOrUpdateTaskJob. Line: ". $exception->getLine(). " . Message: ". $exception->getMessage());
                Log::error($exception);
            }

        }
    }

    private function createOrUpdateTaskOwners($owners, $task)
    {
        foreach ($owners as $owner)
        {
            try {
                if (!isset($owner['id'])) continue;

                $ownerData = [
                    'TaskID'    => $task->id,
                    'OwnerID'   => $owner['id'],
                    'name'      => $owner['name'] ?? '',
                    'email'     => $owner['email'] ?? '',
                    'zpuid'     => $owner['zpuid'] ?? '',
                ];
                if ($taskOwner = TaskOwner::find($owner['id'])) {
                    $taskOwner->update($ownerData);
                } else {
                    TaskOwner::create($ownerData);
                }
            } catch (\Exception $exception) {
                Log::error($exception);
            }
        }
    }

    private function createOrUpdateTaskStatuses($status, $task)
    {
        try {
            if (!isset($status['id'])) return ;
            $statusData = [
                'status_id' => $status['id'],
                'TaskID'    => $task->id,
                'name'      => $status['name'],
                'type'      => $status['type']
            ];
            $taskStatus = TaskStatus::where('status_id', $status['id'])
                ->where('TaskID', $task->id)
                ->first();
            if ($taskStatus) {
                $taskStatus->update($statusData);
            } else {
                TaskStatus::create($statusData);
            }
        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }
}
