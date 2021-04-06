<?php

defined('TYPO3') || die();

(function () {
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
        "@import 'EXT:viresponsiveimages/Configuration/TypoScript/constants.typoscript'"
    );

    /**
     * TypoScript Setup
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:viresponsiveimages/Configuration/TypoScript/setup.typoscript'"
    );
})();


