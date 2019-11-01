<?php
/** @noinspection PhpMissingStrictTypesDeclarationInspection */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/** @uses \Tx\CacheoptTest\Controller\RecordController::displayAction() */
TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Tx.CacheoptTest',
    'RecordRenderPlugin',
    ['Record' => 'display']
);

/** @uses \Tx\CacheoptTest\Controller\RecordController::displayAction() */
TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Tx.CacheoptTest',
    'RecordRenderContent',
    ['Record' => 'display'],
    [],
    TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

Tx\Cacheopt\CacheOptimizerRegistry::getInstance()->registerContentForTable(
    'tx_cacheopttest_domain_model_record',
    'cacheopttest_recordrendercontent'
);
Tx\Cacheopt\CacheOptimizerRegistry::getInstance()->registerPluginForTable(
    'tx_cacheopttest_domain_model_record',
    'cacheopttest_recordrenderplugin'
);
