<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Project;
use App\Zoho\UserSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncUserJob implements ShouldQueue
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
        Logger::verbose("Starting SyncUserJob");

        $projects = Project::all()->toArray();
        foreach ($projects as $project)
        {
            Logger::verbose("Start Processing UserSyncer for ". $project['id']);
            (new UserSyncer($project))->call();
            Logger::verbose("End Processing UserSyncer for ". $project['id']);
            sleep(config('zoho.queue.sleep_after_processing_a_project'));
        }

        Logger::verbose("End SyncUserJob");
    }
}
