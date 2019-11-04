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

use DirectoryIterator;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RuntimeException;
use SplFileInfo;
use Tx\Cacheopt\Tests\Functional\Fixtures\NonCacheClearingFrontendController;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Base class for all functional tests of the cache optimizer.
 */
abstract class CacheOptimizerTestAbstract extends FunctionalTestCase
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

    protected $configurationToUseInTestInstance = [
        'SYS' => [
            'Objects' => [
                TypoScriptFrontendController::class => ['className' => NonCacheClearingFrontendController::class],
            ],
        ],
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

        $GLOBALS['BE_USER'] = $this->setUpBackendUserFromFixture(1);

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        $GLOBALS['LANG']->csConvObj = GeneralUtility::makeInstance(CharsetConverter::class);
    }

    /**
     * @param string $tableName
     * @param int $uid
     * @param array $recordData
     * @param NULL|array $deleteTableRecordIds
     */
    public function modifyRecord($tableName, $uid, array $recordData, array $deleteTableRecordIds = null)
    {
        $dataMap = [
            $tableName => [$uid => $recordData],
        ];
        $commandMap = [];
        if (!empty($deleteTableRecordIds)) {
            foreach ($deleteTableRecordIds as $tableName => $recordIds) {
                foreach ($recordIds as $recordId) {
                    $commandMap[$tableName][$recordId]['delete'] = true;
                }
            }
        }
        $dataHandler = $this->createDataHandler();
        $dataHandler->start($dataMap, $commandMap);
        $dataHandler->process_datamap();
        if (!empty($commandMap)) {
            $dataHandler->process_cmdmap();
        }
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
        $cacheEntriesCount = $this->getDatabaseConnection()->selectCount(
            'id',
            'cf_cache_pages_tags',
            'tag=\'pageId_' . $pageUid . '\''
        );
        $this->assertGreaterThanOrEqual(1, $cacheEntriesCount, 'Page cache for page ' . $pageUid . ' is not filled.');
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
        $tagRow = $this->getDatabaseConnection()->selectSingleRow(
            'identifier',
            'cf_cache_pages_tags',
            'tag=\'pageId_' . $pageUid . '\''
        );

        if (!$tagRow) {
            return [];
        }

        $cacheRow = $this->getDatabaseConnection()->selectSingleRow(
            'id',
            'cf_cache_pages',
            'cf_cache_pages.identifier=\'pageId_' . $tagRow['identifier'] . '\''
        );

        if (!$cacheRow) {
            return [];
        }

        return [$cacheRow];
    }

    /**
     * Loads all required database fixtures from the EXT:cacheopt/Tests/Functional/Fixtures/Database directory.
     */
    protected function loadDatabaseFixtures()
    {
        $fixtureDir = ORIGINAL_ROOT . 'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Database/';
        $iterator = new DirectoryIterator($fixtureDir);

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

        $versionSuffix = 7;
        if ($this->isTypo3Version8()) {
            $versionSuffix = 8;
        }

        $filename = sprintf('typo3_%d.xml', $versionSuffix);
        $this->importDataSet(
            ORIGINAL_ROOT . 'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Database/menu_content/' . $filename
        );
    }

    /**
     * @return DataHandler
     */
    private function createDataHandler()
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        if (isset($backendUser->uc['copyLevels'])) {
            $dataHandler->copyTree = $GLOBALS['BE_USER']->uc['copyLevels'];
        }
        return $dataHandler;
    }

    /**
     * @return bool
     */
    private function isTypo3Version8()
    {
        return VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >= 8000000;
    }
}
