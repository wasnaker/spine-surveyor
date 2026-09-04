<?php

declare(strict_types=1);

/**
 * MANIFEST modul Surveyor.
 *
 * 2 entity lokal: Surveyor, Branch. NPWP (Vat) dipakai via module
 * spine-vat — dependensi via composer require wasnaker/spine-vat.
 *
 * @return array{menu: list<array{slug: string, label: string, icon: string, href: string, position: int, permission?: string}>, widgets: list<array{id: string, area: string, title: string, api: string}>, detail_tabs: list<array{slug: string, label: string, icon: string, api: string, position: int, permission?: string}>, rbac: array{permissions: list<string>, roles: list<array{name: string, label?: string, permissions: list<string>}>, grants: array<string, list<string>>}}
 */
return [
    'menu' => [
        [
            'slug'       => 'surveyors',
            'label'      => 'Surveyors',
            'icon'       => '👥',
            'href'       => '/surveyors',
            'position'   => 30,
            // Platform (surveyor:view) ATAU customer dgn koneksi (view-connected).
            'permission' => 'surveyor:view|surveyor:view-connected',
        ],
    ],

    'widgets' => [
        [
            'id'    => 'surveyors-items',
            'area'  => 'right-4',
            'title' => 'Surveyors',
            'api'   => '/api/v1/surveyors',
        ],
    ],

    'detail_tabs' => [
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/surveyors/{id}',
            'position'   => 10,
            'permission' => 'surveyor:view|surveyor:view-connected',
        ],
        [
            'slug'       => 'branches',
            'label'      => 'Branches',
            'icon'       => '🏢',
            'api'        => '/api/v1/surveyors/{id}/branches',
            'position'   => 20,
            'permission' => 'branch:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/surveyors/{id}/activity-logs',
            'position'   => 30,
            'permission' => 'surveyor:view|surveyor:view-connected',
        ],
    ],

    'rbac' => [
        'permissions' => [
            'surveyor:view', 'surveyor:create', 'surveyor:edit', 'surveyor:delete',
            'surveyor:view-connected',
            'customer:view-connected',
            'branch:view',   'branch:create',   'branch:edit',   'branch:delete',
        ],
        'roles' => [
            // Role BASE semua user entity surveyor: TANPA surveyor:view (menu
            // Surveyors di-block; data company via Profile /user/company).
            // customer:view-connected = menu Customers utk rekan terhubung.
            ['name' => 'surveyor',              'label' => 'Surveyor',
             'permissions' => ['connection:view', 'customer:view-connected', 'agency:surveyor-register', 'pengawas:view']],
            ['name' => 'surveyor-branch-admin', 'label' => 'Surveyor Branch Admin',
             'permissions' => ['connection:view', 'connection:create', 'connection:approve', 'connection:cancel',
                'customer:view-connected', 'agency:surveyor-register', 'pengawas:view']],
            ['name' => 'surveyor-admin',        'label' => 'Surveyor Admin',
             'permissions' => ['connection:view', 'connection:create', 'connection:approve', 'connection:cancel',
                'customer:view-connected', 'agency:surveyor-register', 'pengawas:view']],
        ],
        'grants' => [
            'staff' => ['surveyor:view', 'surveyor:view-connected', 'branch:view'],
        ],
    ],
];
