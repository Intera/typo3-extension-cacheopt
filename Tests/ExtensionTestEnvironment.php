<?php
declare(strict_types=1);

namespace Tx\Cacheopt\Tests;

/**
 * This hook creates a vendor symlink in the Web folder because this is where
 * the testing framework is looking for an autoload.php file.
 */
class ExtensionTestEnvironment
{
    public static function prepare()
    {
        $rootDirecotry = dirname(__DIR__);

        $vendorDir = $rootDirecotry . DIRECTORY_SEPARATOR . '.Build' . DIRECTORY_SEPARATOR . 'vendor';

        $webDir = $rootDirecotry . DIRECTORY_SEPARATOR . '.Build' . DIRECTORY_SEPARATOR . 'Web';
        $webVendorSymlink = $webDir . DIRECTORY_SEPARATOR . 'vendor';

        if (!is_link($webVendorSymlink)) {
            symlink($vendorDir, $webVendorSymlink);
        }
    }
}
