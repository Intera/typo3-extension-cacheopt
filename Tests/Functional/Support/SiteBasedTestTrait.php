<?php
declare(strict_types=1);

namespace Tx\Cacheopt\Tests\Functional\Support;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait used for test classes that want to set up (= write) site configuration files.
 *
 * Mainly used when testing Site-related tests in Frontend requests.
 *
 * Be sure to set the LANGUAGE_PRESETS const in your class.
 */
trait SiteBasedTestTrait
{
    /**
     * @param int $rootPageId
     * @param string $base
     * @return array
     */
    protected function buildSiteConfiguration(
        int $rootPageId,
        string $base = ''
    ): array {
        return [
            'rootPageId' => $rootPageId,
            'base' => $base,
        ];
    }

    /**
     * @param string $identifier
     * @param array $site
     * @param array $languages
     * @param array $errorHandling
     */
    protected function writeSiteConfiguration(
        string $identifier,
        array $site = [],
        array $languages = [],
        array $errorHandling = []
    ) {
        $configuration = $site;
        if (!empty($languages)) {
            $configuration['languages'] = $languages;
        }
        if (!empty($errorHandling)) {
            $configuration['errorHandling'] = $errorHandling;
        }
        $siteConfiguration = new SiteConfiguration(
            $this->instancePath . '/typo3conf/sites/'
        );

        // Ensure no previous site configuration influences the test
        GeneralUtility::rmdir($this->instancePath . '/typo3conf/sites/' . $identifier, true);
        $siteConfiguration->write($identifier, $configuration);
    }
}
