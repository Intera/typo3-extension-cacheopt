<?php
namespace Tx\Cacheopt;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "cacheopt".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * API methods that can be used by extensions.
 */
class CacheApi implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Core\Cache\CacheManager
	 */
	protected $cacheManager;

	/**
	 * Flushes the cache for the given page.
	 *
	 * @param int $pageId
	 * @param bool $useDataHandler If this is true the DataHandler will be used
	 * instead of the CacheManager for cache clearing. This makes sure that the
	 * hooks registered for clearPageCacheEval are called (e.g. those of realurl).
	 * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
	 * @throws \InvalidArgumentException
	 */
	public function flushCacheForPage($pageId, $useDataHandler) {

		if ($useDataHandler) {
			$this->flushCacheForRecordWithDataHandler('pages', $pageId);
		} else {
			$this->initializeCacheManager();
			$this->cacheManager->flushCachesInGroupByTag('pages', 'pageId_' . $pageId);
		}
	}

	/**
	 * Initializes an instance of the DataHandler, registers the given record for
	 * cache clearing and starts the cache clearing process of the DataHandler.
	 *
	 * This process makes sure that the hooks registered for clearPageCacheEval
	 * are called (e.g. those of cacheopt or those of realurl).
	 *
	 * @param string $tablename
	 * @param int $uid
	 * @throws \InvalidArgumentException
	 */
	public function flushCacheForRecordWithDataHandler($tablename, $uid) {
		$tce = GeneralUtility::makeInstance(DataHandler::class);
		$tce->stripslashes_values = 0;
		$tce->start(array(), array());
		/** @noinspection PhpInternalEntityUsedInspection Since the clear_cache() method is deprecated we need
		 * to used this internal method. */
		$tce->registerRecordIdForPageCacheClearing($tablename, $uid);
		$tce->process_datamap();
	}

	/**
	 * Loads an instance of the cache manager in the cacheManager class variable.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function initializeCacheManager() {
		if ($this->cacheManager === NULL) {
			$this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
		}
	}
}