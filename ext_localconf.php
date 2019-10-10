<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpMissingStrictTypesDeclarationInspection */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Hook into the data handler to clear the cache for related records.
// Make sure we are the first processor so that other processors handle the pages we added.
if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'])
    && is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'])
) {
    array_unshift(
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'],
        Tx\Cacheopt\CacheOptimizerDataHandler::class . '->dataHandlerClearPageCacheEval'
    );
} else {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] =
        Tx\Cacheopt\CacheOptimizerDataHandler::class . '->dataHandlerClearPageCacheEval';
}

if (TYPO3_MODE === 'FE' || defined('TX_CACHEOPT_FUNCTIONAL_TEST')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit']['tx_cacheopt']
        = Tx\Cacheopt\TagCollector\ContentTagCollector::class;

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
    );

    // Hook in every time a public URL is requested to collect file related cache tags.
    $signalSlotDispatcher->connect(
        TYPO3\CMS\Core\Resource\ResourceStorage::class,
        TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreGeneratePublicUrl,
        Tx\Cacheopt\TagCollector\FileTagCollector::class,
        'collectTagsForPreGeneratePublicUrl'
    );
}
// Hook into the file handling to clear the cache for related records.
/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileMove,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileMovePost'
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileDelete,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileDeletePost'
);
$signalSlotDispatcher->connect(
    'TYPO3\\CMS\\Core\\Resource\\ResourceStorage',
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileAdd,
    'Tx\\Cacheopt\\CacheOptimizerFiles',
    'handleFileAddPost'
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileCreate,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileCreatePost'
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileCopy,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileCopyPost'
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileSetContents,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileSetContentsPost'
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileRename,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileRenamePost'
);
$signalSlotDispatcher->connect(
    TYPO3\CMS\Core\Resource\ResourceStorage::class,
    TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileReplace,
    Tx\Cacheopt\CacheOptimizerFiles::class,
    'handleFileReplacePost'
);

$cacheOptimizerRegistry = Tx\Cacheopt\CacheOptimizerRegistry::getInstance();

// Default configuration for the cz_simple_cal Extension.
if (TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('cz_simple_cal')) {
    $cacheOptimizerRegistry->registerPluginForTables(
        [
            'tx_czsimplecal_domain_model_address',
            'tx_czsimplecal_domain_model_category',
            'tx_czsimplecal_domain_model_event',
        ],
        'czsimplecal_pi1'
    );
}

unset($cacheOptimizerRegistry);
