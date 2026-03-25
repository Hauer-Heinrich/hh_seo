<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function() {
    // deactivate ext:seo Meta-Tag generation
    // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags'] = [];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] = [];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\CMS\Frontend\Page\PageGenerator']['generateMetaTags']['metatag'] =
        \HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';

    // Register 'hhseo' as global fluid namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['hhseo'] = ['HauerHeinrich\\HhSeo\\ViewHelpers'];

    // Register custom cache
    if (array_key_exists('hhseo_meta', $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']) && !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hhseo_meta'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hhseo_meta'] = [];
    }
});
