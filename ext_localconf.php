<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {
    $extensionKey = "hh_seo";

    // deactivate ext:seo Meta-Tag generation
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] = [];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
        \TYPO3\CMS\Seo\HrefLang\HrefLangGenerator::class . '->generate';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
        \TYPO3\CMS\Seo\Canonical\CanonicalGenerator::class . '->generate';

    if (TYPO3_MODE === 'FE') {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$extensionKey] =
            HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'][] =
        //     HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData'; // params = page-data
    }

});
