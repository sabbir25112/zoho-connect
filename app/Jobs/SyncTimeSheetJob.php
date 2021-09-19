<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Project;
use App\Zoho\TimeSheetSyncer;
use App\Zoho\UserSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTimeSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZohoSyncJobConfigTrait;

    private $start_date;
    private $end_date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Logger::verbose("Starting SyncTimeSheetJob");

        $projects = Project::all()->toArray();
        $request_count = 0;
        $max_request_per_min = config('zoho.queue.max_request_per_min');
        $sleep_after_max_request = config('zoho.queue.sleep_after_max_request');

        foreach ($projects as $project)
        {
            try {
                Logger::verbose("Start Processing Task TimeSheetSyncer for ". $project['id']);
                $process_response = (new TimeSheetSyncer(
                    $project,
                    $this->start_date,
                    $this->end_date,
                    'task'
                ))->call(true);

                $request_count += ($process_response['call_count'] % $max_request_per_min);
                Logger::verbose("TASK:: Request Count: " . $request_count . " . It made ". $process_response['call_count'] . " call(s) for ProjectID: ". $project['id']);
                Logger::verbose("End Processing Task TimeSheetSyncer for ". $project['id']);

                if ($request_count >= $max_request_per_min) {
                    $request_count = 0;
                    Logger::verbose("Sleep for " . $sleep_after_max_request . " Seconds after Max Request");
                    sleep($sleep_after_max_request);
                }

                Logger::verbose("Start Processing Bug TimeSheetSyncer for ". $project['id']);
                $process_response = (new TimeSheetSyncer(
                    $project,
                    $this->start_date,
                    $this->end_date,
                    'bug'
                ))->call(true);

                $request_count += ($process_response['call_count'] % $max_request_per_min);
                Logger::verbose("BUG:: Request Count: " . $request_count . " . It made ". $process_response['call_count'] . " call(s) for ProjectID: ". $project['id']);
                Logger::verbose("End Processing Bug TimeSheetSyncer for ". $project['id']);

                if ($request_count >= $max_request_per_min) {
                    $request_count = 0;
                    Logger::verbose("Sleep for " . $sleep_after_max_request . " Seconds after Max Request");
                    sleep($sleep_after_max_request);
                }

                sleep(config('zoho.queue.sleep_after_processing_a_project'));
            } catch (\Exception $exception) {
                Logger::error("Unhandled error for SyncTimeSheetJob. ProjectID: ". $project['id']. " Line: ". $exception->getLine(). " Message: " . $exception->getMessage());
            }
        }

        Logger::verbose("End SyncTimeSheetJob");
    }
}
