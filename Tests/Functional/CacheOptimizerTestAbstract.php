<?php
declare(strict_types=1);

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

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RuntimeException;
use SplFileInfo;
use TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase;

/**
 * Base class for all functional tests of the cache optimizer.
 */
abstract class CacheOptimizerTestAbstract extends AbstractDataHandlerActionTestCase
{
    const PAGE_UID_REFERENCED_DIRECTORY = 131;

    const PAGE_UID_REFERENCED_FILE = 130;

    /**
     * We want the folders containing the test files to be created.
     *
     * @var array
     */
    protected $additionalFoldersToCreate = [
        '/fileadmin/testdirectory',
        '/fileadmin/testdirectory_referenced',
        '/typo3temp/uploadfiles',
    ];

    /**
     * We do not expect any error log entries.
     *
     * @var array
     */
    protected $expectedErrorLogEntries = null;

    /**
     * The files that should be copied to the test instance.
     *
     * @var array
     */
    protected $filesToCopyInTestInstance = [
        'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Files/fileadmin/testdirectory/testfile.txt' => 'fileadmin/testdirectory/testfile.txt',
        'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Files/fileadmin/testdirectory/testfile_referenced.txt' => 'fileadmin/testdirectory/testfile_referenced.txt',
        'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Files/fileadmin/testdirectory_referenced/file_in_referenced_dir.txt' => 'fileadmin/testdirectory_referenced/file_in_referenced_dir.txt',
        'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Files/typo3temp/uploadfiles/testfile_referenced.txt' => 'typo3temp/uploadfiles/testfile_referenced.txt',
    ];

    /**
     * We need to remove the additional configuration of our base class,
     * otherwise the content renderer will not work properly and the cache
     * will not be filled.
     *
     * @var array
     */
    protected $pathsToLinkInTestInstance = [];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Extensions/cacheopt_test',
        'typo3conf/ext/cacheopt',
    ];

    /**
     * Sets up the test environment.
     */
    public function setUp()
    {
        $this->coreExtensionsToLoad[] = 'css_styled_content';

        define('TX_CACHEOPT_FUNCTIONAL_TEST', true);

        parent::setUp();

        unset($GLOBALS['TYPO3_CONF_VARS']['LOG']);

        $this->loadDatabaseFixtures();
        $this->copyFilesToTestInstance();
    }

    /**
     * Asserts that the page cache for the given page is empty.
     *
     * @param int $pageUid
     */
    protected function assertPageCacheIsEmpty($pageUid)
    {
        $cacheEntries = $this->getPageCacheRecords($pageUid);
        $this->assertCount(0, $cacheEntries, 'Page cache for page ' . $pageUid . ' is not empty.');
    }

    /**
     * Asserts that the page cache for the given page is filled.
     *
     * @param int $pageUid
     */
    protected function assertPageCacheIsFilled($pageUid)
    {
        $cacheEntries = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'id',
            'cf_cache_pages_tags',
            'tag=\'pageId_' . $pageUid . '\''
        );
        $this->assertGreaterThanOrEqual(1, count($cacheEntries), 'Page cache for page ' . $pageUid . ' is not filled.');
    }

    /**
     * Copies the files defined in $filesToCopyInTestInstance to the test instance.
     *
     * @throws RuntimeException
     */
    protected function copyFilesToTestInstance()
    {
        foreach ($this->filesToCopyInTestInstance as $sourcePathToLinkInTestInstance => $destinationPathToLinkInTestInstance) {
            $sourcePath = ORIGINAL_ROOT . '/' . ltrim($sourcePathToLinkInTestInstance, '/');
            if (!file_exists($sourcePath)) {
                throw new RuntimeException(
                    'Path ' . $sourcePath . ' not found',
                    1376745645
                );
            }
            $destinationPath = PATH_site . '/' . ltrim($destinationPathToLinkInTestInstance, '/');
            $success = copy($sourcePath, $destinationPath);
            if (!$success) {
                throw new RuntimeException(
                    'Can not copy the path ' . $sourcePath . ' to ' . $destinationPath,
                    1389969623
                );
            }
        }
    }

    /**
     * Fills the page cache for the page with the given ID and makes sure
     *
     * @param int $pageUid
     */
    protected function fillPageCache($pageUid)
    {
        $this->getFrontendResponse($pageUid)->getContent();
        $this->assertPageCacheIsFilled($pageUid);
    }

    /**
     * Retrieves one page cache record that belongs to the page with the given UID.
     *
     * @param int $pageUid
     * @return array|NULL
     */
    protected function getPageCacheRecords($pageUid)
    {
        return $this->getDatabaseConnection()->exec_SELECTgetRows(
            'cf_cache_pages.id',
            'cf_cache_pages, cf_cache_pages_tags',
            'cf_cache_pages.identifier=cf_cache_pages_tags.identifier AND tag=\'pageId_' . $pageUid . '\'',
            '',
            '',
            1
        );
    }

    /**
     * Loads all required database fixtures from the EXT:cacheopt/Tests/Functional/Fixtures/Database directory.
     */
    protected function loadDatabaseFixtures()
    {
        $fixtureDir = ORIGINAL_ROOT . 'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Database/';
        $iteratorMode = FilesystemIterator::UNIX_PATHS
            | FilesystemIterator::SKIP_DOTS
            | FilesystemIterator::CURRENT_AS_FILEINFO;
        $iterator = new RecursiveDirectoryIterator($fixtureDir, $iteratorMode);

        while ($iterator->valid()) {
            /** @var $entry SplFileInfo */
            $entry = $iterator->current();
            // Skip non-files/non-folders, and empty entries.
            if (!$entry->isFile() || $entry->isDir() || $entry->getFilename() === '') {
                $iterator->next();
                continue;
            }
            $this->importDataSet($entry->getPathname());
            $iterator->next();
        }
    }
}
