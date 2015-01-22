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

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This cache optimizer hooks into the ResourceStorage and clears the cache
 * for all pages pointing to a changed file or folder.
 */
class CacheOptimizerFiles extends AbstractCacheOptimizer implements SingletonInterface {

	/**
	 *
	 * @var CacheApi
	 */
	protected $cacheApi;

	/**
	 * Array containing all page UIDs for which the cache should be cleared.
	 *
	 * @var array
	 */
	protected $flushCachePids = array();

	/**
	 * Will be called after a file is added to a directory and flushes
	 * all caches related to this directory.
	 *
	 * @param FileInterface $file
	 * @param Folder $targetFolder
	 * @return void
	 */
	public function handleFileAddPost(
		/** @noinspection PhpUnusedParameterInspection */
		FileInterface $file,
		Folder $targetFolder
	) {
		$this->initialize();
		$this->flushCacheForRelatedFolders($targetFolder->getStorage()->getUid(), $targetFolder->getIdentifier());
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Will be called after a file was copied.
	 * The cache for all pages related to the target folder will be flushed.
	 *
	 * @param FileInterface $file
	 * @param Folder $targetFolder
	 * @return void
	 */
	public function handleFileCopyPost(
		/** @noinspection PhpUnusedParameterInspection */
		FileInterface $file,
		Folder $targetFolder
	) {
		$this->initialize();
		$this->flushCacheForRelatedFolders($targetFolder->getStorage()->getUid(), $targetFolder->getIdentifier());
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Will be called after a fil was created.
	 * The cache for all pages related to the target folder will be flushed.
	 *
	 * @param $newFileIdentifier
	 * @param Folder $targetFolder
	 * @return void
	 */
	public function handleFileCreatePost(
		/** @noinspection PhpUnusedParameterInspection */
		$newFileIdentifier,
		Folder $targetFolder
	) {
		$this->initialize();
		$this->flushCacheForRelatedFolders($targetFolder->getStorage()->getUid(), $targetFolder->getIdentifier());
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Will be called ater a file was deleted.
	 * The cache for all pages related to the containing folder will be flushed.
	 *
	 * @param FileInterface $file
	 * @return void
	 */
	public function handleFileDeletePost(FileInterface $file) {
		$this->initialize();
		$fileFolder = $file->getParentFolder();
		$this->flushCacheForRelatedFolders($fileFolder->getStorage()->getUid(), $fileFolder->getIdentifier());
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Will be called after a file is moved.
	 * The cache for all pages pointing to the source directory, to the target directory
	 * or to the moved file will be flushed.
	 *
	 * @param FileInterface $file
	 * @param Folder $targetFolder
	 * @param Folder $originalFolder
	 * @return void
	 */
	public function handleFileMovePost(
		FileInterface $file,
		/** @noinspection PhpUnusedParameterInspection */
		Folder $targetFolder,
		Folder $originalFolder
	) {
		$this->initialize();
		$this->flushCacheForRelatedFolders($originalFolder->getStorage()->getUid(), $originalFolder->getIdentifier());
		if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
			$this->flushRelatedCacheForRecord('sys_file', $file->getUid());
		}
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Will be called after a file was renamed.
	 * Flushes the cache for all pages pointing to the file or its parent directory.
	 *
	 * @param FileInterface $file
	 * @param $targetFolder
	 * @return void
	 */
	public function handleFileRenamePost(
		FileInterface $file,
		/** @noinspection PhpUnusedParameterInspection */
		$targetFolder
	) {
		$this->initialize();
		if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
			$this->flushRelatedCacheForRecord('sys_file', $file->getUid());
		}
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Will be called after the content was changed in the given file.
	 * Flushes the cache for all pages pointing to the file or its parent directory.
	 *
	 * @param FileInterface $file
	 * @param $contents
	 * @return void
	 */
	public function handleFileSetContentsPost(
		FileInterface $file,
		/** @noinspection PhpUnusedParameterInspection */
		$contents
	) {
		$this->initialize();
		if ($file instanceof \TYPO3\CMS\Core\Resource\File) {
			$this->flushRelatedCacheForRecord('sys_file', $file->getUid());
		}
		$this->flushCacheForAllRegisteredPages();
	}

	/**
	 * Clears the cache for all registered page UIDs.
	 *
	 * @return void
	 */
	protected function flushCacheForAllRegisteredPages() {
		$flushCachePids = array_unique($this->flushCachePids);
		$this->flushCachePids = array();
		foreach ($flushCachePids as $pageId) {
			$this->cacheApi->flushCacheForPage($pageId, FALSE);
		}
	}

	/**
	 * Registers the given page UID in the array of pages for which the
	 * cache should be flushed at the end.
	 *
	 * @param int $pid
	 * @return void
	 */
	protected function flushCacheForPage($pid) {
		$this->flushCachePids[] = $pid;
	}

	/**
	 * Initializes all required classes.
	 *
	 * @return void
	 */
	protected function initialize() {
		if (isset($this->databaseConnection)) {
			return;
		}
		parent::initialize();
		$this->resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
		$this->cacheApi = GeneralUtility::makeInstance('Tx\\Cacheopt\\CacheApi');
	}
}