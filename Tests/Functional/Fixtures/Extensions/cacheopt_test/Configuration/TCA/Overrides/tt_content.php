<?php
declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

ExtensionUtility::registerPlugin(
    'Tx.CacheoptTest',
    'RecordRenderPlugin',
    'Cacheopt - Record renderer plugin'
);

ExtensionUtility::registerPlugin(
    'Tx.CacheoptTest',
    'RecordRenderContent',
    'Cacheopt - Record renderer content'
);
