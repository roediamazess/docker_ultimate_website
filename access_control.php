<?php
// Mapping hak akses granular per role, modul, dan aksi
// Format: $access_map[role][modul][aksi] = true/false
$access_map = [
    'Administrator' => [
        'user' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>true],
        'customer' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>true],
        'project' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>true],
        'activity' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>true],
        'log' => ['read'=>true],
    ],
    'Management' => [
        'user' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'customer' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'project' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'activity' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'log' => ['read'=>true],
    ],
    'Admin Office' => [
        'user' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'customer' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>false],
        'project' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>false],
        'activity' => ['create'=>true,'read'=>true,'update'=>true,'delete'=>false],
        'log' => ['read'=>false],
    ],
    'User' => [
        'user' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'customer' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'project' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'activity' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'log' => ['read'=>false],
    ],
    'Client' => [
        'user' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'customer' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'project' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'activity' => ['create'=>false,'read'=>true,'update'=>false,'delete'=>false],
        'log' => ['read'=>false],
    ],
];

function has_access($role, $module, $action) {
    global $access_map;
    return !empty($access_map[$role][$module][$action]);
}
