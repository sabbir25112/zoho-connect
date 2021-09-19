<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Project;
use App\Zoho\TaskListSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTaskListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZohoSyncJobConfigTrait;

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

        Logger::verbose("Starting Demo SyncTaskListJob");
        sleep(60 * 10);
        Logger::verbose("End Demo SyncTaskListJob");
        return ;

        Logger::verbose("Starting SyncTaskListJob");

        $projects = Project::all()->toArray();
        $request_count = 0;
        $max_request_per_min = config('zoho.queue.max_request_per_min');
        $sleep_after_max_request = config('zoho.queue.sleep_after_max_request');
        foreach ($projects as $project)
        {
            Logger::verbose("Start Processing UserSyncer for ". $project['id']);

            $process_response = (new TaskListSyncer($project))->call(true);
            $request_count += ($process_response['call_count'] % $max_request_per_min);
            Logger::verbose("Request Count: " . $request_count . " . It made ". $process_response['call_count'] . " call(s) for ProjectID: ". $project['id']);

            if ($request_count > $max_request_per_min) {
                $request_count = 0;
                Logger::verbose("Sleep for " . $sleep_after_max_request . " Seconds after Max Request");
                sleep($sleep_after_max_request);
            }

            Logger::verbose("End Processing TaskListSyncer for ". $project['id']);
            sleep(config('zoho.queue.sleep_after_processing_a_project'));
        }

        Logger::verbose("End SyncTaskListJob");
    }
}
