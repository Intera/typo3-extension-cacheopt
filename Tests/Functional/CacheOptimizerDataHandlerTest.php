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
class CacheOptimizerTest extends CacheOptimizerTestAbstract
{
    const CACHEOPT_RECORD_UID = 1;

    const CONTENT_UID_REFERENCED = 95;

    const CONTENT_UID_SIMPLE = 94;

    const FILE_METADATA_UID_REFERENCED = 28;

    const FILE_METADATA_UID_REFERENCED_IN_DIRECTORY = 26;

    const PAGE_UID_CONTAINING_EXT_CONTENT = 136;

    const PAGE_UID_CONTAINING_EXT_PLUGIN = 134;

    const PAGE_UID_CONTAINING_MENU = 133;

    const PAGE_UID_NORMAL = 129;

    const PAGE_UID_REFERENCED_IN_MENU = 132;

    const PAGE_UID_REFERENCED_IN_MENU_IN_SUBLEVEL = 139;

    const PAGE_UID_REFERENCING_CONTENT = 137;

    const PAGE_UID_ROOT = 128;

    /**
     * If content is edited on a page the cache for this page is cleared.
     *
     * This works by default in TYPO3.
     *
     * @test
     */
    public function contentChangeClearsCacheForContainingPage()
    {
        $this->fillPageCache(self::PAGE_UID_NORMAL);
        $this->actionService->modifyRecord(
            'tt_content',
            self::CONTENT_UID_SIMPLE,
            ['header' => 'referenced_content_mod']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_NORMAL);
    }

    /**
     * If a content element changes the cache is cleared for all pages that contain
     * record content elements that point to the changed content.
     *
     * @test
     */
    public function contentChangeClearsCacheForRelatedRecordContents()
    {
        $this->fillPageCache(self::PAGE_UID_REFERENCING_CONTENT);
        $this->actionService->modifyRecord(
            'tt_content',
            self::CONTENT_UID_REFERENCED,
            ['header' => 'referencing_content_mod']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCING_CONTENT);
    }

    /**
     * If a sys_file_metadata record is changed the directory of the file is detected
     * and the cache of all pages is cleared where a reference to this directory is
     * used in the content elements.
     *
     * @test
     */
    public function fileMetadataChangeClearsCacheForPagesReferencingToTheDirectory()
    {
        $this->fillPageCache(self::PAGE_UID_REFERENCED_DIRECTORY);
        $this->actionService->modifyRecord(
            'sys_file_metadata',
            self::FILE_METADATA_UID_REFERENCED_IN_DIRECTORY,
            ['title' => 'testtitle_referenced']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCED_DIRECTORY);
    }

    /**
     * If a sys_file_metadata record is changed the cache of all pages is cleared where
     * a reference to this file is used in the content elements.
     *
     * @test
     */
    public function fileMetadataChangeClearsCacheForPagesReferencingToTheFile()
    {
        $this->fillPageCache(self::PAGE_UID_REFERENCED_FILE);
        $this->actionService->modifyRecord(
            'sys_file_metadata',
            self::FILE_METADATA_UID_REFERENCED,
            ['title' => 'testtitle_referenced_in_dir']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_REFERENCED_FILE);
    }

    /**
     * When a page is changed the cache is cleared for all pages that contain
     * a menu that points to this page.
     *
     * @test
     */
    public function pageChangeClearsCacheForRelatedMenusOnDifferentLevel()
    {
        $this->fillPageCache(self::PAGE_UID_CONTAINING_MENU);
        $this->actionService->modifyRecord(
            'pages',
            self::PAGE_UID_REFERENCED_IN_MENU_IN_SUBLEVEL,
            ['title' => 'page_referenced_in_menu_modified']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_CONTAINING_MENU);
    }

    /**
     * When a page is changed the cache is cleared for all pages that contain
     * a menu that points to this page.
     *
     * This works by default in TYPO3.
     *
     * @test
     */
    public function pageChangeClearsCacheForRelatedMenusOnSameLevel()
    {
        $this->fillPageCache(self::PAGE_UID_CONTAINING_MENU);
        $this->actionService->modifyRecord(
            'pages',
            self::PAGE_UID_REFERENCED_IN_MENU,
            ['title' => 'page_referenced_in_menu_modified']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_CONTAINING_MENU);
    }

    /**
     * When a record is changed the cache should be cleared for all pages where
     * a related plugin is present in the content elements.
     *
     * @test
     */
    public function recordChangeClearsCacheForPagesContainingRelatedContents()
    {
        $this->fillPageCache(self::PAGE_UID_CONTAINING_EXT_CONTENT);
        $this->actionService->modifyRecord(
            'tx_cacheopttest_domain_model_record',
            self::CACHEOPT_RECORD_UID,
            ['title' => 'testrecord_modified_content']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_CONTAINING_EXT_CONTENT);
    }

    /**
     * When a record is changed the cache should be cleared for all pages where
     * a related plugin is present in the content elements.
     *
     * @test
     */
    public function recordChangeClearsCacheForPagesContainingRelatedPlugins()
    {
        $this->fillPageCache(self::PAGE_UID_CONTAINING_EXT_PLUGIN);
        $this->actionService->modifyRecord(
            'tx_cacheopttest_domain_model_record',
            self::CACHEOPT_RECORD_UID,
            ['title' => 'testrecord_modified_plugin']
        );
        $this->assertPageCacheIsEmpty(self::PAGE_UID_CONTAINING_EXT_PLUGIN);
    }
}
