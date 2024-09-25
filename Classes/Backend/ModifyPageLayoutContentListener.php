<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\Backend;

use \TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \HauerHeinrich\HhSeo\Backend\PageLayoutHeader;

class ModifyPageLayoutContentListener {

    public function __invoke(ModifyPageLayoutContentEvent $event): void {
        if(
            !ExtensionManagementUtility::isLoaded('cs_seo')
            && !ExtensionManagementUtility::isLoaded('yoast_seo')
        ) {
            $pageLayoutHeader = GeneralUtility::makeInstance(PageLayoutHeader::class);
            $event->addHeaderContent($pageLayoutHeader->render([], $event->getModuleTemplate()));
        }
    }
}
