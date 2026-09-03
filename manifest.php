<?php

declare(strict_types=1);

/**
 * MANIFEST modul Customer.
 *
 * 2 entity lokal: Customer, Branch. NPWP (Vat) dipakai via module
 * spine-vat — dependensi via composer require wasnaker/spine-vat.
 *
 * @return array{menu: list<array{slug: string, label: string, icon: string, href: string, position: int, permission?: string}>, widgets: list<array{id: string, area: string, title: string, api: string}>, detail_tabs: list<array{slug: string, label: string, icon: string, api: string, position: int, permission?: string}>, rbac: array{permissions: list<string>, roles: list<array{name: string, label?: string, permissions: list<string>}>, grants: array<string, list<string>>}}
 */
return [
    'menu' => [
        [
            'slug'       => 'customers',
            'label'      => 'Customers',
            'icon'       => '👥',
            'href'       => '/customers',
            'position'   => 30,
            'permission' => 'customer:view',
        ],
    ],

    'widgets' => [
        [
            'id'    => 'customers-items',
            'area'  => 'right-4',
            'title' => 'Customers',
            'api'   => '/api/v1/customers',
        ],
    ],

    'detail_tabs' => [
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/customers/{id}',
            'position'   => 10,
            'permission' => 'customer:view',
        ],
        [
            'slug'       => 'branches',
            'label'      => 'Branches',
            'icon'       => '🏢',
            'api'        => '/api/v1/customers/{id}/branches',
            'position'   => 20,
            'permission' => 'branch:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/customers/{id}/activity-logs',
            'position'   => 30,
            'permission' => 'customer:view',
        ],
    ],

    'rbac' => [
        'permissions' => [
            'customer:view', 'customer:create', 'customer:edit', 'customer:delete',
            'branch:view',   'branch:create',   'branch:edit',   'branch:delete',
        ],
        'roles' => [
            ['name' => 'customer',              'label' => 'Customer',
             'permissions' => ['customer:view']],
            ['name' => 'customer-branch-admin', 'label' => 'Customer Branch Admin',
             'permissions' => ['customer:view', 'branch:*']],
            ['name' => 'customer-admin',        'label' => 'Customer Admin',
             'permissions' => ['customer:*', 'branch:*']],
        ],
        'grants' => [
            'staff' => ['customer:view', 'branch:view'],
        ],
    ],
];
