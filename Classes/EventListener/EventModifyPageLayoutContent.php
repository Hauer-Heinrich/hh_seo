<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\EventListener;


use \Psr\Http\Message\ServerRequestInterface;
// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\View\ViewFactoryInterface;

class EventModifyPageLayoutContent {
    public function __construct(
        private readonly ViewFactoryInterface $viewFactory
    ) {
    }

    public function __invoke(ModifyPageLayoutContentEvent $event): void {
        if(
            !ExtensionManagementUtility::isLoaded('cs_seo')
            && !ExtensionManagementUtility::isLoaded('yoast_seo')
        ) {
            $languageId = $this->getLanguageId();
            $selectedPageUid = (int)$event->getRequest()->getQueryParams()['id'] ?? 0;
            $currentPage = $this->getCurrentPage($selectedPageUid, $languageId);

            if(isset($currentPage['doktype']) && $currentPage['doktype'] !== 1) {
                return;
            }

            $previewUri = \TYPO3\CMS\Backend\Routing\PreviewUriBuilder::create($selectedPageUid)
                ->withLanguage($languageId ?? 0)
                ->buildUri();

            if (!is_array($currentPage)) {
                return;
            }

            $configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
            $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'] ?? null;

            $options = [
                'data' => $currentPage,
                'previewUri' => $previewUri,
            ];

            if(!empty($pluginSettings) && isset($pluginSettings['shortcutIcon'])) {
                $options['shortcutIcon'] = $this->resolveExtFilePathToWebUrl($pluginSettings['shortcutIcon']);
            }

            $event->addHeaderContent($this->renderHtml($options));

            $event->setFooterContent('Overwrite footer content');
        }
    }

    /**
     * Render FLUID
     *
     * @param  array  $options
     */
    protected function renderHtml(array $options = []): string {
        $viewFactoryData = new \TYPO3\CMS\Core\View\ViewFactoryData(
            templateRootPaths: ['EXT:hh_seo/Resources/Private/Templates/Backend/'],
            partialRootPaths: ['EXT:hh_seo/Resources/Private/Partials/Backend/'],
            layoutRootPaths: ['EXT:hh_seo/Resources/Private/Layouts/Backend/'],
        );
        $view = $this->viewFactory->create($viewFactoryData);
        $view->assignMultiple([...$options]);

        return $view->render('Header.html');
    }

    /**
     * @param  integer    $pageId
     * @param  integer    $languageId
     */
    protected function getCurrentPage(int $pageId, int $languageId): ?array {
        $currentPage = null;

        if ($languageId === 0) {
            $currentPage = BackendUtility::getRecord(
                'pages',
                $pageId
            );
        } elseif ($languageId > 0) {
            $overlayRecords = BackendUtility::getRecordLocalization(
                'pages',
                $pageId,
                $languageId
            );

            if (is_array($overlayRecords) && array_key_exists(0, $overlayRecords) && is_array($overlayRecords[0])) {
                $currentPage = $overlayRecords[0];
            }
        }

        return $currentPage;
    }

    /**
     * getLanguageId
     *
     * @return integer
     */
    protected function getLanguageId(): int {
        $moduleData = (array)BackendUtility::getModuleData(['language'], [], 'web_layout');

        return (int)$moduleData['language'];
    }

    /**
     * resolveExtFilePathToWebUrl
     * e. g. EXT:my_extension_key/Resources/icon.svg  => typo3conf/ext/my_extension_key/Resources/icon.svg
     */
    public function resolveExtFilePathToWebUrl(string $filePath): string {
        $shortcutIconPublicUrl = '';

        if(!empty($filePath)) {
            if(filter_var($filePath, FILTER_VALIDATE_URL)) {
                $shortcutIconPublicUrl = trim($filePath);

                return $shortcutIconPublicUrl;
            }

            if(str_starts_with($filePath, 'EXT:')) {
                $absPathName = GeneralUtility::getFileAbsFileName($filePath);
                $shortcutIconPublicUrl = str_replace(\TYPO3\CMS\Core\Core\Environment::getPublicPath().'/', '', $absPathName);
            }

            if(empty($shortcutIconPublicUrl)) {
                $resourceFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
                $image = $resourceFactory->getFileObjectFromCombinedIdentifier($filePath);
                $shortcutIconPublicUrl = $image->getPublicUrl();
            }
        }

        if(!empty($shortcutIconPublicUrl) && !\str_starts_with($shortcutIconPublicUrl, '/')) {
            $shortcutIconPublicUrl = '/'.$shortcutIconPublicUrl;
        }

        return $shortcutIconPublicUrl;
    }
}
