<?php
declare(strict_types=1);

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

use TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentTagCollector implements ContentObjectPostInitHookInterface
{
    /**
     * Hook for post processing the initialization of ContentObjectRenderer
     *
     * @param ContentObjectRenderer $parentObject Parent content object
     */
    public function postProcessContentObjectInitialization(
        ContentObjectRenderer &$parentObject
    ) {
        $tsfe = $this->getTypoScriptFrontendController();
        if (!$tsfe instanceof TypoScriptFrontendController) {
            return;
        }

        $cacheTags = [];
        $contentData = $parentObject->data;

        $table = $parentObject->getCurrentTable();
        $uid = (int)$contentData['uid'];
        if ($table === '' || $uid === 0) {
            return;
        }

        $cacheTags[] = $table . '_' . $uid;

        if (array_key_exists('_LOCALIZED_UID', $contentData) && (int)$contentData['_LOCALIZED_UID'] !== 0) {
            $cacheTags[] = $table . '_' . $contentData['_LOCALIZED_UID'];
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
