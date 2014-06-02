<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Tx.CacheoptTest',
	'RecordRenderPlugin',
	'Cacheopt - Record renderer plugin'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Tx.CacheoptTest',
	'RecordRenderContent',
	'Cacheopt - Record renderer content'
);
