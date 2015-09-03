<?php
namespace Tx\Cacheopt\Tests\Functional;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "cacheopt".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx\Cacheopt\Tests\Functional\Mocks\ResourceStorageMock;
use TYPO3\CMS\Core\Resource\ResourceStorage;

require_once(dirname(__FILE__) . '/CacheOptimizerTestAbstract.php');

/**
 * Functional test for the files cache optimizer.
 */
class CacheOptimizerFilesTest extends CacheOptimizerTestAbstract {

	const FILE_IDENTIFIER_REFERENCED = '/testdirectory/testfile_referenced.txt';

	const FILE_IDENTIFIER_REFERENCED_IN_DIRECTORY = '/testdirectory_referenced/file_in_referenced_dir.txt';

	const PAGE_UID_REFERENCING_CONTENT_REFERENCING_DIRECTORY = 1310;

	const RESOURCE_STORAGE_UID = 1;

	/**
	 * @var \Tx\Cacheopt\CacheOptimizerFiles
	 */
	protected $cacheOptimizerFiles;

	/**
	 * @var \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility
	 */
	protected $fileProcessor;

	/**
	 * @var \TYPO3\CMS\Core\Resource\StorageRepository
	 */
	protected $storageRepository;

	/**
	 * Initializes required classes.
	 */
	public function setUp() {
		parent::setUp();
		$this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\StorageRepository');
		$this->fileProcessor = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\File\\ExtendedFileUtility');
		$this->cacheOptimizerFiles = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Cacheopt\\CacheOptimizerFiles');
		$this->initFileProcessor();
	}

	/**
	 *
	 * If a sys_file record is changed the directory of the file is detected
	 * and the cache of all pages is cleared where a reference to this directory is
	 * used in the content elements.
	 *
	 * @test
	 */
	public function fileChangeClearsCacheForPagesReferencingToTheDirectory() {

		$this->fillPageCache(self::PAGE_UID_REFERENCED_DIRECTORY);
		$this->fillPageCache(self::PAGE_UID_REFERENCING_CONTENT_REFERENCING_DIRECTORY);

		$fileValues = array(
			'editfile' => array(
				array(
					'data' => 'testcontent_modified_directory',
					'target' => $this->getRootFolderIdentifier() . ltrim(self::FILE_IDENTIFIER_REFERENCED_IN_DIRECTORY, '/')
				)
			),
		);

		$this->processFileArrayAndFlushCache($fileValues);
		$this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCED_DIRECTORY);
		$this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCING_CONTENT_REFERENCING_DIRECTORY);
	}

	/**
	 * If a sys_file record is changed the the cache of all pages is cleared
	 * where a reference to this file is used in the content elements.
	 *
	 * @test
	 */
	public function fileChangeClearsCacheForPagesReferencingToTheFile() {

		$this->fillPageCache(self::PAGE_UID_REFERENCED_FILE);

		$fileValues = array(
			'editfile' => array(
				array(
					'data' => 'testcontent_modified',
					'target' => $this->getRootFolderIdentifier() . ltrim(self::FILE_IDENTIFIER_REFERENCED, '/')
				)
			),
		);

		$this->processFileArrayAndFlushCache($fileValues);
		$this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCED_FILE);
	}

	/**
	 * If a sys_file record that is referenced by a page is overwritten by an upload
	 * the cache of the page referencing the file should be cleared.
	 *
	 * @test
	 */
	public function fileUploadClearsCacheOfPageWhereOverwrittenFileIsReferenced() {

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][ResourceStorage::class]['className'] = ResourceStorageMock::class;

		$this->fillPageCache(self::PAGE_UID_REFERENCED_FILE);

		$uploadPosition = 'file1';
		$_FILES['upload_' . $uploadPosition] = array(
			'name' => basename(self::FILE_IDENTIFIER_REFERENCED),
			'type' => 'text/plain',
			'tmp_name' => PATH_site . 'typo3temp/uploadfiles/testfile_referenced.txt',
			'size' => 31
		);

		$fileValues = array(
			'upload' => array(
				array(
					'data' => $uploadPosition,
					'target' => $this->getRootFolderIdentifier() . ltrim(dirname(self::FILE_IDENTIFIER_REFERENCED), '/')
				)
			),
		);

		$this->processFileArrayAndFlushCache($fileValues);
		$this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCED_FILE);
	}

	/**
	 * Returns the default storage.
	 *
	 * @return \TYPO3\CMS\Core\Resource\ResourceStorage
	 */
	protected function getDefaultStorage() {
		return $this->storageRepository->findByUid(self::RESOURCE_STORAGE_UID);
	}

	/**
	 * Returns the identifier of the storage root folder.
	 *
	 * @return string
	 */
	protected function getRootFolderIdentifier() {
		$storage = $this->getDefaultStorage();
		$folderIdentifier = '/';
		// the root of the storage
		$folderCombinedIdentifier = $storage->getUid() . ':' . $folderIdentifier;
		return $folderCombinedIdentifier;
	}

	/**
	 * Initializes the file processor.
	 */
	protected function initFileProcessor() {
		$this->fileProcessor->init(array(), $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']);
		$this->fileProcessor->dontCheckForUnique = 1;
	}

	/**
	 * Lets the file processor process the given array and lets the cache
	 * optimizer flush the cache for all collected pages.
	 *
	 * @param array $fileValues
	 */
	protected function processFileArrayAndFlushCache($fileValues) {
		$this->fileProcessor->start($fileValues);
		$this->fileProcessor->processData();
	}
}