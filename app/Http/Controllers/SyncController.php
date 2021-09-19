<?php namespace App\Http\Controllers;

use App\Jobs\SyncBugsJob;
use App\Jobs\SyncSubTaskJob;
use App\Jobs\SyncTaskJob;
use App\Jobs\SyncTaskListJob;
use App\Jobs\SyncUserJob;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Settings;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskBilling;
use App\Models\TaskCustom;
use App\Models\Tasklist;
use App\Models\TaskOwner;
use App\Models\TaskStatus;
use App\Models\TimeSheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SyncController extends Controller
{
    private $token;

    public function __construct()
    {
        set_time_limit(0);
        $this->token = Settings::first()->access_token;
    }

    private function prepareJsonColumns($array, $json_columns)
    {
        foreach ($json_columns as $column) {
            if (isset($array[$column])) {
                $array[$column] = json_encode($array[$column]);
            }
        }
        return $array;
    }

    private function getPortals()
    {
        // return json_decode('[{"storage_type":"ZOHO_DOCS","trial_enabled":false,"can_create_project":true,"gmt_time_zone":"(GMT 10:0) Eastern Standard Time (New South Wales)","project_count":{"active":2},"role":"Employee","is_sprints_integrated":false,"avail_user_count":-1,"is_crm_partner":false,"link":{"project":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/"}},"bug_plan":"Enterprise","can_add_template":true,"locale":{"country":"United States","code":"en_US","language":"English"},"IS_LOGHR_RESTRICTEDBY_WORKHR":false,"layouts":{"projects":{"module_id":"685798000007722005"},"tasks":{"module_id":"685798000006116005"}},"gmt_time_zone_offset":36000000,"new_user_plan":false,"available_projects":-1,"default":false,"id":36249008,"bug_plural":"Bugs","is_new_plan":false,"plan":"Enterprise","percentage_calculation":"based_on_status","settings":{"business_hours":{"business_end":1020,"business_start":540},"street_address":"Suite 4, Level 2, 22 George Street","country":"Australia","default_dependency_type":"finish-start","working_days":["Monday","Tuesday","Wednesday","Thursday","Friday"],"city":"North Strathfield","timelog_period":{"isEditLogRestricted":true,"log_future_time":{"allowed":false},"log_past_time":{"customize":{"unit":"day(s)","value":"1"}}},"task_duration_type":"hours","time_zone":"Australia\/NSW","startday_of_week":"monday","task_date_format":"dd\/MM\/yyyy hh:mm aaa","timesheet":{"default_billing_status":"Billable","is_timesheet_approval_enabled":true},"website_url":"www.qrsolutions.com.au","holidays":[{"date":"04-18-2014","name":"Good Friday","id":"685798000000060079"},{"date":"04-21-2014","name":"Easter Monday","id":"685798000000060081"},{"date":"04-25-2014","name":"Anzac Day","id":"685798000000060083"},{"date":"06-09-2014","name":"Queens Birthday","id":"685798000000060085"},{"date":"09-29-2014","name":"Family & Community Day","id":"685798000000060087"},{"date":"10-06-2014","name":"Labour Day","id":"685798000000060089"},{"date":"12-25-2014","name":"Christmas Day","id":"685798000000060091"},{"date":"12-26-2014","name":"Boxing Day","id":"685798000000060093"},{"date":"01-01-2015","name":"New Year Day","id":"685798000002672033"},{"date":"01-26-2015","name":"Australia Day","id":"685798000002672035"},{"date":"04-03-2015","name":"Good Friday","id":"685798000002672037"},{"date":"04-06-2015","name":"Easter Monday","id":"685798000002672039"},{"date":"06-08-2015","name":"Queens Birthday","id":"685798000002672041"},{"date":"08-03-2015","name":"Bank Holiday","id":"685798000002672043"},{"date":"10-05-2015","name":"Labour Day","id":"685798000002672045"},{"date":"12-25-2015","name":"Christmas Day","id":"685798000002672047"}],"is_budget_enabled":false,"company_name":"QR Solutions","date_format":"dd\/MM\/yyyy hh:mm aaa","state":"NSW","postal_code":"2137","has_budget_permission":true},"avail_client_user_count":0,"is_tags_available":true,"sprints_project_permission":false,"is_display_taskprefix":true,"bug_singular":"Bug","login_zpuid":685798000014575503,"is_display_projectprefix":true,"project_prefix":"PR-","max_user_count":-1,"max_client_user_count":-1,"extensions":{"locations":{"task_transition":"685798000012231001","taskdetails_rightpanel":"685798000009601091","app_settings":"685798000008013009","issuedetails_rightpanel":"685798000009601093","issue_tab":"685798000008013013","task_tab":"685798000008013011","attachment_picker":"685798000008880073","top_band":"685798000008776003","blueprint_during":"685798000012231003","project_tab":"685798000008776001"}},"profile_id":685798000005970143,"name":"qrsolutions","id_string":"36249008","is_time_log_restriction_enabled":false,"integrations":{"people":{"is_enabled":false},"meeting":{"is_enabled":false}}}]', true);
        try {
            $api_base_url = config('zoho.url.api_base');
            $portal_api_uri = $api_base_url . '/portals/';
            $response = Http::withToken($this->token)->get($portal_api_uri);
            if ($response->successful()) {
                return $response->json()['portals'];
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function syncProjects($is_internal = false)
    {
        $portals = $this->getPortals();
        foreach ($portals as $portal) {
            $projects = $this->getProjects($portal);
            $this->createOrUpdateProjects($projects);
        }
        if (!$is_internal)  {
            session()->flash('success', 'Project Sync Complete');
            return redirect()->back();
        }
    }

    public function syncSubTasks()
    {
        if ($this->isJobInQueue(SyncSubTaskJob::class)) {
            session()->flash('error', 'Sub Task Sync Job already running in background');
            return redirect()->back();
        }

        SyncSubTaskJob::dispatch();
        session()->flash('success', 'Sub Task Sync Job running in background. Please check after some time');
        return redirect()->back();
    }

    public function syncUsers()
    {
        if ($this->isJobInQueue(SyncUserJob::class)) {
            session()->flash('error', 'User Sync Job already running in background');
            return redirect()->back();
        }

        SyncUserJob::dispatch();
        session()->flash('success', 'User Sync Job running in background. Please check after some time');
        return redirect()->back();
    }

    public function syncTasks()
    {
        if ($this->isJobInQueue(SyncTaskJob::class)) {
            session()->flash('error', 'Task Sync Job already running in background');
            return redirect()->back();
        }

        SyncTaskJob::dispatch();
        session()->flash('success', 'Task Sync Job running in background. Please check after some time');
        return redirect()->back();
    }

    public function syncBugs()
    {
        if ($this->isJobInQueue(SyncBugsJob::class)) {
            session()->flash('error', 'Bug Sync Job already running in background');
            return redirect()->back();
        }

        SyncBugsJob::dispatch();
        session()->flash('success', 'Bug Sync Job running in background. Please check after some time');
        return redirect()->back();
    }

    public function syncTaskLists()
    {
        if ($this->isJobInQueue(SyncTaskListJob::class)) {
            session()->flash('error', 'TaskList Sync Job already running in background');
            return redirect()->back();
        }

        SyncTaskListJob::dispatch();
        session()->flash('success', 'TaskList Sync Job running in background. Please check after some time');
        return redirect()->back();
    }


    private function getProjects($portal)
    {
        // return json_decode('[{"is_strict":"no","project_percent":"45","role":"Employee","bug_count":{"closed":1,"open":1},"IS_BUG_ENABLED":true,"owner_id":"680368510","bug_client_permission":"allexternal","taskbug_prefix":"AP2","link":{"activity":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/activities\/"},"document":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/documents\/"},"forum":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/forums\/"},"timesheet":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/logs\/"},"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasks\/"},"folder":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/folders\/"},"milestone":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/milestones\/"},"bug":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/bugs\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/"},"tasklist":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/"},"event":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/events\/"},"user":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/users\/"},"status":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/statuses\/"}},"custom_status_id":"685798000007722089","description":"<div><br><\/div>","milestone_count":{"closed":0,"open":0},"updated_date_long":1626955295714,"show_project_overview":false,"task_count":{"closed":58,"open":69},"updated_date_format":"07-22-2021 10:01:35 PM","workspace_id":"49oz6c24e52a06f6b4ba5a8d0a791c889c90c","custom_status_name":"Active","owner_zpuid":"685798000008180077","is_client_assign_bug":"false","bug_defaultview":"6","billing_status":"Billable","id":685798000011352647,"key":"PR-425","is_chat_enabled":true,"is_sprints_project":false,"custom_status_color":"#2cc8ba","owner_name":"Venkatraman","created_date_long":1598237721540,"group_name":"QR SOLUTIONS","created_date_format":"08-24-2020 12:55:21 PM","group_id":685798000000107049,"profile_id":685798000005970143,"enabled_tabs":["dashboard","projectfeed","tasks","bugs","milestones","calendar","documents","timesheet","invoices","forums","pages","chat","users","reports"],"name":"appsupport.io","is_public":"no","id_string":"685798000011352647","created_date":"08-24-2020","updated_date":"07-22-2021","bug_prefix":"AP2-I","cascade_setting":{"date":false,"logHours":false,"plan":true,"percentage":false,"workHours":false},"layout_details":{"task":{"name":"Standard Layout","id":"685798000006116011"},"bug":{"name":"Standard Layout","id":"685798000008839003"},"project":{"name":"Standard Layout","id":"685798000007722008"}},"status":"active"},{"is_strict":"no","project_percent":"33","role":"Employee","bug_count":{"closed":0,"open":0},"IS_BUG_ENABLED":true,"owner_id":"32229111","bug_client_permission":"allexternal","taskbug_prefix":"Q-1","link":{"activity":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/activities\/"},"document":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/documents\/"},"forum":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/forums\/"},"timesheet":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/logs\/"},"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/tasks\/"},"folder":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/folders\/"},"milestone":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/milestones\/"},"bug":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/bugs\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/"},"tasklist":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/tasklists\/"},"event":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/events\/"},"user":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/users\/"},"status":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/statuses\/"}},"custom_status_id":"685798000007722089","description":"<div>AppGateway Development for Finance Application<br><\/div><div>Hosting Applications on AWS and Managed Services&nbsp;<br><\/div><div>AppRetail extension to Finance Application&nbsp;<br><\/div><div>..Others&nbsp;<br><\/div><div><br><\/div><div>Technology Partnership in General<br><\/div>","milestone_count":{"closed":0,"open":0},"start_date_long":1585746000000,"updated_date_long":1626955295711,"show_project_overview":false,"task_count":{"closed":1,"open":2},"updated_date_format":"07-22-2021 10:01:35 PM","workspace_id":"83yijb8ea25d7189a475bbc5e7618384e6ebd","custom_status_name":"Active","owner_zpuid":"685798000007490017","is_client_assign_bug":"false","bug_defaultview":"6","billing_status":"Non Billable","id":685798000010285053,"key":"PR-414","is_chat_enabled":true,"start_date":"04-02-2020","is_sprints_project":false,"custom_status_color":"#2cc8ba","owner_name":"Murthy Vaidheeswaran","created_date_long":1586155623637,"group_name":"QR SOLUTIONS","created_date_format":"04-06-2020 04:47:03 PM","group_id":685798000000107049,"profile_id":685798000005970143,"enabled_tabs":["tasks","bugs","milestones","dashboard","projectfeed","calendar","documents","timesheet","invoices","forums","pages","chat","users","reports"],"name":"QRS - Habile - Ebiz","is_public":"no","id_string":"685798000010285053","created_date":"04-06-2020","updated_date":"07-22-2021","bug_prefix":"Q-1-I","cascade_setting":{"date":false,"logHours":false,"plan":true,"percentage":false,"workHours":false},"layout_details":{"task":{"name":"Standard Layout","id":"685798000006116011"},"bug":{"name":"Standard Layout","id":"685798000008839003"},"project":{"name":"Standard Layout","id":"685798000007722008"}},"status":"active"}]', true);
        try {
            $projects_api = $portal['link']['project']['url'];
            $response = Http::withToken($this->token)->get($projects_api);
            if ($response->successful()) {
                return $response->json()['projects'];
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    private function createOrUpdateProjects($projects)
    {
        $project_columns = Schema::getColumnListing((new Project())->getTable());

        foreach ($projects as $project) {
            try {
                $project = Arr::only($project, $project_columns);

                $json_columns = [
                    'bug_count',
                    'link',
                    'milestone_count',
                    'task_count',
                ];
                if (isset($project['created_date'])) {
                    $project['created_date'] = Carbon::createFromFormat('m-d-Y', $project['created_date']);
                }
                if (isset($project['updated_date'])) {
                    $project['updated_date'] = Carbon::createFromFormat('m-d-Y', $project['updated_date']);
                }

                $formatted_project_data = $this->prepareJsonColumns($project, $json_columns);

                if ($project_model = Project::find($formatted_project_data['id'])) {
                    $project_model->update($formatted_project_data);
                } else {
                    Project::create($formatted_project_data);
                }
            } catch (\Exception $exception) {
                Log::error($exception);
            }
        }
    }

    public function syncTimeSheet(Request $request)
    {
        $component_types = ['task', 'bug'];
        $timesheet_columns = Schema::getColumnListing((new TimeSheet())->getTable());
        $portals = $this->getPortals();
        foreach ($portals as $portal)
        {
            $projects = $this->getProjects($portal);

            foreach ($projects as $project)
            {
                foreach ($component_types as $component_type)
                {
                    $timesheets = $this->getTimeSheets($project, $request, $component_type);
                    foreach ($timesheets as $timesheet)
                    {
                        try {
                            $timesheet = Arr::only($timesheet, $timesheet_columns);
                            $timesheet_data = $this->prepareTimeSheetData($project, $timesheet);
                            if ($timesheet_model = TimeSheet::find($timesheet_data['id'])) {
                                $timesheet_model->update($timesheet_data);
                            } else {
                                TimeSheet::create($timesheet_data);
                            }
                        } catch (\Exception $exception) {
                            Log::error($exception);
                            continue;
                        }
                    }
                }
            }
        }
        session()->flash('success', 'TimeSheet Sync Complete');
        return redirect()->back();
    }

    private function prepareTimeSheetData($project, $timesheet)
    {
        $json_fields = ['link', 'task', 'bug', 'added_by', 'task_list'];

        $timesheet['project_id'] = $project['id'];

        if (isset($timesheet['task']['is_sub_task']) && $timesheet['task']['is_sub_task']) {
            $timesheet['subtask_id']    = $timesheet['task']['id'] ?? null;
            $timesheet['subtask_name']  = $timesheet['task']['name'] ?? null;
            $timesheet['task_id']       = $timesheet['task']['root_task_id'] ?? null;
        } else {
            $timesheet['task_id']       = $timesheet['task']['id'] ?? null;
            $timesheet['task_name']     = $timesheet['task']['name'] ?? null;
        }

        if (isset($timesheet['created_date'])) {
            $timesheet['created_date'] = Carbon::createFromFormat('m-d-Y', $timesheet['created_date']);
        }

        if (isset($timesheet['last_modified_date'])) {
            $timesheet['last_modified_date'] = Carbon::createFromFormat('m-d-Y', $timesheet['last_modified_date']);
        }

        return $this->prepareJsonColumns($timesheet, $json_fields);
    }

    private function getTimeSheets($project, Request $request, $component_type)
    {
        $custom_date = ['start_date' => $request->start_date, 'end_date' => $request->end_date];
        $query_string = '?index=0&range=200&users_list=all&view_type=custom_date&date=' . $request->start_date;
        $query_string .= "&bill_status=All&component_type=$component_type&";
        $query_string .= 'custom_date=' . urlencode(json_encode($custom_date));

        $output = [];
        try {
            $time_sheet_api = $project['link']['timesheet']['url'] . $query_string;
            $response = Http::withToken($this->token)->get($time_sheet_api);
            if ($response->successful()) {
                $response_json = $response->json();
                $time_logs = $response_json['timelogs']['date'] ?? [];
                foreach ($time_logs as $time_log)
                {
                    $log_date       = Carbon::createFromFormat('m-d-Y', $time_log['date']);
                    $log_date_long  = $time_log['date_long'];
                    $logs = $component_type == 'task' ? $time_log['tasklogs'] : $time_log['buglogs'];
                    foreach ($logs as $log)
                    {
                        $log['log_date']        = $log_date;
                        $log['log_date_long']   = $log_date_long;
                        $log['type']            = $component_type;
                        $output[]               = $log;
                    }
                }
                return $output;
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    private function isJobInQueue($jobClassName)
    {
        $queue = DB::table(config('queue.connections.database.table'))->orderBy('id')->get();
        foreach ($queue as $job){
            $payload = json_decode($job->payload,true);
            if($payload['displayName'] == $jobClassName){
                return true;
            }
        }
        return false;
    }
}
