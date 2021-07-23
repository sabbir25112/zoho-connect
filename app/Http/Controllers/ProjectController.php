<?php namespace App\Http\Controllers;

use App\Http\Constants;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    private function getPortals()
    {
        // return json_decode('[{"storage_type":"ZOHO_DOCS","trial_enabled":false,"can_create_project":true,"gmt_time_zone":"(GMT 10:0) Eastern Standard Time (New South Wales)","project_count":{"active":2},"role":"Employee","is_sprints_integrated":false,"avail_user_count":-1,"is_crm_partner":false,"link":{"project":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/"}},"bug_plan":"Enterprise","can_add_template":true,"locale":{"country":"United States","code":"en_US","language":"English"},"IS_LOGHR_RESTRICTEDBY_WORKHR":false,"layouts":{"projects":{"module_id":"685798000007722005"},"tasks":{"module_id":"685798000006116005"}},"gmt_time_zone_offset":36000000,"new_user_plan":false,"available_projects":-1,"default":false,"id":36249008,"bug_plural":"Bugs","is_new_plan":false,"plan":"Enterprise","percentage_calculation":"based_on_status","settings":{"business_hours":{"business_end":1020,"business_start":540},"street_address":"Suite 4, Level 2, 22 George Street","country":"Australia","default_dependency_type":"finish-start","working_days":["Monday","Tuesday","Wednesday","Thursday","Friday"],"city":"North Strathfield","timelog_period":{"isEditLogRestricted":true,"log_future_time":{"allowed":false},"log_past_time":{"customize":{"unit":"day(s)","value":"1"}}},"task_duration_type":"hours","time_zone":"Australia\/NSW","startday_of_week":"monday","task_date_format":"dd\/MM\/yyyy hh:mm aaa","timesheet":{"default_billing_status":"Billable","is_timesheet_approval_enabled":true},"website_url":"www.qrsolutions.com.au","holidays":[{"date":"04-18-2014","name":"Good Friday","id":"685798000000060079"},{"date":"04-21-2014","name":"Easter Monday","id":"685798000000060081"},{"date":"04-25-2014","name":"Anzac Day","id":"685798000000060083"},{"date":"06-09-2014","name":"Queens Birthday","id":"685798000000060085"},{"date":"09-29-2014","name":"Family & Community Day","id":"685798000000060087"},{"date":"10-06-2014","name":"Labour Day","id":"685798000000060089"},{"date":"12-25-2014","name":"Christmas Day","id":"685798000000060091"},{"date":"12-26-2014","name":"Boxing Day","id":"685798000000060093"},{"date":"01-01-2015","name":"New Year Day","id":"685798000002672033"},{"date":"01-26-2015","name":"Australia Day","id":"685798000002672035"},{"date":"04-03-2015","name":"Good Friday","id":"685798000002672037"},{"date":"04-06-2015","name":"Easter Monday","id":"685798000002672039"},{"date":"06-08-2015","name":"Queens Birthday","id":"685798000002672041"},{"date":"08-03-2015","name":"Bank Holiday","id":"685798000002672043"},{"date":"10-05-2015","name":"Labour Day","id":"685798000002672045"},{"date":"12-25-2015","name":"Christmas Day","id":"685798000002672047"}],"is_budget_enabled":false,"company_name":"QR Solutions","date_format":"dd\/MM\/yyyy hh:mm aaa","state":"NSW","postal_code":"2137","has_budget_permission":true},"avail_client_user_count":0,"is_tags_available":true,"sprints_project_permission":false,"is_display_taskprefix":true,"bug_singular":"Bug","login_zpuid":685798000014575503,"is_display_projectprefix":true,"project_prefix":"PR-","max_user_count":-1,"max_client_user_count":-1,"extensions":{"locations":{"task_transition":"685798000012231001","taskdetails_rightpanel":"685798000009601091","app_settings":"685798000008013009","issuedetails_rightpanel":"685798000009601093","issue_tab":"685798000008013013","task_tab":"685798000008013011","attachment_picker":"685798000008880073","top_band":"685798000008776003","blueprint_during":"685798000012231003","project_tab":"685798000008776001"}},"profile_id":685798000005970143,"name":"qrsolutions","id_string":"36249008","is_time_log_restriction_enabled":false,"integrations":{"people":{"is_enabled":false},"meeting":{"is_enabled":false}}}]', true);
        try {
            $api_base_url   = config('zoho.url.api_base');
            $portal_api_uri = $api_base_url . '/portals/';
            $token = Cache::get(Constants::ZOHO_ACCESS_KEY_CACHE);
            $response = Http::withToken($token)->get($portal_api_uri);
            if ($response->successful()) {
                return $response->json()['portals'];
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function sync()
    {
        $startTime = time();
        $portals = $this->getPortals();
        foreach ($portals as $portal)
        {
            $projects = $this->getProjects($portal);
            $this->syncProjects($projects);
            foreach ($projects as $project)
            {
                $taskLists = $this->getTaskList($project);
                $this->syncTaskList($taskLists);
                foreach ($taskLists as $taskList)
                {
                    $tasks = $this->getTask($taskList);
                    $this->syncTask($tasks);
                }
            }
        }
        dd(time() - $startTime);
    }

    private function getProjects($portal)
    {
        // return json_decode('[{"is_strict":"no","project_percent":"45","role":"Employee","bug_count":{"closed":1,"open":1},"IS_BUG_ENABLED":true,"owner_id":"680368510","bug_client_permission":"allexternal","taskbug_prefix":"AP2","link":{"activity":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/activities\/"},"document":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/documents\/"},"forum":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/forums\/"},"timesheet":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/logs\/"},"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasks\/"},"folder":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/folders\/"},"milestone":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/milestones\/"},"bug":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/bugs\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/"},"tasklist":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/"},"event":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/events\/"},"user":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/users\/"},"status":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/statuses\/"}},"custom_status_id":"685798000007722089","description":"<div><br><\/div>","milestone_count":{"closed":0,"open":0},"updated_date_long":1626955295714,"show_project_overview":false,"task_count":{"closed":58,"open":69},"updated_date_format":"07-22-2021 10:01:35 PM","workspace_id":"49oz6c24e52a06f6b4ba5a8d0a791c889c90c","custom_status_name":"Active","owner_zpuid":"685798000008180077","is_client_assign_bug":"false","bug_defaultview":"6","billing_status":"Billable","id":685798000011352647,"key":"PR-425","is_chat_enabled":true,"is_sprints_project":false,"custom_status_color":"#2cc8ba","owner_name":"Venkatraman","created_date_long":1598237721540,"group_name":"QR SOLUTIONS","created_date_format":"08-24-2020 12:55:21 PM","group_id":685798000000107049,"profile_id":685798000005970143,"enabled_tabs":["dashboard","projectfeed","tasks","bugs","milestones","calendar","documents","timesheet","invoices","forums","pages","chat","users","reports"],"name":"appsupport.io","is_public":"no","id_string":"685798000011352647","created_date":"08-24-2020","updated_date":"07-22-2021","bug_prefix":"AP2-I","cascade_setting":{"date":false,"logHours":false,"plan":true,"percentage":false,"workHours":false},"layout_details":{"task":{"name":"Standard Layout","id":"685798000006116011"},"bug":{"name":"Standard Layout","id":"685798000008839003"},"project":{"name":"Standard Layout","id":"685798000007722008"}},"status":"active"},{"is_strict":"no","project_percent":"33","role":"Employee","bug_count":{"closed":0,"open":0},"IS_BUG_ENABLED":true,"owner_id":"32229111","bug_client_permission":"allexternal","taskbug_prefix":"Q-1","link":{"activity":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/activities\/"},"document":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/documents\/"},"forum":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/forums\/"},"timesheet":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/logs\/"},"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/tasks\/"},"folder":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/folders\/"},"milestone":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/milestones\/"},"bug":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/bugs\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/"},"tasklist":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/tasklists\/"},"event":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/events\/"},"user":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/users\/"},"status":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000010285053\/statuses\/"}},"custom_status_id":"685798000007722089","description":"<div>AppGateway Development for Finance Application<br><\/div><div>Hosting Applications on AWS and Managed Services&nbsp;<br><\/div><div>AppRetail extension to Finance Application&nbsp;<br><\/div><div>..Others&nbsp;<br><\/div><div><br><\/div><div>Technology Partnership in General<br><\/div>","milestone_count":{"closed":0,"open":0},"start_date_long":1585746000000,"updated_date_long":1626955295711,"show_project_overview":false,"task_count":{"closed":1,"open":2},"updated_date_format":"07-22-2021 10:01:35 PM","workspace_id":"83yijb8ea25d7189a475bbc5e7618384e6ebd","custom_status_name":"Active","owner_zpuid":"685798000007490017","is_client_assign_bug":"false","bug_defaultview":"6","billing_status":"Non Billable","id":685798000010285053,"key":"PR-414","is_chat_enabled":true,"start_date":"04-02-2020","is_sprints_project":false,"custom_status_color":"#2cc8ba","owner_name":"Murthy Vaidheeswaran","created_date_long":1586155623637,"group_name":"QR SOLUTIONS","created_date_format":"04-06-2020 04:47:03 PM","group_id":685798000000107049,"profile_id":685798000005970143,"enabled_tabs":["tasks","bugs","milestones","dashboard","projectfeed","calendar","documents","timesheet","invoices","forums","pages","chat","users","reports"],"name":"QRS - Habile - Ebiz","is_public":"no","id_string":"685798000010285053","created_date":"04-06-2020","updated_date":"07-22-2021","bug_prefix":"Q-1-I","cascade_setting":{"date":false,"logHours":false,"plan":true,"percentage":false,"workHours":false},"layout_details":{"task":{"name":"Standard Layout","id":"685798000006116011"},"bug":{"name":"Standard Layout","id":"685798000008839003"},"project":{"name":"Standard Layout","id":"685798000007722008"}},"status":"active"}]', true);
        try {
            $projects_api = $portal['link']['project']['url'];
            $token = Cache::get(Constants::ZOHO_ACCESS_KEY_CACHE);
            $response = Http::withToken($token)->get($projects_api);
            if ($response->successful()) {
                return $response->json()['projects'];
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    private function syncProjects($projects)
    {
        // Sync projects DB
    }

    private function getTaskList($project)
    {
        // return json_decode('[{"created_time_long":1599126574243,"created_time":"09-03-2020","flag":"internal","created_time_format":"09-03-2020 07:49:34 PM","link":{"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471075\/tasks\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471075\/"}},"completed":false,"rolled":false,"task_count":{"open":26},"sequence":5,"milestone":{"name":"None","id":685798000000000073},"last_updated_time":"01-19-2021","last_updated_time_long":1611048991753,"name":"4th Milestone","id_string":"685798000011471075","id":685798000011471075,"last_updated_time_format":"01-19-2021 08:36:31 PM"},{"created_time_long":1599126567381,"created_time":"09-03-2020","flag":"internal","created_time_format":"09-03-2020 07:49:27 PM","link":{"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471071\/tasks\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471071\/"}},"completed":false,"rolled":false,"task_count":{"closed":6,"open":15},"sequence":4,"milestone":{"name":"None","id":685798000000000073},"last_updated_time":"12-03-2020","last_updated_time_long":1606991387255,"name":"3rd Milestone","id_string":"685798000011471071","id":685798000011471071,"last_updated_time_format":"12-03-2020 09:29:47 PM"},{"created_time_long":1599126558721,"created_time":"09-03-2020","flag":"internal","created_time_format":"09-03-2020 07:49:18 PM","link":{"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471067\/tasks\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471067\/"}},"completed":false,"rolled":false,"task_count":{"closed":7,"open":9},"sequence":3,"milestone":{"name":"None","id":685798000000000073},"last_updated_time":"11-12-2020","last_updated_time_long":1605170103808,"name":"2nd Milestone","id_string":"685798000011471067","id":685798000011471067,"last_updated_time_format":"11-12-2020 07:35:03 PM"},{"created_time_long":1599126550955,"created_time":"09-03-2020","flag":"internal","created_time_format":"09-03-2020 07:49:10 PM","link":{"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471063\/tasks\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011471063\/"}},"completed":false,"rolled":false,"task_count":{"closed":2,"open":8},"sequence":2,"milestone":{"name":"None","id":685798000000000073},"last_updated_time":"10-28-2020","last_updated_time_long":1603859447852,"name":"1st Milestone","id_string":"685798000011471063","id":685798000011471063,"last_updated_time_format":"10-28-2020 03:30:47 PM"},{"created_time_long":1598587181833,"created_time":"08-28-2020","flag":"internal","created_time_format":"08-28-2020 01:59:41 PM","link":{"task":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011410057\/tasks\/"},"self":{"url":"https:\/\/projectsapi.zoho.com\/restapi\/portal\/36249008\/projects\/685798000011352647\/tasklists\/685798000011410057\/"}},"completed":false,"rolled":false,"task_count":{"closed":43,"open":11},"sequence":1,"milestone":{"name":"None","id":685798000000000073},"last_updated_time":"06-23-2021","last_updated_time_long":1624409301865,"name":"General","id_string":"685798000011410057","id":685798000011410057,"last_updated_time_format":"06-23-2021 10:48:21 AM"}]', true);
        try {
            $taskList_api = $project['link']['tasklist']['url'];
            $token = Cache::get(Constants::ZOHO_ACCESS_KEY_CACHE);
            $response = Http::withToken($token)->get($taskList_api, [
                'flag' => 'allflag',
            ]);
            if ($response->successful()) {
                return $response->json()['tasklists'];
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    private function syncTaskList($taskLists)
    {
        // sync tasklists table
    }

    private function getTask($taskList)
    {
        try {
            $task_api = $taskList['link']['task']['url'];
            $token = Cache::get(Constants::ZOHO_ACCESS_KEY_CACHE);
            $response = Http::withToken($token)->get($task_api);
            if ($response->successful()) {
                return $response->json()['tasks'];
            }
            return [];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    private function syncTask($tasks)
    {
        // sync tasks table
    }
}
