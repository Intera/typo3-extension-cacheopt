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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Central registry that contains information about which tables are connected
 * to which content types.
 *
 * It also stores information about all records / pages / folders for which the
 * cache has already been flushed to prevent duplicate cache flushing.
 */
class CacheOptimizerRegistry implements SingletonInterface {

	/**
	 * Array containing information which table is related to which content type:
	 * array(
	 *   'ty_myext_mytable' => 'myext_contenttype'
	 * )
	 *
	 * @var array
	 */
	protected $contentTypesByTable = array();

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

	/**
	 * Array containing UIDs of pages for which the cache has been flushed already.
	 *
	 * @var array
	 */
	protected $flushedPageUids = array();

	/**
	 * Array containing information which table is related to which plugin type:
	 * array(
	 *   'ty_myext_mytable' => 'myext_plugintype'
	 * )
	 *
	 * @var array
	 */
	protected $pluginTypesByTable = array();

	/**
	 * Array containing the identifiers of the folders for which the cache has already been flushed.
	 * array(
	 *   'storageUid' => array('directoryIdentifier' => 1)
	 * )
	 *
	 * @var array
	 */
	protected $processedFolders = array();

	/**
	 * Array containing the records that already have been processed:
	 * array(
	 *   'tablename' => array('recordUid' => 1)
	 * )
	 *
	 * @var array
	 */
	protected $processedRecords = array();

	/**
	 * Returns an instance of the CacheOptimizerRegistry.
	 *
	 * @return CacheOptimizerRegistry
	 */
	public static function getInstance() {
		return GeneralUtility::makeInstance('Tx\\Cacheopt\\CacheOptimizerRegistry');
	}

	/**
	 * Returns an array containing all content types that belong to the given
	 * table or NULL if no content types are registered.
	 *
	 * @param string $table
	 * @return array
	 */
	public function getContentTypesForTable($table) {
		if (isset($this->contentTypesByTable[$table])) {
			return $this->contentTypesByTable[$table];
		} else {
			return NULL;
		}
	}

	/**
	 * Returns an array containing all page UIDs for which the cache was flushed already.
	 *
	 * @return array
	 */
	public function getFlushedCachePageUids() {
		return array_unique($this->flushedPageUids);
	}

	/**
	 * Returns an array containing all plugin types that belong to the given
	 * table or NULL if no plugin types are registered.
	 *
	 * @param string $table
	 * @return array
	 */
	public function getPluginTypesForTable($table) {
		if (isset($this->pluginTypesByTable[$table])) {
			return $this->pluginTypesByTable[$table];
		} else {
			return NULL;
		}
	}

	/**
	 * Returns TRUE if the given folder in the given storage was already processed.
	 *
	 * @param int $storageUid
	 * @param string $folderIdentifier
	 * @return bool
	 */
	public function isProcessedFolder($storageUid, $folderIdentifier) {
		return isset($this->processedFolders[(int)$storageUid][$folderIdentifier]);
	}

	/**
	 * Return TRUE if the record with the given UID in the given table was already processed.
	 *
	 * @param string $table
	 * @param int $uid
	 * @return bool
	 */
	public function isProcessedRecord($table, $uid) {
		return isset($this->processedRecords[$table][(int)$uid]);
	}

	/**
	 * Returns TRUE if the cache for the page with the given UID was already flushed.
	 *
	 * @param int $pid
	 * @return bool
	 */
	public function pageCacheIsFlushed($pid) {
		$pid = (int)$pid;
		if ($pid === 0) {
			return TRUE;
		}
		return (array_search($pid, $this->flushedPageUids) !== FALSE);
	}

	/**
	 * Let the registry know that the given table is related
	 * to the given content type.
	 *
	 * @param string $table
	 * @param string $contentType
	 * @return void
	 * @api
	 */
	public function registerContentForTable($table, $contentType) {
		$this->contentTypesByTable[$table][] = $contentType;
	}

	/**
	 * The cache for the page with the given ID was flushed.
	 *
	 * @param int $pid
	 * @return void
	 */
	public function registerPageWithFlushedCache($pid) {
		$this->flushedPageUids[] = (int)$pid;
	}

	/**
	 * Marks all page UIDs contained in the given array as cache flushed.
	 *
	 * @param array $pidArray
	 */
	public function registerPagesWithFlushedCache(array $pidArray) {
		foreach ($pidArray as $pid) {
			$this->registerPageWithFlushedCache($pid);
		}
	}

	/**
	 * Let the registry know that the given table is related
	 * to the given plugin type.
	 *
	 * @param string $table
	 * @param string $listType
	 * @return void
	 * @api
	 */
	public function registerPluginForTable($table, $listType) {
		$this->pluginTypesByTable[$table][] = $listType;
	}

	/**
	 * Let the registry know that the given tables are related
	 * to the given plugin type.
	 *
	 * @param array $tables
	 * @param string $listType
	 * @return void
	 * @api
	 */
	public function registerPluginForTables(array $tables, $listType) {
		foreach ($tables as $table) {
			$this->registerPluginForTable($table, $listType);
		}
	}

	/**
	 * The folder in the given storage with the given identifier has been processed.
	 *
	 * @param int $storageUid
	 * @param string $folderIdentifier
	 * @return void
	 */
	public function registerProcessedFolder($storageUid, $folderIdentifier) {
		$this->processedFolders[(int)$storageUid][$folderIdentifier] = TRUE;
	}

	/**
	 * The record in the given table with the given uid has been processed.
	 *
	 * @param string $table
	 * @param int $uid
	 * @return void
	 */
	public function registerProcessedRecord($table, $uid) {
		$this->processedRecords[$table][(int)$uid] = TRUE;
	}

	/**
	 * @return void
	 */
	protected function initialize() {
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
	}
}