<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {
    $extensionKey = 'hh_seo';

    $typo3version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
    $version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($typo3version->getVersion());

    // deactivate ext:seo Meta-Tag generation
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] = [];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] = [];

    if (class_exists('\\TYPO3\\CMS\\Core\\Domain\\Repository\\PageRepository', true)) {
        class_alias('\\TYPO3\\CMS\\Core\\Domain\\Repository\\PageRepository', 'PageRepository');
    } else {
        class_alias('\\TYPO3\\CMS\\Frontend\\Page\\PageRepository', 'PageRepository');
    }

    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$extensionKey] =
    //     \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess'][$extensionKey] =
    //     \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][$extensionKey] =
    //     \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][$extensionKey] =
    //     \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] =
        \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';

    $rootLineFields = &$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'];
    if (trim($rootLineFields) != "") $rootLineFields .= ',';
    $rootLineFields .= 'html_head,html_body_top,html_body_bottom,geo_region,geo_placename,geo_position_long,geo_position_lat,og_image,twitter_image';

    // Register 'hhseo' as global fluid namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['hhseo'] = ['HauerHeinrich\\HhSeo\\ViewHelpers'];

    // Register custom cache
    if (array_key_exists('hhseo_meta', $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']) && !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hhseo_meta'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hhseo_meta'] = [];
    }
});
