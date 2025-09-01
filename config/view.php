<?php

return [
    // Directories where Blade views are stored
    'paths' => [
        resource_path('views'),
    ],

    // Directory for compiled Blade templates
    'compiled' => realpath(storage_path('framework/views')),
];
