<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\Tasklist;
use App\Models\TimeSheet;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function start()
    {
        $count = [
            'projects'  => Project::count(),
            'users'     => User::count(),
            'tasklists' => Tasklist::count(),
            'tasks'     => Task::count(),
            'subtasks'  => SubTask::count(),
            'timesheets'=> TimeSheet::count(),
            'bugs'      => Bug::count()
        ];
        $projects = Project::pluck('name', 'id')->toArray();

        return view('start', compact('count', 'projects'));
    }
}
