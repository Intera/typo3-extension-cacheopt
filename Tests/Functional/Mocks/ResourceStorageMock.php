<?php
namespace Tx\Cacheopt\Tests\Functional\Mocks;

/*                                                                        *
 * This script belongs to the TYPO3 Extension "cacheopt".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Resource\ResourceStorage;

/**
 * This mock adjusts the behavior of the default resource storage.
 */
class ResourceStorageMock extends ResourceStorage {

	/**
	 * Disables the is_uploaded_file() check and only makes sure the user has permissions to add a file to a folder.
	 *
	 * @param string $localFilePath the temporary file name from $_FILES['file1']['tmp_name']
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $targetFileName the destination file name $_FILES['file1']['name']
	 * @param int $uploadedFileSize
	 * @return void
	 */
	protected function assureFileUploadPermissions($localFilePath, $targetFolder, $targetFileName, $uploadedFileSize) {
		$this->assureFileAddPermissions('', $targetFolder, $targetFileName);
	}
}