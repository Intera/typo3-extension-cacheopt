<?php
/** @noinspection PhpMissingStrictTypesDeclarationInspection */

/** @var string $_EXTKEY */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Cache optimizer',
    'description' => 'Optimizes automatic cache clearing.',
    'category' => 'be',
    'version' => '10.0.0-dev',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Alexander Stehlik',
    'author_email' => 'astehlik.deleteme@intera.de',
    'author_company' => 'Intera GmbH',
    'constraints' => [
        'depends' => ['typo3' => '10.0.0-10.4.99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
