<?php

return [
    'head_office_branch' => env('SATURDAY_HEAD_OFFICE_BRANCH', 'Head Office'),
    'factory_branch' => env('SATURDAY_FACTORY_BRANCH', 'Factory'),
    'head_office_shift' => [
        'start' => '08:30',
        'end' => '16:30',
    ],
    'factory_shift' => [
        'start' => '08:30',
        'end' => '13:00',
    ],
];
