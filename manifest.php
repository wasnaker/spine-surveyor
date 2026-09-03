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
            'permission' => 'surveyor:view',
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
            'permission' => 'surveyor:view',
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
            'permission' => 'surveyor:view',
        ],
    ],

    'rbac' => [
        'permissions' => [
            'surveyor:view', 'surveyor:create', 'surveyor:edit', 'surveyor:delete',
            'branch:view',   'branch:create',   'branch:edit',   'branch:delete',
        ],
        'roles' => [
            ['name' => 'surveyor',              'label' => 'Surveyor',
             'permissions' => ['surveyor:view']],
            ['name' => 'surveyor-branch-admin', 'label' => 'Surveyor Branch Admin',
             'permissions' => ['surveyor:view', 'branch:*']],
            ['name' => 'surveyor-admin',        'label' => 'Surveyor Admin',
             'permissions' => ['surveyor:*', 'branch:*']],
        ],
        'grants' => [
            'staff' => ['surveyor:view', 'branch:view'],
        ],
    ],
];
