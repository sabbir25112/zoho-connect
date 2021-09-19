<?php

namespace App\Jobs;

use App\Logger;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateOrUpdateUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZohoSyncJobConfigTrait;

    protected $project;
    protected $users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $project)
    {
        $this->users = $users;
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_columns = Schema::getColumnListing((new User())->getTable());

        foreach ($this->users as $user)
        {
            $user = Arr::only($user, $user_columns);

            try {
                $user['project_id'] = $this->project['id'];
                if ($user_model = User::find($user['id'])) {
                    Logger::verbose("Found User with id ". $user['id'] . " , Updating...");
                    $user_model->update($user);
                } else {
                    Logger::verbose("Creating User with id ". $user['id']);
                    User::create($user);
                }
            } catch (\Exception $exception) {
                Logger::verbose("Unexpected Error in CreateOrUpdateUserJob. Line: ". $exception->getLine(). " . Message: ". $exception->getMessage());
                Log::error($exception);
            }
        }
    }
}
