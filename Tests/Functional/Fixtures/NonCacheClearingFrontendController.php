<?php

namespace Tx\Cacheopt\Tests\Functional\Fixtures;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class NonCacheClearingFrontendController extends TypoScriptFrontendController
{
    public function clearPageCacheContent()
    {
        // We do not want the cache to be cleared!
    }
}
