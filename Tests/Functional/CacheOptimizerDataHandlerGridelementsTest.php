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

/**
 * Functional tests for the data handler cache optimizer.
 */
class CacheOptimizerDataHandlerGridelementsTest extends CacheOptimizerTestAbstract
{
    const CONTENT_UID_REFERENCED = 21111;

    const PAGE_UID_REFERENCING_CONTENT = 221;

    public function setUp(): void
    {
        // TODO: re-enable when gridelements is available for version 10
        return;

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
        // TODO: re-enable when gridelements is available for version 10
        $this->markTestSkipped('gridelements it not yet available for verison 10');
        return;

        $this->fillPageCache(self::PAGE_UID_REFERENCING_CONTENT);
        $this->getActionService()->modifyRecord(
            'tt_content',
            self::CONTENT_UID_REFERENCED,
            ['header' => 'referencing_content_mod']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCING_CONTENT);
    }
}
