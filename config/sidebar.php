<?php return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Navigation
    |--------------------------------------------------------------------------
    */
    [
        'title' => 'Dashboard',
        'items' => [
            [
                'label' => 'Main',
                'route' => 'dashboard',
                'roles' => [
                    'Member',
                    'President',
                    'Vice President',
                    'Secretary',
                    'Treasurer',
                    'Auditor',
                ],
            ],
        ],
    ],
    [
        'title' => 'Claims',
        'items' => [
            [
                'label' => 'Submit Claim',
                // 'route' => 'claims.create',
                'route' => 'dashboard',
                'roles' => ['Member'],
            ],
            [
                'label' => 'My Claims',
                // 'route' => 'claims.my',
                'route' => 'dashboard',
                'roles' => ['Member'],
            ],
            [
                'label' => 'Balance',
                // 'route' => 'balance.index',
                'route' => 'dashboard',
                'roles' => ['Member'],
            ],
            [
                'label' => 'All Claims',
                // 'route' => 'claims.index',
                'route' => 'dashboard',
                'roles' => [
                    'President',
                    'Treasurer',
                    'Secretary',
                    'Auditor',
                ],
            ],
            [
                'label' => 'Disbursement',
                // 'route' => 'disbursement.index',
                'route' => 'dashboard',
                'roles' => [
                    'Treasurer',
                ],
            ],
        ],
    ],
    [
        'title' => 'Reports',
        'items' => [
            [
                'label' => 'My Reports',
                // 'route' => 'reports.my',
                'route' => 'dashboard',
                'roles' => [
                    'Member',
                ],
            ],
            [
                'label' => 'Reports',
                // 'route' => 'reports.index',
                'route' => 'dashboard',
                'roles' => [
                    'President',
                    'Treasurer',
                    'Secretary',
                    'Auditor',
                ],
            ],
        ],
    ],
    [
        'title' => 'Management',
        'items' => [
            [
                'label' => 'Members',
                // 'route' => 'members.index',
                'route' => 'dashboard',
                'roles' => [
                    'President',
                    'Treasurer',
                ],
            ],
            [
                'label' => 'Users',
                'route' => 'users.index',
                'roles' => [
                    'President',
                    'Treasurer',
                    'Finance Partner',
                    'Campus President',
                ],
            ],
            [
                'label' => 'Settings',
                'route' => 'settings.index',
                'roles' => [
                    'President',
                    'Treasurer',
                    'Finance Partner',
                    'Campus President',
                ],
            ],
        ],
    ],
];
