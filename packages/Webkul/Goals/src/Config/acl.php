<?php

return [
    [
        'key'   => 'goals',
        'name'  => 'admin::app.acl.goals',
        'route' => 'admin.goals.index',
        'sort'  => 2,
    ],
    [
        'key'   => 'goals.create',
        'name'  => 'admin::app.acl.create',
        'route' => 'admin.goals.create',
        'sort'  => 2,
    ],
    [
        'key'   => 'goals.edit',
        'name'  => 'admin::app.acl.edit',
        'route' => 'admin.goals.edit',
        'sort'  => 3,
    ],
    [
        'key'   => 'goals.delete',
        'name'  => 'admin::app.acl.delete',
        'route' => 'admin.goals.delete',
        'sort'  => 4,
    ],
];
