<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag']['order'] = -1;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$extensionKey] = HauerHeinrich\HhSeo\Hooks\PageDataHook::class . '->addPageData';

});
