<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Hook into the data handler to clear the cache for related records.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] = 'Tx\\Cacheopt\\CacheOptimizerDataHandler->dataHandlerClearPageCacheEval';

// Hook into the file handling to clear the cache for related records.
/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileMove, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileMovePost');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileDelete, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileDeletePost');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileAdd, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileAddPost');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileCreate, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileCreatePost');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileCopy, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileCopyPost');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileSetContents, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileSetContentsPost');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PostFileRename, 'Tx\\Cacheopt\\CacheOptimizerFiles', 'handleFileRenamePost');

$cacheOptimizerRegistry = \Tx\Cacheopt\CacheOptimizerRegistry::getInstance();

// Default configuration for the news Extension.
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('news')) {

	// Since news has its own cache handling we exclude the news records from further processing.
	$cacheOptimizerRegistry->registerExcludedTable('tx_news_domain_model_news');
	$cacheOptimizerRegistry->registerExcludedTable('tx_news_domain_model_tag');
}

// Default configuration for the cz_simple_cal Extension.
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('cz_simple_cal')) {

	$cacheOptimizerRegistry->registerPluginForTables(
		array(
			'tx_czsimplecal_domain_model_address',
			'tx_czsimplecal_domain_model_category',
			'tx_czsimplecal_domain_model_event',
		),
		'czsimplecal_pi1'
	);

	// We do not want refindex traversal for the event index table.
	$cacheOptimizerRegistry->registerExcludedTable('tx_czsimplecal_domain_model_eventindex');
}

unset($cacheOptimizerRegistry);