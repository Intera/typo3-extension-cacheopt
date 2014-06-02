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