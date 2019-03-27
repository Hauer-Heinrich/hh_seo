<?php
namespace HauerHeinrich\HhSeo\Hooks;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PageDataHook {
    protected $additionalData;

    public function __construct() {
        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
    }

    /**
     * My example function
     *
     * @param array $parameters
     * @return string
    */
    public function addPageData(&$parameters) {
        $headerData = $this->additionalData['headerData'];

        DebuggerUtility::var_dump($this->additionalData["headerData"]);

        $newData = '';
        if($headerData['title']) {
            $newData .= "<title>{$headerData['title']}</title>";
        }

        if($headerData['description']) {
            $newData .= "<meta name='description' content='{$headerData['description']}'>";
        }


        if($headerData['og:title']) {
            $newData .= "<meta property='og:title' content='{$headerData['og:title']}'>";
        } else if($headerData['title']) {
            $newData .= "<meta property='og:title' content='{$headerData['title']}'>";
        }

        // <meta http-equiv="imagetoolbar" content="false">
        // <meta name="format-detection" content="telephone=no">
        // <meta name="theme-color" content="{$plugin.hhthememutterkind.themeColor}">

        $parameters["headerData"][2] = $newData;
    }
}
