<?php
defined('TYPO3') || die('Access denied.');

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function() {
    if(
        !ExtensionManagementUtility::isLoaded('cs_seo')
        && !ExtensionManagementUtility::isLoaded('yoast_seo')
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][]
            = \HauerHeinrich\HhSeo\Backend\PageLayoutHeader::class . '->render';
    }

    // deactivate ext:seo Meta-Tag generation
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] = [];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] = [];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] =
        \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';

    $versionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
    // TODO: Remove when dropping TYPO3 v12 support
    if ($versionInformation->getMajorVersion() < 13) {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',html_head,html_body_top,html_body_bottom,geo_region,geo_placename,geo_position_long,geo_position_lat,og_image,twitter_image';
    }

    // Register 'hhseo' as global fluid namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['hhseo'] = ['HauerHeinrich\\HhSeo\\ViewHelpers'];

    // Register custom cache
    if (array_key_exists('hhseo_meta', $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']) && !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hhseo_meta'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hhseo_meta'] = [];
    }
});
