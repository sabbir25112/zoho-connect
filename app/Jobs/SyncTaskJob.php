<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Project;
use App\Zoho\TaskSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTaskJob implements ShouldQueue
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
        Logger::verbose("Starting SyncTaskJob");

        $projects = Project::all()->toArray();
        foreach ($projects as $project)
        {
            try {
                Logger::verbose("Start Processing TaskSyncer for ". $project['id']);
                (new TaskSyncer($project))->call();
                Logger::verbose("End Processing TaskSyncer for ". $project['id']);
                sleep(config('zoho.queue.sleep_after_processing_a_project'));
            } catch (\Exception $exception) {
                dd($exception);
            }
        }

        Logger::verbose("End SyncTaskJob");
    }
}
