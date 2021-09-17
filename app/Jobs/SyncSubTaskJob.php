<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Project;
use App\Models\Task;
use App\Zoho\SubTaskSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSubTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CommonJobConfig;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Logger::verbose("Starting SyncSubTaskJob");

        $projects = Project::all()->toArray();
        foreach ($projects as $project)
        {
            Logger::verbose("Start Processing TaskSyncer for ". $project['id']);

            $tasks = Task::where(['project_id' => $project['id'], 'subtasks' => 1])->get()->toArray();
            $chunked_tasks = array_chunk($tasks, config('zoho.queue.max_request_per_min'));

            foreach ($chunked_tasks as $chunked_task)
            {
                foreach ($chunked_task as $task)
                {
                    (new SubTaskSyncer($task, $project))->call();
                }
                Logger::verbose("Sleep for " . config('zoho.queue.sleep_after_max_request') . " Seconds after Max Request");
                sleep(config('zoho.queue.sleep_after_max_request'));
            }
            Logger::verbose("End Processing TaskSyncer for ". $project['id']);
            Logger::verbose("Sleep for " . config('zoho.queue.sleep_after_processing_a_project') . " Seconds");
            sleep(config('zoho.queue.sleep_after_processing_a_project'));
        }

        Logger::verbose("End SyncSubTaskJob");
    }
}
