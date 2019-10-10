<?php
/** @noinspection PhpMissingStrictTypesDeclarationInspection */

/** @var string $_EXTKEY */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Cache optimizer test',
    'description' => 'Test records for the cacheopt Extension.',
    'category' => 'fe',
    'shy' => 0,
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => 0,
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Alexander Stehlik',
    'author_email' => 'astehlik.deleteme@intera.de',
    'author_company' => 'Intera GmbH',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => ['typo3' => '9.5.0-9.5.99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
