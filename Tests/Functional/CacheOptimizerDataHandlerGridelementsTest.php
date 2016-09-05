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

/**
 * Functional tests for the data handler cache optimizer.
 */
class CacheOptimizerDataHandlerGridelementsTest extends CacheOptimizerTestAbstract
{
    const CONTENT_UID_REFERENCED = 21111;
    const PAGE_UID_REFERENCING_CONTENT = 221;

    public function setUp()
    {
        if ($this->isTypo3Version8()) {
            return;
        }

        $this->testExtensionsToLoad[] = 'typo3conf/ext/gridelements';

        parent::setUp();

        $this->importDataSet(
            ORIGINAL_ROOT . 'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Database/gridelements/pages.xml'
        );
        $this->importDataSet(
            ORIGINAL_ROOT . 'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Database/gridelements/sys_template.xml'
        );
        $this->importDataSet(
            ORIGINAL_ROOT . 'typo3conf/ext/cacheopt/Tests/Functional/Fixtures/Database/gridelements/tt_content.xml'
        );
    }

    /**
     * If a content element changes the cache is cleared for all pages that contain
     * record content elements that point to the changed content.
     *
     * @test
     */
    public function contentChangeClearsCacheForRelatedRecordContentsWithinGridelements()
    {
        if ($this->isTypo3Version8()) {
            $this->markTestSkipped('gridelements is not supporting TYPO3 8.0 yet.');
        }

        $this->fillPageCache(self::PAGE_UID_REFERENCING_CONTENT);
        $this->actionService->modifyRecord(
            'tt_content',
            self::CONTENT_UID_REFERENCED,
            ['header' => 'referencing_content_mod']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCING_CONTENT);
    }

    /**
     * We check if the current TYPO3 version is 8.x.
     *
     * We can not use \TYPO3\CMS\Core\Utility\GeneralUtility::compat_version() because we must run
     * parent::setUp() before to make it work which will already cause an error when loading
     * the gridelements Extension.
     */
    protected function isTypo3Version8()
    {
        // We know the ConnectionPool class was introduced in TYPO3 8.x.
        return class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool');
    }
}
