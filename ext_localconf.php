<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {
    // deactivate ext:seo Meta-Tag generation
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] = [];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] = [];

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
