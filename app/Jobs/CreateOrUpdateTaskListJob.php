<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Tasklist;
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

class CreateOrUpdateTaskListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZohoSyncJobConfigTrait;

    private $taskLists;
    private $project;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($taskLists, $project)
    {
        $this->taskLists = $taskLists;
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $taskLists_columns = Schema::getColumnListing((new Tasklist())->getTable());
        foreach ($this->taskLists as $taskList) {
            try {
                $taskList = Arr::only($taskList, $taskLists_columns);
                $taskList['project_id'] = $this->project['id'];

                if (isset($taskList['created_time'])) {
                    $taskList['created_time'] = Carbon::createFromFormat('m-d-Y', $taskList['created_time']);
                }

                if (isset($taskList['last_updated_time'])) {
                    $taskList['last_updated_time'] = Carbon::createFromFormat('m-d-Y', $taskList['last_updated_time']);
                }

                $formatted_tasklist_data = prepare_json_columns($taskList, ['task_count', 'link']);
                if ($taskList_model = Tasklist::find($formatted_tasklist_data['id'])) {
                    Logger::verbose("Found Tasklist with id " . $formatted_tasklist_data['id'] . " , Updating...");
                    $taskList_model->update($formatted_tasklist_data);
                } else {
                    Logger::verbose("Creating Tasklist with id " . $formatted_tasklist_data['id']);
                    Tasklist::create($formatted_tasklist_data);
                }
            } catch (\Exception $exception) {
                Logger::verbose("Unexpected Error in CreateOrUpdateTaskListJob. Line: " . $exception->getLine() . " . Message: " . $exception->getMessage());
                Log::error($exception);
            }
        }
    }
}
