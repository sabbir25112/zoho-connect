<?php

namespace App\Jobs;

use App\Logger;
use App\Models\Bug;
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

class CreateOrUpdateBugsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZohoSyncJobConfigTrait;

    protected $project;
    protected $bugs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bugs, $project)
    {
        $this->bugs = $bugs;
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bug_columns = Schema::getColumnListing((new Bug())->getTable());
        $json_columns = [
            'link',
            'severity',
            'reproducible',
            'module',
            'classification',
            'GROUP_NAME',
            'status',
        ];
        foreach ($this->bugs as $bug)
        {
            $bug = Arr::only($bug, $bug_columns);

            try {
                $bug['project_id'] = $this->project['id'];

                if (isset($bug['updated_time'])) {
                    $bug['updated_time'] = Carbon::createFromFormat('m-d-Y', $bug['updated_time']);
                }

                if (isset($bug['created_time'])) {
                    $bug['created_time'] = Carbon::createFromFormat('m-d-Y', $bug['created_time']);
                }

                $bug = prepare_json_columns($bug, $json_columns);
                if ($bug_model = Bug::find($bug['id'])) {
                    $bug_model->update($bug);
                } else {
                    Bug::create($bug);
                }
            } catch (\Exception $exception) {
                Logger::verbose("Unexpected Error in CreateOrUpdateBugsJob. Line: ". $exception->getLine(). " . Message: ". $exception->getMessage());
                Log::error($exception);
            }
        }
    }
}
