<?php
defined('TYPO3_MODE') || die();

/**
 * PageTS
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:viresponsiveimages/Configuration/TSconfig/Page/All.typoscript">'
);

/**
 * TypoScript Constants
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:viresponsiveimages/Resources/Private/TypoScript/constants.typoscript">'
);

/**
 * TypoScript Setup
 */
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:viresponsiveimages/Resources/Private/TypoScript/setup.typoscript">'
);
