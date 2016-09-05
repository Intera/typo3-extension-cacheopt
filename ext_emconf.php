<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "cacheopt".
 *
 * Auto generated 24-10-2014 13:53
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Cache optimizer',
    'description' => 'Optimizes automatic cache clearing.',
    'category' => 'be',
    'version' => '2.0.0-dev',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Alexander Stehlik',
    'author_email' => 'astehlik.deleteme@intera.de',
    'author_company' => 'Intera GmbH',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '6.2.1-7.99.99',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ],
];

