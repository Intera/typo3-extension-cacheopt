<?php
namespace Tx\Cacheopt\TagCollector;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "cacheopt".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FileTagCollector
{
    /**
     * @param ResourceStorage $storage
     * @param DriverInterface $driver
     * @param ResourceInterface $resourceObject
     * @param $relativeToCurrentScript
     * @param array $urlData
     */
    public function collectTagsForPreGeneratePublicUrl(
        /** @noinspection PhpUnusedParameterInspection */
        ResourceStorage $storage,
        DriverInterface $driver,
        ResourceInterface $resourceObject,
        $relativeToCurrentScript,
        array $urlData
    ) {
        $tsfe = $this->getTypoScriptFrontendController();
        if (!$tsfe instanceof TypoScriptFrontendController) {
            return;
        }

        $cacheTags = [];
        $file = null;
        if ($resourceObject instanceof File) {
            $file = $resourceObject;
        } elseif ($resourceObject instanceof FileReference) {
            $file = $resourceObject->getOriginalFile();
            $cacheTags[] = 'sys_file_reference_' . $resourceObject->getUid();
        } elseif ($resourceObject instanceof ProcessedFile) {
            $file = $resourceObject->getOriginalFile();
            $cacheTags[] = 'sys_file_processedfile_' . $resourceObject->getUid();
        }

        if ($file instanceof File) {
            $cacheTags[] = 'sys_file_' . $file->getUid();
            $fileMetadata = $file->_getMetaData();
            $cacheTags[] = 'sys_file_metadata_' . $fileMetadata['uid'];
        }

        $tsfe->addCacheTags($cacheTags);
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
