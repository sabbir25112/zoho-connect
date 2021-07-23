<?php

return [
    'url' => [
        'api_base' => 'https://projectsapi.zoho.com/restapi'
    ],
    'authentication' => [
        'url' => 'https://accounts.zoho.com/oauth/v2/auth',
        'auth_scopes' => [
            'ZohoProjects.portals.READ',
            'ZohoProjects.projects.READ',
            'ZohoProjects.tasklists.READ',
            'ZohoProjects.tasks.READ',
            'ZohoProjects.timesheets.READ',
        ],
    ],
];
