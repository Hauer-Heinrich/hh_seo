<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {
    $extensionKey = "hh_seo";
    // $vendor = "HauerHeinrich";
    // $className = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extensionKey);

    // deactivate ext:seo Meta-Tag generation
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] = [];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
        \TYPO3\CMS\Seo\HrefLang\HrefLangGenerator::class . '->generate';

    if (TYPO3_MODE === 'FE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$extensionKey] =
            HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] =
        //     HauerHeinrich\HhSeo\Hooks\TestHook::class . '->test';
    }

    $rootLineFields = &$GLOBALS["TYPO3_CONF_VARS"]["FE"]["addRootLineFields"];
    if (trim($rootLineFields) != "") $rootLineFields .= ',';
    $rootLineFields .= 'html_head,html_body_top,html_body_bottom,geo_region,geo_placename,geo_position_long,geo_position_lat,og_image,twitter_image';

    // Register 'hhseo' as global fluid namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['hhseo'] = ['HauerHeinrich\\HhSeo\\ViewHelpers'];
});
