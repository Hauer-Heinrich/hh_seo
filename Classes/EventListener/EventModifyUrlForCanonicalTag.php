<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\EventListener;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Attribute\AsEventListener;
use \TYPO3\CMS\Seo\Event\ModifyUrlForCanonicalTagEvent;

#[AsEventListener(
    identifier: 'hh-seo/ModifyUrlForCanonicalTag',
    event: ModifyUrlForCanonicalTagEvent::class,
    before: 'typo3-seo/hreflangGenerator'
)]
final class EventModifyUrlForCanonicalTag {

    protected array $additionalData = [];

    public function __construct() {
        $this->additionalData = isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']) ? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'] : [];
    }

    public function __invoke(ModifyUrlForCanonicalTagEvent $event): void {
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
