<?php
declare(strict_types=1);

return [
    'ctrl' => [
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'title' => 'Cacheopt record',
        'delete' => 'deleted',
        'searchFields' => 'title',
    ],
    'interface' => [
        'always_description' => 0,
        'showRecordFieldList' => 'title',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
                'eval' => 'required',
                'size' => '50',
                'max' => '256',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title'],
    ],
    'palettes' => [],
];
