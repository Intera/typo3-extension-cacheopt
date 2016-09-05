<?php
namespace Tx\CacheoptTest\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "cacheopt_test".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Dummy controller for rendering records.
 */
class RecordController extends ActionController
{

    /**
     * Display a dummy string.
     *
     * @return string
     */
    public function displayAction()
    {
        return 'test';
    }

    /**
     * We do not need a view since we only render a dummy string.
     *
     * @return null
     */
    protected function resolveView()
    {
        return null;
    }

}