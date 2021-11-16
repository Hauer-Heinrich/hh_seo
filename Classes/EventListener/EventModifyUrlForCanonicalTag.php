<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\EventListener;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
// use \TYPO3\CMS\Core\Utility\GeneralUtility;

class EventModifyUrlForCanonicalTag {

    public function __construct() {
        $this->currentPageUid = $GLOBALS['TSFE']->id;
        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
    }

    public function __invoke($event): void {
        $metaTag = $this->additionalData['MetaTag'];

        if(!empty($metaTag)) {
            ksort($metaTag);

            foreach ($metaTag as $key => $value) {
                if(array_key_exists('headerData', $value) && array_key_exists('canonical', $value['headerData'])) {
                    $canonical = $value['headerData']['canonical'];
                    if(!empty($canonical)) {
                        $event->setUrl($canonical);
                    }
                }
            }
        }
    }
}
