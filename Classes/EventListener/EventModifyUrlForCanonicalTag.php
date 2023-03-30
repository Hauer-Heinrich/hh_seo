<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\EventListener;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
// use \TYPO3\CMS\Core\Utility\GeneralUtility;

class EventModifyUrlForCanonicalTag {

    protected $currentPageUid;

    protected array $additionalData;

    public function __construct() {
        // $this->currentPageUid = isset($GLOBALS['TSFE']->id) ? $GLOBALS['TSFE']->id : 1;
        $this->additionalData = isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']) ? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'] : [];
    }

    public function __invoke($event): void {
        $metaTag = isset($this->additionalData['MetaTag']) ? $this->additionalData['MetaTag'] : [];

        if(!empty($metaTag)) {
            ksort($metaTag);

            foreach ($metaTag as $value) {
                if(array_key_exists('headerData', $value) && is_array($value['headerData']) && array_key_exists('canonical', $value['headerData'])) {
                    $canonical = $value['headerData']['canonical'];
                    if(!empty($canonical)) {
                        $event->setUrl($canonical);
                    }
                }
            }
        }
    }
}
