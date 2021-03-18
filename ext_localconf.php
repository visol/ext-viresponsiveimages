<?php
defined('TYPO3') || die();

/**
 * PageTS
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    "@import 'EXT:viresponsiveimages/Configuration/TSconfig/Page/All.typoscript'"
);

/**
 * TypoScript Constants
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    "@import 'EXT:viresponsiveimages/Resources/Private/TypoScript/constants.typoscript'"
);

/**
 * TypoScript Setup
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    "@import 'EXT:viresponsiveimages/Resources/Private/TypoScript/setup.typoscript'"
);
