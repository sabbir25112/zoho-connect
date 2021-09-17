<?php

return [
    'url' => [
        'api_base' => 'https://projectsapi.zoho.com/restapi'
    ],
    'authentication' => [
        'url' => 'https://accounts.zoho.com/oauth/v2/auth',
        'refresh_token' => 'https://accounts.zoho.com/oauth/v2/token',
        'auth_scopes' => [
            'ZohoProjects.portals.READ',
            'ZohoProjects.projects.READ',
            'ZohoProjects.tasklists.READ',
            'ZohoProjects.tasks.READ',
            'ZohoProjects.timesheets.READ',
            'ZohoProjects.users.READ',
            'ZohoProjects.bugs.READ',
        ],
    ],
    'queue' => [
        'sleep_after_processing_a_project'  => 5,
        'max_request_per_min'               => 50,
        'sleep_after_max_request'           => 30,
    ]
];
