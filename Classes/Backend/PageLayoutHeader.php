<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\Backend;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Backend\Controller\PageLayoutController;
use \TYPO3\CMS\Backend\Template\ModuleTemplate;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Fluid\View\StandaloneView;

class PageLayoutHeader {
    public function __construct() {
    }

    public function render(array $params = null, $parentObj = null): string {
        $languageId = $this->getLanguageId();
        $pageId = (int)GeneralUtility::_GET('id');
        $currentPage = $this->getCurrentPage($pageId, $languageId, $parentObj);

        $previewUri = \TYPO3\CMS\Backend\Routing\PreviewUriBuilder::create($pageId)
            ->withLanguage($languageId ?? 0)
            ->buildUri();

        if (!is_array($currentPage)) {
            return '';
        }

        $configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];

        $options = [
            'data' => $currentPage,
            'previewUri' => $previewUri,
        ];

        if(!empty($pluginSettings) && isset($pluginSettings['shortcutIcon'])) {
            $options['shortcutIcon'] = $this->resolveExtFilePathToWebUrl($pluginSettings['shortcutIcon']);
        }

        return $this->renderHtml($options);
    }

    /**
     * renderHtml
     * Render FLUID
     *
     * @param  array  $options
     * @return string
     */
    protected function renderHtml(array $options = []): string {
        $templateView = GeneralUtility::makeInstance(StandaloneView::class);
        $templateView->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName('EXT:hh_seo/Resources/Private/Templates/Backend/Header.html')
        );
        $templateView->assignMultiple([
            ...$options
        ]);

        return $templateView->render();
    }

    /**
     * getCurrentPage data
     *
     * @param  integer    $pageId
     * @param  integer    $languageId
     * @param  object     $parentObj
     * @return array|null
     */
    protected function getCurrentPage(int $pageId, int $languageId, object $parentObj): ?array {
        $currentPage = null;

        if (($parentObj instanceof PageLayoutController || $parentObj instanceof ModuleTemplate) && $pageId > 0) {
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
            if (strpos($filePath, 'EXT:') === 0) {
                $absPathName = GeneralUtility::getFileAbsFileName($filePath);
                $shortcutIconPublicUrl = str_replace(\TYPO3\CMS\Core\Core\Environment::getPublicPath().'/', '', $absPathName);
            }

            if(empty($shortcutIconPublicUrl)) {
                $resourceFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
                $image = $resourceFactory->getFileObjectFromCombinedIdentifier($filePath);
                $shortcutIconPublicUrl = $image->getPublicUrl();
            }
        }

        return $shortcutIconPublicUrl;
    }
}
