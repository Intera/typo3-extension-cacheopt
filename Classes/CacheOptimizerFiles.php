<?php
declare(strict_types=1);

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

use InvalidArgumentException;
use RuntimeException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This cache optimizer hooks into the ResourceStorage and clears the cache
 * for all pages pointing to a changed file or folder.
 */
class CacheOptimizerFiles implements SingletonInterface
{
    /**
     *
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var CacheOptimizerRegistry
     */
    protected $cacheOptimizerRegistry;

    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * Array containing all page UIDs for which the cache should be cleared.
     *
     * @var array
     */
    protected $flushCacheTags = [];

    /**
     * Will be called after a file is added to a directory and flushes
     * all caches related to this directory.
     *
     * @param FileInterface|File $file
     * @param Folder $targetFolder
     * @return void
     * @throws RuntimeException
     * @throws NoSuchCacheGroupException
     * @throws InvalidArgumentException
     */
    public function handleFileAddPost(
        /** @noinspection PhpUnusedParameterInspection */
        FileInterface $file,
        Folder $targetFolder
    ) {
        $this->initialize();
        $this->flushCacheForRelatedFolders($targetFolder->getStorage()->getUid(), $targetFolder->getIdentifier());
        if ($file instanceof File) {
            $this->registerFileForCacheFlush($file);
        }
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Will be called after a file was copied.
     * The cache for all pages related to the target folder will be flushed.
     *
     * @param FileInterface $file
     * @param Folder $targetFolder
     * @return void
     * @throws RuntimeException
     * @throws NoSuchCacheGroupException
     * @throws InvalidArgumentException
     */
    public function handleFileCopyPost(
        /** @noinspection PhpUnusedParameterInspection */
        FileInterface $file,
        Folder $targetFolder
    ) {
        $this->initialize();
        $this->flushCacheForRelatedFolders($targetFolder->getStorage()->getUid(), $targetFolder->getIdentifier());
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Will be called after a fil was created.
     * The cache for all pages related to the target folder will be flushed.
     *
     * @param $newFileIdentifier
     * @param Folder $targetFolder
     * @return void
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoSuchCacheGroupException
     */
    public function handleFileCreatePost(
        /** @noinspection PhpUnusedParameterInspection */
        $newFileIdentifier,
        Folder $targetFolder
    ) {
        $this->initialize();
        $this->flushCacheForRelatedFolders($targetFolder->getStorage()->getUid(), $targetFolder->getIdentifier());
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Will be called ater a file was deleted.
     * The cache for all pages related to the containing folder will be flushed.
     *
     * @param FileInterface $file
     * @return void
     * @throws RuntimeException
     * @throws NoSuchCacheGroupException
     * @throws InvalidArgumentException
     */
    public function handleFileDeletePost(FileInterface $file)
    {
        $this->initialize();
        $fileFolder = $file->getParentFolder();
        $this->flushCacheForRelatedFolders($fileFolder->getStorage()->getUid(), $fileFolder->getIdentifier());
        $this->flushCacheForAllRegisteredTags();
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws NoSuchCacheGroupException
     */
    public function handleFileMovePost(
        FileInterface $file,
        /** @noinspection PhpUnusedParameterInspection */
        Folder $targetFolder,
        Folder $originalFolder
    ) {
        $this->initialize();
        $this->flushCacheForRelatedFolders($originalFolder->getStorage()->getUid(), $originalFolder->getIdentifier());
        if ($file instanceof File) {
            $this->registerFileForCacheFlush($file);
        }
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Will be called after a file was renamed.
     * Flushes the cache for all pages pointing to the file or its parent directory.
     *
     * @param FileInterface $file
     * @param $targetFolder
     * @return void
     * @throws NoSuchCacheGroupException
     * @throws InvalidArgumentException
     */
    public function handleFileRenamePost(
        FileInterface $file,
        /** @noinspection PhpUnusedParameterInspection */
        $targetFolder
    ) {
        $this->initialize();
        if ($file instanceof File) {
            $this->registerFileForCacheFlush($file);
        }
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Will be called after a file was renamed.
     * Flushes the cache for all pages pointing to the file or its parent directory.
     *
     * @param FileInterface $file
     * @param $localFilePath
     * @return void
     * @throws NoSuchCacheGroupException
     * @throws InvalidArgumentException
     */
    public function handleFileReplacePost(
        $file,
        /** @noinspection PhpUnusedParameterInspection */
        $localFilePath
    ) {
        $this->initialize();
        if ($file instanceof File) {
            $this->registerFileForCacheFlush($file);
        }
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Will be called after the content was changed in the given file.
     * Flushes the cache for all pages pointing to the file or its parent directory.
     *
     * @param FileInterface $file
     * @param $contents
     * @return void
     * @throws NoSuchCacheGroupException
     * @throws InvalidArgumentException
     */
    public function handleFileSetContentsPost(
        FileInterface $file,
        /** @noinspection PhpUnusedParameterInspection */
        $contents
    ) {
        $this->initialize();
        if ($file instanceof File) {
            $this->registerFileForCacheFlush($file);
        }
        $this->flushCacheForAllRegisteredTags();
    }

    /**
     * Clears the cache for all registered page UIDs.
     *
     * @return void
     * @throws NoSuchCacheGroupException
     */
    protected function flushCacheForAllRegisteredTags()
    {
        $flushCacheTags = array_unique($this->flushCacheTags);
        $this->flushCacheTags = [];
        foreach ($flushCacheTags as $cacheTag) {
            $this->cacheManager->flushCachesInGroupByTag('pages', $cacheTag);
        }
    }

    /**
     * Searches for all records pointing to the given folder and flushes
     * the related page caches.
     *
     * @param int $storageUid
     * @param string $folderIdentifier
     * @return void
     * @throws RuntimeException
     */
    protected function flushCacheForRelatedFolders($storageUid, $folderIdentifier)
    {
        if ($this->cacheOptimizerRegistry->isProcessedFolder($storageUid, $folderIdentifier)) {
            return;
        }
        $this->cacheOptimizerRegistry->registerProcessedFolder($storageUid, $folderIdentifier);
        $fileCollectionResult = $this->databaseConnection->exec_SELECTquery(
            'uid',
            'sys_file_collection',
            "deleted=0 AND type='folder' AND storage="
            . (int)$storageUid . ' AND folder='
            . $this->databaseConnection->fullQuoteStr($folderIdentifier, 'sys_file_collection')
        );
        while ($fileCollectionRow = $this->databaseConnection->sql_fetch_assoc($fileCollectionResult)) {
            if (!is_array($fileCollectionRow)) {
                throw new RuntimeException('Database error while fetching file collections for folder.');
            }
            /** @var array $fileCollectionRow */
            $this->registerRecordForCacheFlushing('sys_file_collection', $fileCollectionRow['uid']);
        }
    }

    /**
     * Initializes all required classes.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function initialize()
    {
        if ($this->databaseConnection !== null) {
            return;
        }
        $this->cacheOptimizerRegistry = GeneralUtility::makeInstance(CacheOptimizerRegistry::class);
        $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Registers the given file for cache flushing.
     *
     * @param File $file
     */
    protected function registerFileForCacheFlush(File $file)
    {
        $this->registerRecordForCacheFlushing('sys_file', $file->getUid());
    }

    /**
     * Registers the given page UID in the array of pages for which the
     * cache should be flushed at the end.
     *
     * @param string $table
     * @param int $uid
     */
    protected function registerRecordForCacheFlushing($table, $uid)
    {
        if ($this->cacheOptimizerRegistry->isProcessedRecord($table, $uid)) {
            return;
        }
        $this->cacheOptimizerRegistry->registerProcessedRecord($table, $uid);
        $this->flushCacheTags[] = $table . '_' . $uid;
    }
}
