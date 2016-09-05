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
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * This cache optimizer hooks into the data handler to determine additional
 * pages for which the cache should be cleared.
 */
class CacheOptimizerDataHandler {

	/**
	 * @var CacheOptimizerRegistry
	 */
	protected $cacheOptimizerRegistry;

	/**
	 * The array of page UIDs for which the cache should be flushed in the current DataHandler run.
	 *
	 * @var array
	 */
	protected $currentPageIdArray;

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * @var ResourceFactory
	 */
	protected $resourceFactory;

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
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function dataHandlerClearPageCacheEval(
		array $parameters,
		/** @noinspection PhpUnusedParameterInspection */
		DataHandler $dataHandler
	) {
		$this->initialize();

		if ($parameters['functionID'] !== 'clear_cache()') {
			return;
		}

		$this->cacheOptimizerRegistry->registerPagesWithFlushedCache($parameters['pageIdArray']);

		$table = $parameters['table'];
		$uid = (int)$parameters['uid'];

		if ($this->cacheOptimizerRegistry->isProcessedRecord($table, $uid)) {
			return;
		}

		$this->cacheOptimizerRegistry->registerProcessedRecord($table, $uid);

		$this->currentPageIdArray =& $parameters['pageIdArray'];
		$this->registerRelatedPluginPagesForCacheFlush($table);
	}

	/**
	 * Returns a where statement that excludes all page UIDs (pid field)
	 * for which the cache is already flushed.
	 *
	 * @param bool $neverExcludeRoot If TRUE the TYPO3 root (pid = 0) will never be excluded.
	 * @return string
	 */
	protected function getPidExcludeStatement($neverExcludeRoot) {
		$flushedCachePids = $this->cacheOptimizerRegistry->getFlushedCachePageUids();
		if (count($flushedCachePids)) {
			$query = ' AND (pid not IN (' . implode(',', $flushedCachePids) . ')';
			$query .= $neverExcludeRoot ? ' OR pid=0)' : ')';
			return $query;
		} else {
			return '';
		}
	}

	/**
	 * Builds a where statement that selects all tt_content elements that
	 * have a content type or a plugin type that is related to the given table.
	 *
	 * @param string $table
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function getTtContentWhereStatementForTable($table) {
		$this->initialize();
		$whereStatement = '';

		$contentTypesForTable = $this->cacheOptimizerRegistry->getContentTypesForTable($table);
		if ($contentTypesForTable !== NULL) {
			$whereStatement .= ' OR (tt_content.CType IN ('
				. implode(
					',',
					$this->databaseConnection->fullQuoteArray($contentTypesForTable, 'tt_content')
				)
				. '))';
		}

		$pluginTypesForTable = $this->cacheOptimizerRegistry->getPluginTypesForTable($table);
		if ($pluginTypesForTable !== NULL) {
			$whereStatement .= ' OR (tt_content.CType=\'list\' AND tt_content.list_type IN ('
				. implode(
					',',
					$this->databaseConnection->fullQuoteArray($pluginTypesForTable, 'tt_content')
				)
				. '))';
		}

		if ($whereStatement !== '') {
			$whereStatement = ' AND ( (1=2) ' . $whereStatement . ')';
		}

		return $whereStatement;
	}

	/**
	 * Initializes required objects.
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	protected function initialize() {
		if ($this->databaseConnection !== NULL) {
			return;
		}
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
		$this->cacheOptimizerRegistry = CacheOptimizerRegistry::getInstance();
	}

	/**
	 * Checks if the cache for the given page was already flushed in the current
	 * run and if not flushCacheForPage() will be called in the parent class.
	 *
	 * @param int $pid
	 * @return void
	 */
	protected function registerPageForCacheFlush($pid) {
		if ($this->cacheOptimizerRegistry->pageCacheIsFlushed($pid)) {
			return;
		}
		$this->cacheOptimizerRegistry->registerPageWithFlushedCache($pid);
		$this->currentPageIdArray[] = (int)$pid;
	}

	/**
	 * Registers all pages for cache flush that contain contents related to records of the given table.
	 * Internal use, should be called by flushRelatedCacheForRecord() only!
	 *
	 * @param string $table
	 * @return void
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	protected function registerRelatedPluginPagesForCacheFlush($table) {
		$whereStatement = $this->getTtContentWhereStatementForTable($table);
		if ($whereStatement === '') {
			return;
		}
		$pageUidQuery = $this->databaseConnection->SELECTquery(
			'pid',
			'tt_content',
			'1=1' . $this->getPidExcludeStatement(FALSE) . $whereStatement,
			'pid'
		);
		$pageUidResult = $this->databaseConnection->sql_query($pageUidQuery);
		while ($pageUidRow = $this->databaseConnection->sql_fetch_assoc($pageUidResult)) {
			if (!is_array($pageUidRow)) {
				throw new \RuntimeException('Database error fechting related plugin pages.');
			}
			/** @var array $pageUidRow $pid */
			$pid = $pageUidRow['pid'];
			$this->registerPageForCacheFlush($pid);
		}
	}
}