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

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * This cache optimizer hooks into the data handler to determine additional
 * pages for which the cache should be cleared.
 */
class CacheOptimizerDataHandler extends AbstractCacheOptimizer {

	/**
	 * The array of page UIDs for which the cache should be flushed in the current DataHandler run.
	 *
	 * @var array
	 */
	protected $currentPageIdArray;

	/**
	 * Is called by the data handler within the processClearCacheQueue() method and
	 * adds related records to the cache clearing queue.
	 *
	 * @param array $parameters Parameters array containing:
	 * pageIdArray => reference to indexed array containing the records for which the cache should be cleared
	 * table => the name of the table of the current record
	 * uid =>  the uid of the record
	 * functionID => is always clear_cache()
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
	 */
	public function dataHandlerClearPageCacheEval(
			array $parameters,
			/** @noinspection PhpUnusedParameterInspection */
			DataHandler $dataHandler
	) {
		$this->initialize();

		$this->cacheOptimizerRegistry->registerPagesWithFlushedCache($parameters['pageIdArray']);

		$table = $parameters['table'];
		$uid = (int)$parameters['uid'];

		if ($this->cacheOptimizerRegistry->isProcessedRecord($table, $uid)) {
			return;
		}

		$this->currentPageIdArray =& $parameters['pageIdArray'];
		$this->flushRelatedCacheForRecord($table, $uid);
	}

	/**
	 * Adds the pid to the array of PIDs for which the cache should be flushed.
	 *
	 * @param int $pid
	 */
	protected function flushCacheForPage($pid) {
		$this->currentPageIdArray[] = (int)$pid;
	}
}