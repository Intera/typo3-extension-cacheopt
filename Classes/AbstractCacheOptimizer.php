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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * This class provides utility methods for all cache optimizers.
 */
abstract class AbstractCacheOptimizer {

	const MAX_RECURSE_DEPTH = 1;

	/**
	 * @var CacheOptimizerRegistry
	 */
	protected $cacheOptimizerRegistry;

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 */
	protected $resourceFactory;

	/**
	 * Flushes the cache for the page with the given UID.
	 *
	 * This should never be called directly! Call registerPageForCacheFlush() instead
	 * which makes sure that the cache for this page has not been flushed before.
	 *
	 * @param int $pid
	 * @return void
	 */
	abstract protected function flushCacheForPage($pid);

	/**
	 * Builds a where statement that selects all tt_content elements that
	 * have a content type or a plugin type that is related to the given table.
	 *
	 * @param string $table
	 * @return string
	 */
	public function getTtContentWhereStatementForTable($table) {
		$this->initialize();
		$whereStatement = '';

		$contentTypesForTable = $this->cacheOptimizerRegistry->getContentTypesForTable($table);
		if (isset($contentTypesForTable)) {
			$whereStatement .= ' OR (tt_content.CType IN (' . implode(',', $this->databaseConnection->fullQuoteArray($contentTypesForTable, 'tt_content')) . '))';
		}

		$pluginTypesForTable = $this->cacheOptimizerRegistry->getPluginTypesForTable($table);
		if (isset($pluginTypesForTable)) {
			$whereStatement .= ' OR (tt_content.CType=\'list\' AND tt_content.list_type IN (' . implode(',', $this->databaseConnection->fullQuoteArray($pluginTypesForTable, 'tt_content')) . '))';
		}

		if ($whereStatement !== '') {
			$whereStatement = ' AND ( (1=2) ' . $whereStatement . ')';
		}

		return $whereStatement;
	}

	/**
	 * Searches for all records pointing to the given folder and flushes
	 * the related page caches.
	 *
	 * @param int $storageUid
	 * @param string $folderIdentifier
	 * @return void
	 */
	protected function flushCacheForRelatedFolders($storageUid, $folderIdentifier) {
		if ($this->cacheOptimizerRegistry->isProcessedFolder($storageUid, $folderIdentifier)) {
			return;
		}
		$this->cacheOptimizerRegistry->registerProcessedFolder($storageUid, $folderIdentifier);
		$fileCollectionResult = $this->databaseConnection->exec_SELECTquery('uid', 'sys_file_collection', "deleted=0 AND type='folder' AND storage=" . (int)$storageUid . " AND folder=" . $this->databaseConnection->fullQuoteStr($folderIdentifier, 'sys_file_collection'));
		while ($fileCollectionRow = $this->databaseConnection->sql_fetch_assoc($fileCollectionResult)) {
			$this->flushRelatedCacheForRecord('sys_file_collection', (int)$fileCollectionRow['uid'], self::MAX_RECURSE_DEPTH);
		}
	}

	/**
	 * Searches for all relations of the given record in the refindex and
	 * clears the cache for the pages containing these records.
	 *
	 * @param string $table
	 * @param int $uid
	 * @param int $depth
	 * @return void
	 */
	protected function flushRelatedCacheForRecord($table, $uid, $depth = 0) {

		if ($this->cacheOptimizerRegistry->isProcessedRecord($table, $uid)) {
			return;
		}

		$this->cacheOptimizerRegistry->registerProcessedRecord($table, $uid);

		$referenceResult = $this->databaseConnection->exec_SELECTquery('tablename,recuid', 'sys_refindex', "ref_table!='_STRING' AND flexpointer='' and softref_key='' AND ref_table=" . $this->databaseConnection->fullQuoteStr($table, 'sys_refindex') . " AND ref_uid=" . (int)$uid, '', '', '', 'hash');
		while ($referenceRow = $this->databaseConnection->sql_fetch_assoc($referenceResult)) {
			$this->registerSingleRecordRecursiveForCacheFlush($referenceRow['tablename'], (int)$referenceRow['recuid'], $depth);
		}

		$referenceResult = $this->databaseConnection->exec_SELECTquery('ref_table,ref_uid', 'sys_refindex', "ref_table!='_STRING' AND flexpointer='' and softref_key='' AND tablename=" . $this->databaseConnection->fullQuoteStr($table, 'sys_refindex') . " AND recuid=" . (int)$uid, '', '', '', 'hash');
		while ($referenceRow = $this->databaseConnection->sql_fetch_assoc($referenceResult)) {
			$this->registerSingleRecordRecursiveForCacheFlush($referenceRow['ref_table'], (int)$referenceRow['ref_uid'], $depth);
		}

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
	 * Initializes required objects.
	 *
	 * @return void
	 */
	protected function initialize() {
		if (isset($this->databaseConnection)) {
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
		$this->flushCacheForPage($pid);
	}

	/**
	 * The parent folder of the given file will be retrieved and the cache
	 * for all file collections pointing to this folder will be flushed.
	 *
	 * @param int $fileUid
	 * @return void
	 */
	protected function registerRelatedFolderFileCollectionsForCacheFlush($fileUid) {
		try {
			$file = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileObject($fileUid);
			$folderIdentifier = $file->getParentFolder()->getIdentifier();
			$this->flushCacheForRelatedFolders($file->getStorage()->getUid(), $folderIdentifier);
		} catch (\Exception $e) {

		}
	}

	/**
	 * Registers all pages for cache flush that contain contents related to records of the given table.
	 * Internal use, should be called by flushRelatedCacheForRecord() only!
	 *
	 * @param string $table
	 * @return void
	 */
	protected function registerRelatedPluginPagesForCacheFlush($table) {
		$whereStatement = $this->getTtContentWhereStatementForTable($table);
		if ($whereStatement === '') {
			return;
		}
		$pageUidQuery = $this->databaseConnection->SELECTquery('pid', 'tt_content', '1=1' . $this->getPidExcludeStatement(FALSE) . $whereStatement, 'pid');
		$pageUidResult = $this->databaseConnection->sql_query($pageUidQuery);
		while ($pageUidRow = $this->databaseConnection->sql_fetch_assoc($pageUidResult)) {
			$pid = $pageUidRow['pid'];
			$this->registerPageForCacheFlush($pid);
		}
	}

	/**
	 * Retrieves the pid of the current record (if it can be found) and registers
	 * the containing page for cache flush.
	 *
	 * Additionally, when the given record is a sys_file the cache for all
	 * file collections pointing to the parent folder of the file will
	 * be registered for flush.
	 *
	 * If the record is found flushRelatedCacheForRecord() will be called again.
	 * If the MAX_RECURSE_DEPTH is reached, this method will exit immediately.
	 *
	 * Internal use, should be called by flushRelatedCacheForRecord() only!
	 *
	 * @param string $referencedTable
	 * @param int $referencedUid
	 * @param int $depth
	 * @return void
	 */
	protected function registerSingleRecordRecursiveForCacheFlush($referencedTable, $referencedUid, $depth) {

		if ($depth > self::MAX_RECURSE_DEPTH) {
			return;
		}

		if ($referencedTable === 'sys_file') {
			$this->registerRelatedFolderFileCollectionsForCacheFlush($referencedUid);
		}

		$pidQuery = $this->getPidExcludeStatement(TRUE);
		$record = BackendUtility::getRecord($referencedTable, $referencedUid, 'pid', $pidQuery);
		if (is_array($record) && count($record)) {
			if ($record['pid'] > 0) {
				$this->registerPageForCacheFlush($record['pid']);
			}
			$this->flushRelatedCacheForRecord($referencedTable, $referencedUid, ($depth + 1));
		}
	}
}