<?php
/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Cache optimizer',
    'description' => 'Optimizes automatic cache clearing.',
    'category' => 'be',
    'version' => '2.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Alexander Stehlik',
    'author_email' => 'astehlik.deleteme@intera.de',
    'author_company' => 'Intera GmbH',
    'constraints' => [
        'depends' => ['typo3' => '6.2.1-8.7.99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
